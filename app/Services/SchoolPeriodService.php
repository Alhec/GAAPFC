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

class SchoolPeriodService
{
    const taskError = 'No se puede proceder con la tarea';
    const taskPartialError = 'No se pudo proceder con la tarea en su totalidad';
    const emptySchoolPeriod = 'No existen periodos escolares';
    const notFoundSchoolPeriod = 'Periodo escolar no encontrado';
    const busyCodSchoolPeriod = 'Periodo escolar ya registrado';
    const duplicateSubjects = 'Materias duplicadas';
    const invalidSubjectOrTeacher = 'Materia o profesor invalido';
    const ok = 'OK';
    const noCurrentSchoolPeriod='No hay periodo escolar en curso';
    const noTeachSubjects='No impartes materias en el periodo escolar actual';

    const logCreateSchoolPeriod = 'Creo el periodo escolar ';
    const logUpdateSchoolPeriod = 'Actualizo el periodo escolar ';
    const whitId = ' con id ';
    const logDeleteSchoolPeriod = 'Elimino el periodo escolar ';


    public static function getSchoolPeriods($organizationId)
    {
        $schoolPeriods = SchoolPeriod::getSchoolPeriods($organizationId);
        if (is_numeric($schoolPeriods)&&$schoolPeriods===0){
            return response()->json(['message' => self::taskError], 206);
        }
        if (count($schoolPeriods)>0){
            return $schoolPeriods;
        }
        return response()->json(['message'=>self::emptySchoolPeriod],206);
    }

    public static function getSchoolPeriodById($id,$organizationId)
    {
        $schoolPeriod = SchoolPeriod::getSchoolPeriodById($id,$organizationId);
        if (is_numeric($schoolPeriod)&&$schoolPeriod===0){
            return response()->json(['message' => self::taskError], 206);
        }
        if (count($schoolPeriod)>0){
            return $schoolPeriod[0];
        }
        return response()->json(['message'=>self::notFoundSchoolPeriod],206);
    }

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

