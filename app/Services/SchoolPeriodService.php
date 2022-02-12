<?php
/**
 * Created by PhpStorm.
 * User: halayon
 * Date: 26/08/19
 * Time: 09:39 AM
 */

namespace App\Services;

use App\Log;
use App\User;
use Illuminate\Http\Request;
use App\SchoolPeriod;
use App\Subject;
use App\SchoolPeriodSubjectTeacher;
use App\Schedule;
use Illuminate\Http\Response;

/**
 * @package : Services
 * @author : Hector Alayon
 * @version : 1.0
 */
class SchoolPeriodService
{
    const taskError = 'No se puede proceder con la tarea';
    const taskPartialError = 'No se pudo proceder con la tarea en su totalidad';
    const emptySchoolPeriod = 'No existen periodos escolares';
    const notFoundSchoolPeriod = 'Periodo escolar no encontrado';
    const busyCodSchoolPeriod = 'Periodo escolar ya registrado';
    const duplicateSubjects = 'Asignaturas duplicadas';
    const invalidSubjectOrTeacher = 'Asignatura o profesor invalido';
    const ok = 'OK';
    const noCurrentSchoolPeriod='No hay periodo escolar en curso';
    const noTeachSubjects='No impartes asignaturas en el periodo escolar actual';
    const logCreateSchoolPeriod = 'Creo el periodo escolar ';
    const logUpdateSchoolPeriod = 'Actualizo el periodo escolar ';
    const whitId = ' con id ';
    const logDeleteSchoolPeriod = 'Elimino el periodo escolar ';

    /**
     * Lista todos los periodos escolares que están asociados a la organización con el método
     * SchoolPeriod::getSchoolPeriods($organizationId).
     * @param string $organizationId Id de la organiación
     * @param integer $perPage Parámetro opcional, cantidad de elementos por página, default:0
     * @return SchoolPeriod|Response Obtiene todos los periodos escolares presentes en la organizacion.
     */
    public static function getSchoolPeriods($organizationId,$perPage=0)
    {
        $perPage == 0 ? $schoolPeriods = SchoolPeriod::getSchoolPeriods($organizationId) :
            $schoolPeriods = SchoolPeriod::getSchoolPeriods($organizationId,$perPage);
        if (is_numeric($schoolPeriods)&&$schoolPeriods===0){
            return response()->json(['message'=>self::taskError],500);
        }
        if ($perPage == 0){
            if (count($schoolPeriods)>0){
                return $schoolPeriods;
            }
            return response()->json(['message'=>self::emptySchoolPeriod],206);
        }else{
            return $schoolPeriods;
        }
    }

    /**
     *Devuelve un periodo escolar dado su id y la organización con el método
     * SchoolPeriod::getSchoolPeriodById($id,$organizationId).
     * @param string $id Id de la asignatura
     * @param string $organizationId Id de la organiación
     * @return Subject|Response Obtiene un periodo escolar dado su id en la organizacion.
     */
    public static function getSchoolPeriodById($id,$organizationId)
    {
        $schoolPeriod = SchoolPeriod::getSchoolPeriodById($id,$organizationId);
        if (is_numeric($schoolPeriod)&&$schoolPeriod===0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (count($schoolPeriod)>0){
            return $schoolPeriod[0];
        }
        return response()->json(['message'=>self::notFoundSchoolPeriod],206);
    }

    /**
     *Valida que se cumpla las restricciones:
     * *cod_school_period: requerido y máximo 10
     * *start_date: requerido y máximo 10
     * *end_date: requerido y máximo 10
     * *withdrawal_deadline: máximo 10
     * *load_notes: boolean
     * *subjects.*.teacher_id: requerido y numérico
     * *subjects.*.subject_id: requerido y numérico
     * *subjects.*.limit: requerido y numérico
     * *subjects.*.duty: requerido y numérico
     * *subjects.*.modality: requerido, máximo 3 y debe terminar en REG, INT o SUF
     * *subjects.*.start_date: máximo 10
     * *subjects.*.end_date: máximo 10
     * *subjects.*.schedules.*.day: requerido, máximo 1 y debe terminar en 1 ,2, 3, 4, 5, 6, o 7
     * *subjects.*.schedules.*.classroom: requerido y máximo 20
     * *subjects.*.schedules.*.start_hour: requerido y máximo 8
     * *subjects.*.schedules.*.end_hour: requerido y máximo 8
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validate(Request $request)
    {
        $request->validate([
            'cod_school_period'=>'required|max:10',
            'start_date'=>'required|size:10',
            'end_date'=>'required|size:10',
            'withdrawal_deadline'=>'size:10',
            'load_notes'=>'boolean',
            'inscription_start_date'=>'required|size:10',
            'inscription_visible'=>'boolean',
            'project_duty'=>'required|numeric',
            'final_work_duty'=>'required|numeric',
            'subjects.*.teacher_id'=>'required|numeric',
            'subjects.*.subject_id'=>'required|numeric',
            'subjects.*.limit'=>'required|numeric',
            'subjects.*.duty'=>'required|numeric',
            'subjects.*.modality'=>'required|max:3|ends_with:REG,INT,SUF',
            'subjects.*start_date'=>'size:10',
            'subjects.*end_date'=>'size:10',
            'subjects.*.schedules.*.day'=>'required|size:1|ends_with:1,2,3,4,5,6,7',
            'subjects.*.schedules.*.classroom'=>'required|max:20',
            'subjects.*.schedules.*.start_hour'=>'required|size:8',
            'subjects.*.schedules.*.end_hour'=>'required|size:8',
        ]);
    }

    /**
     * Valida que los id de los profesores y las asignaturas se encuentre en la organización, retorna un booleano.
     * @param Subject $subjects: Array de la petición con las asignaturas que se asocian al periodo escolar
     * @param string $organizationId Id de la organiación
     * @return integer|boolean Devuelve un booleano si los profesores y asignaturas pertenecen a la organizacion en caso
     * de existir un error devolvera 0.
     */
    public static function validateSubjects($subjects,$organizationId)
    {
        $subjectsInBd = Subject::getSubjects($organizationId);
        $teachersInBd = User::getUsers('T',$organizationId);
        if (is_numeric((is_numeric($subjectsInBd))&&$subjectsInBd===0)||(is_numeric($teachersInBd)&&$teachersInBd===0)){
            return 0;
        }
        $subjectsId = array_column($subjectsInBd->toArray(),'id');
        $teachersId = array_column($teachersInBd->toArray(),'id');
        foreach ($subjects as $subject){
            if (!in_array($subject['subject_id'],$subjectsId)){
                return false;
            }
            if (!in_array($subject['teacher_id'],$teachersId)){
                return false;
            }
        }
        return true;
    }

    /**
     * Valida que no se envíe la misma asignatura dos veces en el  mismo periodo escolar, retorna un booleano.
     * @param Subject $subjects: Array de la petición con las asignaturas que se asocian al periodo escolar
     * @return integer|boolean Devuelve true si las asignaturas no estan duplicadas, de lo contrario devolvera false
     */
    public static function subjectConsistency($subjects)
    {
        $subjectId = [];
        foreach ($subjects as $subject){
            if (in_array($subject['subject_id'],$subjectId)){
                return false;
            }
            $subjectId[]=$subject['subject_id'];
        }
        return true;
    }

    /**
     * Crea un horario asociado al objeto SchoolPeriodSubjectTeacher con el metodo Schedule::addSchedule($schedule).
     * @param object $schedules Array de la petición con los horarios de una asignatura
     * @param integer $schoolPeriodSubjectTeacherId Id de la relacion entre periodo escolar, asignatura y profesor
     * @return integer de ocurrir un error devolvera 0.
     */
    public static function addSchedules($schedules,$schoolPeriodSubjectTeacherId)
    {
        foreach ($schedules as $schedule){
            $schedule['school_period_subject_teacher_id']=$schoolPeriodSubjectTeacherId;
            $result = Schedule::addSchedule($schedule);
            if ($result === 0) {
                return 0;
            }
        }
    }

    /**
     * Agrega asignaturas al periodo escolar dado su id y devuelve su id con el metodo
     * SchoolPeriodSubjectTeacher::addSchoolPeriodSubjectTeacher($subject).
     * @param array $subjects: Array de la petición con las asignaturas que se asocian al periodo escolar
     * @param integer $schoolPeriodId Id del periodo escolar asociado
     * @return integer Devuelve el id de la relacion entre periodo escolar profesor y asignatura, en caso de existir un
     * error devolvera 0.
     */
    public static function addSubjectInSchoolPeriod($subjects,$schoolPeriodId)
    {
        foreach ($subjects as $subject){
            $subject['enrolled_students']=0;
            $subject['school_period_id']=$schoolPeriodId;
            $schoolPeriodSubjectTeacherId = SchoolPeriodSubjectTeacher::addSchoolPeriodSubjectTeacher($subject);
            if ($schoolPeriodSubjectTeacherId==0){
                return 0;
            }
            if (isset($subject['schedules'])){
                $result = self::addSchedules($subject['schedules'],$schoolPeriodSubjectTeacherId);
                if (is_numeric($result)&&$result==0){
                    return 0;
                }
            }
            return $schoolPeriodSubjectTeacherId;
        }
    }

    /**
     * Agrega asignaturas al periodo escolar dado su id con el metodo
     * SchoolPeriodSubjectTeacher::addSchoolPeriodSubjectTeacher($subject).
     * @param array $subjects: Array de la petición con las asignaturas que se asocian al periodo escolar
     * @param integer $schoolPeriodId Id del periodo escolar asociado
     * @return integer Devuelve el id de la relacion entre periodo escolar profesor y asignatura, en caso de existir un
     * error devolvera 0.
     */
    public static function addNewSubjectInSchoolPeriod($subjects,$schoolPeriodId)
    {
        foreach ($subjects as $subject){
            $subject['enrolled_students']=0;
            $subject['school_period_id']=$schoolPeriodId;
            $schoolPeriodSubjectTeacherId = SchoolPeriodSubjectTeacher::addSchoolPeriodSubjectTeacher($subject);
            if ($schoolPeriodSubjectTeacherId==0){
                return 0;
            }
            if (isset($subject['schedules'])){
                $result = self::addSchedules($subject['schedules'],$schoolPeriodSubjectTeacherId);
                if (is_numeric($result)&&$result==0){
                    return 0;
                }
            }
        }
    }

    /**
     * Agrega un periodo escolar con el método SchoolPeriod::addSchoolPeriod($request).
     * Nota: Se asume que las asignaturas de proyecto o trabajo especial de grado estarán habilitadas en todos los
     * periodos escolares de los programas que las tengan.
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @return Response|SchoolPeriod de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera
     * correcta devolvera el objeto SchoolPeriod.
     */
    public static function addSchoolPeriod(Request $request,$organizationId)
    {
        self::validate($request);
        $existSchoolPeriodByCod =SchoolPeriod::existSchoolPeriodbyCodSchoolPeriod($request['cod_school_period'],
            $organizationId);
        if (is_numeric($existSchoolPeriodByCod)&&$existSchoolPeriodByCod===0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (!$existSchoolPeriodByCod){
            $request['load_notes']=false;
            $request['inscription_visible']=false;
            $request['organization_id']=$organizationId;
            if (isset($request['subjects'])){
                if (!self::subjectConsistency($request['subjects'])){
                    return response()->json(['message'=>self::duplicateSubjects],206);
                }
                $validateSubjects=self::validateSubjects($request['subjects'],$organizationId);
                if (is_numeric($validateSubjects)&&$validateSubjects===0){
                    return response()->json(['message'=>self::taskError],500);
                }
                if (!$validateSubjects){
                    return response()->json(['message'=>self::invalidSubjectOrTeacher],206);
                }
                $schoolPeriodId = SchoolPeriod::addSchoolPeriod($request);
                if ($schoolPeriodId==0){
                    return response()->json(['message'=>self::taskError],500);
                }
                $result = self::addNewSubjectInSchoolPeriod($request['subjects'],$schoolPeriodId);
                if (is_numeric($result)&&$result===0){
                    return response()->json(['message' => self::taskPartialError], 500);
                }
            }else{
                $schoolPeriodId = SchoolPeriod::addSchoolPeriod($request);
                if (is_numeric($schoolPeriodId)&&$schoolPeriodId===0){
                    return response()->json(['message'=>self::taskError],500);
                }
            }
            $updateStudent=StudentService::warningOrAvailableWorkToStudent($organizationId);
            if (is_numeric($updateStudent)&&$updateStudent===0){
                return response()->json(['message'=>self::taskError],500);
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logCreateSchoolPeriod.$request['cod_school_period'].
                self::whitId.$schoolPeriodId);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message' => self::taskPartialError], 500);
            }
            return self::getSchoolPeriodById($schoolPeriodId,$organizationId);
        };
        return response()->json(['message'=>self::busyCodSchoolPeriod],206);
    }

    /**
     * Elimina un periodo escolar dado su id con el método SchoolPeriod::deleteSchoolPeriod($id).
     * @param string $id Id del periodo escolar
     * @param string $organizationId Id de la organiación
     * @return Response, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcto
     * devolvera un objeto con mensaje OK.
     */
    public static function deleteSchoolPeriod($id,$organizationId)
    {
        $schoolPeriod = SchoolPeriod::getSchoolPeriodById($id,$organizationId);
        if (count($schoolPeriod)>0){
            $result=SchoolPeriod::deleteSchoolPeriod($id);
            if (is_numeric($result)&&$result==0){
                return response()->json(['message'=>self::taskError],500);
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logDeleteSchoolPeriod.
                $schoolPeriod[0]['cod_school_period'].self::whitId.$id);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message' => self::taskError], 500);
            }
            return response()->json(['message'=>self::ok]);
        }
        return response()->json(['message'=>self::notFoundSchoolPeriod],206);
    }