    public static function addSchoolPeriod(Request $request,$organizationId)
    {
        self::validate($request);
        $existSchoolPeriodByCod =SchoolPeriod::existSchoolPeriodbyCodSchoolPeriod($request['cod_school_period'],$organizationId);
        if (is_numeric($existSchoolPeriodByCod)&&$existSchoolPeriodByCod===0){
            return response()->json(['message' => self::taskError], 206);
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
                    return response()->json(['message' => self::taskError], 206);
                }
                if (!$validateSubjects){
                    return response()->json(['message'=>self::invalidSubjectOrTeacher],206);
                }
                $schoolPeriodId = SchoolPeriod::addSchoolPeriod($request);
                if ($schoolPeriodId==0){
                    return response()->json(['message' => self::taskError], 206);
                }
                $result = self::addSubjectInSchoolPeriod($request['subjects'],$schoolPeriodId);
                if (is_numeric($result)&&$result===0){
                    return response()->json(['message' => self::taskPartialError], 206);
                }
            }else{
                $schoolPeriodId = SchoolPeriod::addSchoolPeriod($request);
                if (is_numeric($schoolPeriodId)&&$schoolPeriodId===0){
                    return response()->json(['message' => self::taskError], 206);
                }
            }
            $updateStudent=StudentService::warningOrAvailableWorkToStudent($organizationId);
            if (is_numeric($updateStudent)&&$updateStudent===0){
                return response()->json(['message' => self::taskError], 206);
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logCreateSchoolPeriod.$request['cod_school_period'].
                self::whitId.$schoolPeriodId);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message'=>self::taskPartialError],401);
            }
            return self::getSchoolPeriodById($schoolPeriodId,$organizationId);
        };
        return response()->json(['message'=>self::busyCodSchoolPeriod],206);
    }

    public static function deleteSchoolPeriod($id,$organizationId)
    {
        $schoolPeriod = SchoolPeriod::getSchoolPeriodById($id,$organizationId);
        if (count($schoolPeriod)>0){
            $result=SchoolPeriod::deleteSchoolPeriod($id);
            if (is_numeric($result)&&$result==0){
                return response()->json(['message' => self::taskError], 206);
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logDeleteSchoolPeriod.$schoolPeriod[0]['cod_school_period'].
                self::whitId.$id);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message'=>self::taskPartialError],401);
            }
            return response()->json(['message'=>self::ok]);
        }
        return response()->json(['message'=>self::notFoundSchoolPeriod],206);
    }

    public static function validateInUpdate(Request $request)
    {
        $request->validate([
            'load_notes'=>'required|boolean',
            'inscription_visible'=>'required|boolean',
        ]);
    }

    public static function updateSubjectInSchoolPeriod($subjects,$schoolPeriodId)
    {
        $subjectsInBd = SchoolPeriodSubjectTeacher::getSchoolPeriodSubjectTeacherBySchoolPeriod($schoolPeriodId);
        if (is_numeric($subjectsInBd)&&$subjectsInBd==0){
            return response()->json(['message' => self::taskError], 206);
        }
        $subjectsUpdated = [];
        foreach ($subjects as $subject){
            $existSubject = false;
            foreach ($subjectsInBd as $subjectInBd){
                if ($subjectInBd['teacher_id']==$subject['teacher_id'] AND $subjectInBd['subject_id']==$subject['subject_id']){
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

    public static function updateSchoolPeriod(Request $request,$id,$organizationId)
    {
        $request['organization_id']=$organizationId;
        $existSchoolPeriod=SchoolPeriod::existSchoolPeriodById($id,$organizationId);
        if (is_numeric($existSchoolPeriod)&&$existSchoolPeriod===0){
            return response()->json(['message' => self::taskError], 206);
        }
        if ($existSchoolPeriod){
            self::validate($request);
            self::validateInUpdate($request);
            $schoolPeriod=SchoolPeriod::getSchoolPeriodByCodSchoolPeriod($request['cod_school_period'],$organizationId);
            if (is_numeric($schoolPeriod)&&$schoolPeriod===0){
                return response()->json(['message' => self::taskError], 206);
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
                    return response()->json(['message' => self::taskError], 206);
                }
                if (!$validateSubjects){
                    return response()->json(['message'=>self::invalidSubjectOrTeacher],206);
                }
                $result=SchoolPeriod::updateSchoolPeriod($id, $request);
                if (is_numeric($result)&&$result===0){
                    return response()->json(['message' => self::taskError], 206);
                }
                $result = self::updateSubjectInSchoolPeriod($request['subjects'],$id);
                if (is_numeric($result)&&$result===0){
                    return response()->json(['message' => self::taskError], 206);
                }
            }else{
                $result = SchoolPeriod::updateSchoolPeriod($id, $request);
                if (is_numeric($result)&&$result===0){
                    return response()->json(['message' => self::taskError], 206);
                }
                $existSchoolPeriodSubjectTeacher=SchoolPeriodSubjectTeacher::existSchoolPeriodSubjectTeacherBySchoolPeriodId($id);
                if (is_numeric($existSchoolPeriodSubjectTeacher)&& $existSchoolPeriodSubjectTeacher==0){
                    return response()->json(['message' => self::taskError], 206);
                }
                if ($existSchoolPeriodSubjectTeacher){
                    $result=SchoolPeriodSubjectTeacher::deleteSchoolPeriodSubjectTeacherBySchoolPeriod($id);
                    if (is_numeric($result)&&$result===0){
                        return response()->json(['message' => self::taskError], 206);
                    }
                }
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logUpdateSchoolPeriod.$request['cod_school_period'].
                self::whitId.$id);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message'=>self::taskPartialError],401);
            }
            return self::getSchoolPeriodById($id,$organizationId);
        }
        return response()->json(['message'=>self::notFoundSchoolPeriod],206);
    }

    public static function getCurrentSchoolPeriod($organizationId)
    {
        $currentSchoolPeriod = SchoolPeriod::getCurrentSchoolPeriod($organizationId);
        if (is_numeric($currentSchoolPeriod) && $currentSchoolPeriod ==0){
            return response()->json(['message' => self::taskError], 206);
        }
        if (count($currentSchoolPeriod)>0){
            return $currentSchoolPeriod[0];
        }
        return response()->json(['message'=>self::noCurrentSchoolPeriod],206);
    }

    public static function getSubjectsTaughtSchoolPeriod($teacherId,$organizationId)
    {
        $isValid=TeacherService::validateTeacher($teacherId,$organizationId);
        if ($isValid=='valid'){
            $currentSchoolPeriod= SchoolPeriod::getCurrentSchoolPeriod($organizationId);
            if (is_numeric($currentSchoolPeriod)&&$currentSchoolPeriod===0){
                return response()->json(['message' => self::taskError], 206);
            }
            if (count($currentSchoolPeriod)>0){
                $subjectsTaught = SchoolPeriodSubjectTeacher::getSchoolPeriodSubjectTeacherBySchoolPeriodTeacher($teacherId,
                    $currentSchoolPeriod[0]['id']);
                if (is_numeric($subjectsTaught)&&$subjectsTaught===0){
                    return response()->json(['message' => self::taskError], 206);
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