    /**
     *Valida que se cumpla las restricciones:
     * *load_notes: requerido y booleano
     * *inscription_visible: requerido y booleano
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validateInUpdate(Request $request)
    {
        $request->validate([
            'load_notes'=>'required|boolean',
            'inscription_visible'=>'required|boolean',
        ]);
    }

    /**
     * Actualiza un objeto de tipo schoolPeriodSubjectTeacher con el metodo
     * SchoolPeriodSubjectTeacher::updateSchoolPeriodSubjectTeacher($subjectInBd['id'],$subject).
     * @param Subject $subjects: Array de la petición con las asignaturas que se asocian al periodo escolar
     * @param integer $schoolPeriodId Id del periodo escolar asociado
     * @return integer en caso de existir un error devolvera 0.
     */
    public static function updateSubjectInSchoolPeriod($subjects,$schoolPeriodId)
    {
        $subjectsInBd = SchoolPeriodSubjectTeacher::getSchoolPeriodSubjectTeacherBySchoolPeriod($schoolPeriodId);
        if (is_numeric($subjectsInBd)&&$subjectsInBd==0){
            return 0;
        }
        $subjectsUpdated = [];
        foreach ($subjects as $subject){
            $existSubject = false;
            foreach ($subjectsInBd as $subjectInBd){
                if ($subjectInBd['teacher_id']==$subject['teacher_id'] AND
                    $subjectInBd['subject_id']==$subject['subject_id']){
                    $subject['school_period_id']=$schoolPeriodId;
                    $subject['enrolled_student']=$subjectInBd['enrolled_student'];
                    $result=SchoolPeriodSubjectTeacher::updateSchoolPeriodSubjectTeacher($subjectInBd['id'],$subject);
                    if (is_numeric($result)&&$result===0){
                        return 0;
                    }
                    $result= Schedule::deleteAllSchedule($subjectInBd['id']);
                    if (is_numeric($result)&&$result==0){
                        return 0;
                    }
                    if (isset($subject['schedules'])){
                        $result = self::addSchedules($subject['schedules'],$subjectInBd['id']);
                        if (is_numeric($result)&&$result===0){
                            return 0;
                        }
                    }
                    $subjectsUpdated[]=$subjectInBd['id'];
                    $existSubject=true;
                    break;
                }
            }
            if ($existSubject == false){
                $result =self::addSubjectInSchoolPeriod([$subject],$schoolPeriodId);
                if (is_numeric($result)&&$result===0){
                    return 0;
                }
                $subjectsUpdated[]=$result;
            }
        }
        foreach ($subjectsInBd as $subjectId){
            if (!in_array($subjectId['id'],$subjectsUpdated)){
                $result =SchoolPeriodSubjectTeacher::deleteSchoolPeriodSubjectTeacher($subjectId['id']);
                if (is_numeric($result)&&$result===0){
                    return 0;
                }
            }
        }
    }

    /**
     * Actualiza un periodo escolar dado su id con el metodo SchoolPeriod::updateSchoolPeriod($id, $request).
     * @param Request $request Objeto con los datos de la petición
     * @param string $id Id del periodo escolar
     * @param string $organizationId Id de la organiación
     * @return Response|SchoolPeriod de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera
     * correcta devolvera el objeto SchoolPeriod.
     */
    public static function updateSchoolPeriod(Request $request,$id,$organizationId)
    {
        $request['organization_id']=$organizationId;
        $existSchoolPeriod=SchoolPeriod::existSchoolPeriodById($id,$organizationId);
        if (is_numeric($existSchoolPeriod)&&$existSchoolPeriod===0){
            return response()->json(['message'=>self::taskError],500);
        }
        if ($existSchoolPeriod){
            self::validate($request);
            self::validateInUpdate($request);
            $schoolPeriod=SchoolPeriod::getSchoolPeriodByCodSchoolPeriod($request['cod_school_period'],$organizationId);
            if (is_numeric($schoolPeriod)&&$schoolPeriod===0){
                return response()->json(['message'=>self::taskError],500);
            }
            if (count($schoolPeriod)>0){
                if ($schoolPeriod[0]['id']!=$id){
                    return response()->json(['message'=>self::busyCodSchoolPeriod],206);
                }
            }
            if (isset($request['subjects'])){
                if (!self::subjectConsistency($request['subjects'])){
                    return response()->json(['message'=>self::duplicateSubjects],206);
                }
                $validateSubjects=self::validateSubjects($request['subjects'],$organizationId);
                if (is_numeric($validateSubjects)&&$validateSubjects===0){
                    return response()->json(['message'=>self::taskError],500);
                }
                if (!$validateSubjects){
                    return response()->json(['message'=>self::invalidSubjectOrTeacher],206);
                }
                $result=SchoolPeriod::updateSchoolPeriod($id, $request);
                if (is_numeric($result)&&$result===0){
                    return response()->json(['message'=>self::taskError],500);
                }
                $result = self::updateSubjectInSchoolPeriod($request['subjects'],$id);
                if (is_numeric($result)&&$result===0){
                    return response()->json(['message'=>self::taskError],500);
                }
            }else{
                $result = SchoolPeriod::updateSchoolPeriod($id, $request);
                if (is_numeric($result)&&$result===0){
                    return response()->json(['message'=>self::taskError],500);
                }
                $existSchoolPeriodSubjectTeacher=
                    SchoolPeriodSubjectTeacher::existSchoolPeriodSubjectTeacherBySchoolPeriodId($id);
                if (is_numeric($existSchoolPeriodSubjectTeacher)&& $existSchoolPeriodSubjectTeacher==0){
                    return response()->json(['message'=>self::taskError],500);
                }
                if ($existSchoolPeriodSubjectTeacher){
                    $result=SchoolPeriodSubjectTeacher::deleteSchoolPeriodSubjectTeacherBySchoolPeriod($id);
                    if (is_numeric($result)&&$result===0){
                        return response()->json(['message'=>self::taskError],500);
                    }
                }
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logUpdateSchoolPeriod.$request['cod_school_period'].
                self::whitId.$id);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message' => self::taskPartialError], 500);
            }
            return self::getSchoolPeriodById($id,$organizationId);
        }
        return response()->json(['message'=>self::notFoundSchoolPeriod],206);
    }

    /**
     * Obtiene el periodo escolar actual con el método  SchoolPeriod::getCurrentSchoolPeriod($organizationId).
     * SchoolPeriod::getSchoolPeriodById($id,$organizationId).
     * @param string $organizationId Id de la organiación
     * @return SchoolPeriod|Response Obtiene un periodo escolar actual dado su id en la organizacion, de ocurrir un
     * error o no existir periodo escolar devolvera un mensaje asociado.
     */
    public static function getCurrentSchoolPeriod($organizationId)
    {
        $currentSchoolPeriod = SchoolPeriod::getCurrentSchoolPeriod($organizationId);
        if (is_numeric($currentSchoolPeriod) && $currentSchoolPeriod ==0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (count($currentSchoolPeriod)>0){
            return $currentSchoolPeriod[0];
        }
        return response()->json(['message'=>self::noCurrentSchoolPeriod],206);
    }

    /**
     * Obtiene las asignaturas que dicta un profesor dado su id, en el periodo escolar actual.
     * SchoolPeriod::getSchoolPeriodById($id,$organizationId).
     * @param string $teacherId Id del profesor
     * @param string $organizationId Id de la organiación
     * @return SchoolPeriodSubjectTeacher|Response Obtiene todas las asignaturas que dicta un profesor en el periodo
     * escolar actual.
     */
    public static function getSubjectsTaughtSchoolPeriod($teacherId,$organizationId)
    {
        $isValid=TeacherService::validateTeacher($teacherId,$organizationId);
        if ($isValid=='valid'){
            $currentSchoolPeriod= SchoolPeriod::getCurrentSchoolPeriod($organizationId);
            if (is_numeric($currentSchoolPeriod)&&$currentSchoolPeriod===0){
                return response()->json(['message'=>self::taskError],500);
            }
            if (count($currentSchoolPeriod)>0){
                $subjectsTaught =
                    SchoolPeriodSubjectTeacher::getSchoolPeriodSubjectTeacherBySchoolPeriodTeacher($teacherId,
                    $currentSchoolPeriod[0]['id']);
                if (is_numeric($subjectsTaught)&&$subjectsTaught===0){
                    return response()->json(['message'=>self::taskError],500);
                }
                if (count($subjectsTaught)>0){
                    return $subjectsTaught;
                }
                return response()->json(['message'=>self::noTeachSubjects],206);
            }
            return response()->json(['message'=>self::noCurrentSchoolPeriod],206);
        }
        return $isValid;
    }
}
