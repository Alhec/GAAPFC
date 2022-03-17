<?php
/**
 * Created by PhpStorm.
 * User: halayon
 * Date: 24/08/19
 * Time: 03:04 PM
 */

namespace App\Services;

use App\Log;
use Illuminate\Http\Request;
use App\Subject;
use App\SchoolProgram;
use App\SchoolProgramSubject;
use Illuminate\Http\Response;

/**
 * @package : Services
 * @author : Hector Alayon
 * @version : 1.0
 */
class SubjectService
{
    const taskError = 'No se puede proceder con la tarea';
    const taskPartialError = 'No se pudo proceder con la tarea en su totalidad';
    const emptySubject = 'No existen asignaturas';
    const notFoundSubject = 'Asignatura no encontrada';
    const busySubjectCode = 'Codigo de asignatura en uso';
    const invalidProgram = 'Programas invalidos';
    const invalidSubjectGroup = 'Las asignaturas que se quieren asociar no se encuentran en el programa escolar';
    const ok ='OK';
    const logCreateSubject = 'Creo la asignatura ';
    const logUpdateSubject = 'Actualizo la asignatura ';
    const whitId = ' con id ';
    const logDeleteSubject = 'Elimino la asignatura ';

    /**
     *Lista todas las asignaturas que están asociadas a algún programa escolar que se encuentren en una organización con
     * el método Subject::getSubjects($organizationId).
     * @param string $organizationId Id de la organiación
     * @param integer $perPage Parámetro opcional, cantidad de elementos por página, default:0
     * @return array|Response Obtiene todas las asignaturas presentes en la organizacion.
     */
    public static function getSubjects($organizationId,$perPage=0)
    {
        $perPage == 0 ? $subjects = Subject::getSubjects($organizationId) :
            $subjects = Subject::getSubjects($organizationId,$perPage);
        if (is_numeric($subjects)&&$subjects == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        if ($perPage == 0){
            if (count($subjects)>0){
                return $subjects;
            }
            return response()->json(['message'=>self::emptySubject],206);
        }else{
            return $subjects;
        }
    }

    /**
     *Devuelve una asignatura dado su id y la organización donde se encuentran los programas asociados a ella con el
     * método Subject::getSubjectById($id,$organizationId).
     * @param string $id Id de la asignatura
     * @param string $organizationId Id de la organiación
     * @return Subject|Response Obtiene la asignatura dado su id en la organizacion.
     */
    public static function getSubjectById($id, $organizationId)
    {
        $subject = Subject::getSubjectById($id,$organizationId);
        if (is_numeric($subject)&&$subject == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (count($subject)>0){
            for ($i=0;$i<count($subject[0]['schoolPrograms']);$i++){
                $subjectGroup = SchoolProgramSubject::
                getSubjectGroup($subject[0]['schoolPrograms'][$i]['schoolProgramSubject']['subject_group']);
                $subjects = [];
                foreach ($subjectGroup as $subjectId){
                    if ($subjectId['subject_id']!=$subject[0]['id']){
                        $subjectBD = Subject::getSimpleSubjectById($subjectId['subject_id'],$organizationId);
                        if (is_numeric($subjectBD)&&$subjectBD===0){
                            return response()->json(['message'=>self::taskError],500);
                        }
                        if (count($subjectBD)>0){
                            $subjects[] = $subjectBD[0];
                        }
                    }
                }
                if (count($subjects)>0){
                    $subject[0]['schoolPrograms'][$i]['schoolProgramSubject']['group']=$subjects;
                }
            }
            return $subject[0];
        }
        return response()->json(['message'=>self::notFoundSubject],206);
    }

    /**
     *Valida que se cumpla las restricciones:
     * *code: requerido y máximo 10
     * *name: requerido  y máximo 50
     * *uc: requerido y numérico
     * *is_final_subject: boleano
     * *is_project_subject: boleano
     * *theoretical_hours: requerido y numérico
     * *practical_hours: requerido y numérico
     * *laboratory_hours: requerido y numérico
     * *school_programs.*.school_program_id: requerido y numérico
     * *school_programs.*.type: requerido, máximo 2 y debe terminar en EL, OP o OB
     * *school_programs.*.with_subject.*.subject_id: numérico
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validate(Request $request)
    {
         $request->validate([
             'code'=>'required|max:10',
             'name'=>'required|max:50',
             'uc'=>'required|numeric',
             'is_final_subject'=>'boolean',
             'is_project_subject'=>'boolean',
             'theoretical_hours'=>'required|numeric',
             'practical_hours'=>'required|numeric',
             'laboratory_hours'=>'required|numeric',
             'school_programs.*.school_program_id'=>'required|numeric',
             'school_programs.*.type'=>'required|max:2|ends_with:EL,OP,OB',
             'school_programs.*.with_subjects.*.subject_id'=>'numeric',
        ]);
    }

    /**
     * Valida que los id de los programas escolares se encuentren asociados a la organización.
     * @param SchoolProgram $schoolPrograms Array de la petición con ids de programas escolares
     * @param string $organizationId Id de la organiación
     * @return integer|boolean Devuelve un booleano si los programas escolares pertenecen a la organizacion en caso de
     * existir un error devolvera 0.
     */
    public static function validateSchoolProgram($schoolPrograms,$organizationId){
        $schoolProgramsId = [];
        $schoolProgramsInBd= SchoolProgram::getSchoolProgram($organizationId);
        if (is_numeric($schoolProgramsInBd) && $schoolProgramsInBd === 0){
            return 0;
        }
        if (count($schoolProgramsInBd)<1){
            return false;
        }
        foreach ($schoolProgramsInBd as $schoolProgramInBd){
            $schoolProgramsId[]=$schoolProgramInBd['id'];
        }
        foreach ($schoolPrograms as $schoolProgram){
            if (!in_array($schoolProgram['school_program_id'],$schoolProgramsId)){
                return false;
            }
        }
        return true;
    }

    /**
     * En  caso de que alguna de las asignaturas sean de tipo proyecto o trabajo de grado como pertenecen a un mismo
     * grupo de asignaturas y se deben ver juntas estas también serán del mismo tipo (ejemplo postgrado de geoquímica,
     * el proyecto debe inscribirse con el seminario, por tanto la asignatura seminario será de proyecto seminario) con
     * el método Subject::updateSubjectLikeArray($subjectAssociatedId,$subjectAssociated[0]->toArray()).
     * @param integer $subjectId Id de la asignatura relacionada
     * @param string $organizationId Id de la organiación
     * @param string $subjectAssociatedId Id de la materia creada
     * @return integer de ocurrir un error devolvera 0.
     */
    public static function updateSubjectToFinalOrProject($subjectId,$organizationId,$subjectAssociatedId)
    {
        $subject = Subject::getSimpleSubjectById($subjectId,$organizationId);
        if (is_numeric($subject)&&$subject===0){
            return 0;
        }
        $subjectAssociated = Subject::getSimpleSubjectById($subjectAssociatedId,$organizationId);
        if (is_numeric($subjectAssociated)&&$subjectAssociated===0){
            return 0;
        }
        if ($subject[0]['is_final_subject'] || $subject[0]['is_project_subject']){
            if ($subject[0]['is_final_subject']){
                $subjectAssociated[0]['is_final_subject']=$subject[0]['is_final_subject'];
            }else if ($subject[0]['is_project_subject']){
                $subjectAssociated[0]['is_project_subject']=$subject[0]['is_project_subject'];
            }
            $result=Subject::updateSubjectLikeArray($subjectAssociatedId,$subjectAssociated[0]->toArray());
            if (is_numeric($result) && $result === 0){
                return 0;
            }
        }else if ($subjectAssociated[0]['is_final_subject'] || $subjectAssociated[0]['is_project_subject']){

            if ($subjectAssociated[0]['is_final_subject']){
                $subject[0]['is_final_subject']=$subjectAssociated[0]['is_final_subject'];
            }else if($subjectAssociated[0]['is_project_subject']){
                $subject[0]['is_project_subject']=$subjectAssociated[0]['is_project_subject'];
            }
            $result=Subject::updateSubjectLikeArray($subjectId,$subject[0]->toArray());
            if (is_numeric($result) && $result === 0){
                return 0;
            }
        }
    }

    /**
     * Crea una asociación entre programa escolar y asignaturas, y tambien si estas asignaturas están asociadas con
     * otras, (como el caso explicado del proyecto y seminario en uno de los programas escolares del posgrado de
     * geoquímica donde estas asignaturas deben inscribirse en conjunto) haciendo uso del método
     * SchoolProgramSubject::addSchoolProgramSubject([
     * 'school_program_id'=>$schoolProgram['id'],
     * 'subject_id'=>$subjectId,
     * 'type'=>$schoolProgram['type']
     * ]).
     * Luego de agregarla a base de datos se edita para definir en qué grupo de asignaturas debe asociarse
     * (subject_group); si no está asociada a un grupo se coloca el id de la entidad.
     * @param integer $schoolPrograms Array de la petición con ids de programas escolares
     * @param integer $subjectId Id de la asignatura
     * @param string $organizationId Id de la organiación
     * @return integer de ocurrir un error devolvera 0.
     */
    public static function addSchoolProgramInSubject($schoolPrograms, $subjectId,$organizationId)
    {
        foreach ($schoolPrograms as $schoolProgram){
            $schoolProgramSubjectId = SchoolProgramSubject::addSchoolProgramSubject([
                'school_program_id'=>$schoolProgram['school_program_id'],
                'subject_id'=>$subjectId,
                'type'=>$schoolProgram['type']
            ]);
            if (is_numeric($schoolProgramSubjectId )&&$schoolProgramSubjectId ==0){
                return 0;
            }else{
                $result = self::updateSchoolProgramSubject($schoolProgramSubjectId,$schoolProgram['school_program_id'],
                    $subjectId,$schoolProgram['type'],$schoolProgramSubjectId);
                if (is_numeric($result) && $result == 0){
                    return 0;
                }
            }
            if (isset($schoolProgram['with_subjects'])){
                foreach($schoolProgram['with_subjects'] as $withSubjectId){
                    $subject = SchoolProgramSubject::getSchoolProgramSubjectBySubjectAndSchoolProgram(
                        $withSubjectId['subject_id'],$schoolProgram['school_program_id']);
                    if (is_numeric($subject) && $subject ===0){
                        return 0;
                    }
                    if (count($subject)>0){
                        $subject = $subject[0];
                        $result=self::updateSchoolProgramSubject($subject['id'],$subject['school_program_id'],
                            $subject['subject_id'],$subject['type'],$schoolProgramSubjectId);
                        if (is_numeric($result) && $result === 0){
                            return 0;
                        }
                        if ($subject['subject_group']!==$schoolProgramSubjectId){
                            $subjectsInGroup = SchoolProgramSubject::getSubjectGroup($subject['subject_group']);
                            if (is_numeric($subjectsInGroup)&&$subjectsInGroup ===0 ){
                                return 0;
                            }
                            if (count($subjectsInGroup)>0){
                                foreach ($subjectsInGroup as $subjectInGroup){
                                   $result = self::updateSchoolProgramSubject($subjectInGroup['id'],
                                       $subjectInGroup['school_program_id'],$subjectInGroup['subject_id'],
                                       $subjectInGroup['type'],$subjectInGroup['id']);
                                    if (is_numeric($result) && $result == 0){
                                        return 0;
                                    }
                                }
                            }
                        }
                        $result = self::updateSubjectToFinalOrProject($withSubjectId['subject_id'],$organizationId,
                            $subjectId);
                        if (is_numeric($result) && $result === 0){
                            return 0;
                        }
                    }
                }
            }
        }
    }

    /**
     * Actualiza una asociación entre programa escolar y asignaturas, y tambien si estas asignaturas están asociadas con
     * otras, (como el caso explicado del proyecto y seminario en uno de los programas escolares del posgrado de
     * geoquímica donde estas asignaturas deben inscribirse en conjunto) haciendo uso del método
     * SchoolProgramSubject::updateSchoolProgramSubject($id,
     * [
     * 'school_program_id'=>$schoolProgramId,
     * 'subject_id'=>$subjectId,
     * 'type'=>$type,
     * 'subject_group'=>$subjectGroup
     * ]).
     * @param integer $id Id de la entidad schoolProgramSubject
     * @param integer $schoolProgramId d del programa escolar asociado
     * @param integer $subjectId Id de la asignatura relacionada
     * @param string $type Tipo de asignatura en el programa escolar
     * @param integer $subjectGroup Id del grupo asociado al cual pertenece
     * @return integer de ocurrir un error devolvera 0.
     */
    public static function updateSchoolProgramSubject($id,$schoolProgramId,$subjectId,$type,$subjectGroup)
    {
        $result=SchoolProgramSubject::updateSchoolProgramSubject($id,
            [
                'school_program_id'=>$schoolProgramId,
                'subject_id'=>$subjectId,
                'type'=>$type,
                'subject_group'=>$subjectGroup
            ]);
        if (is_numeric($result) && $result == 0){
            return 0;
        }
    }

    /**
     * Valida que las asignaturas agrupadas en cada programa escolar se encuentren en dicho programa antes de asociarse.
     * @param SchoolProgram $schoolPrograms Array con programas escolares de la petición
     * @param string $organizationId Id de la organiación
     * @return integer|boolean Devuelve un booleano si las asignaturas asociadas pertenecen a los programas escolares
     * en la organizacion en caso de existir un error devolvera 0.
     */
    public static function validateSubjectGroup($schoolPrograms,$organizationId)
    {
        foreach ($schoolPrograms as $schoolProgram){
            if (isset($schoolProgram['with_subjects'])){
                $subjectsInBd = Subject::getSubjectsBySchoolProgram($schoolProgram['school_program_id'],$organizationId);
                if (is_numeric($subjectsInBd) && $subjectsInBd==0){
                    return 0;
                }
                if (count($subjectsInBd)<1){
                    return false;
                }
                $subjectsId = array_column($schoolProgram['with_subjects'],'subject_id');
                $subjectsIdInBd = array_column($subjectsInBd->toArray(),'id');
                foreach ($subjectsId as $subjectId){
                    if (!in_array($subjectId, $subjectsIdInBd)){
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Agrega una asignatura en un programa escolar de la organización  con el método Subject::addSubject($request).
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @return Response|Subject de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcta
     * devolvera el objeto Subject.
     */
    public static function addSubject(Request $request,$organizationId)
    {
        self::validate($request);
        $result =Subject::existSubjectByCode($request['code'],$organizationId);
        if (is_numeric($result)&& $result == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (!$result){
            $validateSchoolProgram = self::validateSchoolProgram($request['school_programs'],$organizationId);
            if (is_numeric($validateSchoolProgram) && $validateSchoolProgram === 0){
                return response()->json(['message'=>self::taskError],500);
            }
            if ($validateSchoolProgram){
                $validateSubjectGroup = self::validateSubjectGroup($request['school_programs'],$organizationId);
                if($validateSubjectGroup === 0){
                    return response()->json(['message'=>self::taskError],500);
                }
                if ($validateSubjectGroup){
                    $id = Subject::addSubject($request);
                    if ($id === 0){
                        return response()->json(['message'=>self::taskError],500);
                    }
                    $result= self::addSchoolProgramInSubject($request['school_programs'],$id,$organizationId);
                    if (is_numeric($result)&& $result == 0){
                        return response()->json(['message' => self::taskPartialError], 500);
                    }
                    $log = Log::addLog(auth('api')->user()['id'],self::logCreateSubject.$request['name'].self::whitId.
                        $id);
                    if (is_numeric($log)&&$log==0){
                        return response()->json(['message' => self::taskPartialError], 500);
                    }
                    return self::getSubjectById($id,$organizationId);
                }
                return response()->json(['message'=>self::invalidSubjectGroup],206);
            }
            return response()->json(['message'=>self::invalidProgram],206);
        }
        return response()->json(['message'=>self::busySubjectCode],206);
    }

    /**
     * Elimina una asignatura si existe en la organización dada usando el método Subject::deleteSubject($id).
     * @param string $id Id de la asignatura
     * @param string $organizationId Id de la organiación
     * @return Response, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcto
     * devolvera un objeto con mensaje OK.
     */
    public static function deleteSubject($id,$organizationId)
    {
        $subject = Subject::getSubjectById($id,$organizationId);
        if (is_numeric($subject)&& $subject == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (count($subject)>0){
            $result = Subject::deleteSubject($id);
            if (is_numeric($result)&& $result == 0){
                return response()->json(['message'=>self::taskError],500);
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logDeleteSubject.$subject[0]['name'].self::whitId.
                $subject[0]['id']);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message' => self::taskError], 500);
            }
            return response()->json(['message'=>self::ok]);
        }
        return response()->json(['message'=>self::notFoundSubject],206);
    }

    /**
     * Modifica el subjectGroup de las entidades que alguna vez estuvieron asociadas asignándoles sus id como
     * subjectGroup usando el metodo self::updateSchoolProgramSubject($subjectGroup['id'],
     * $subjectGroup['school_program_id'],$subjectGroup['subject_id'],$subjectGroup['type'],$subjectGroup['id']).
     * @param object $subject información de la asignatura y el programa escolar.
     * @return integer de ocurrir un error devolvera 0
     */
    public static function defineNewSubjectGroup($subject){
        $schoolProgramSubject = SchoolProgramSubject::getSchoolProgramSubjectBySubjectAndSchoolProgram(
            $subject['subject_id'],$subject['school_program_id']);
        if (is_numeric($schoolProgramSubject)&&$schoolProgramSubject===0){
            return 0;
        }
        if (count($schoolProgramSubject)>0){
            $subjectsGroup = SchoolProgramSubject::getSubjectGroup($schoolProgramSubject[0]['subject_group']);
            if (is_numeric($subjectsGroup)&&$subjectsGroup===0){
                return 0;
            }
            if (count($subjectsGroup)>0){
                foreach ($subjectsGroup as $subjectGroup){
                    if ($subjectGroup['subject_id']!=$subject['subject_id']){
                        $result = self::updateSchoolProgramSubject($subjectGroup['id'],
                            $subjectGroup['school_program_id'], $subjectGroup['subject_id'],$subjectGroup['type'],
                            $subjectGroup['id']);
                        if ($result ===0){
                            return 0;
                        }
                    }
                }
            }
        }
    }

    /**
     * Actualiza una asociación entre programa escolar y asignaturas, y tambien si estas asignaturas están asociadas con
     * otras, (como el caso explicado del proyecto y seminario en uno de los programas escolares del posgrado de
     * geoquímica donde estas asignaturas deben inscribirse en conjunto) haciendo uso del método
     * SchoolProgramSubject::updateSchoolProgramSubject($schoolProgramSubjectInBd['id'],$schoolProgram)
     * @param SchoolProgram $schoolPrograms Array de la petición con ids de programas escolares
     * @param integer $subjectId Id de la asignatura relacionada
     * @param string $organizationId Id de la organiación
     * @return integer de ocurrir un error devolvera 0
     */
    public static function updateSchoolProgramSubjectsInSubject($schoolPrograms, $subjectId,$organizationId)
    {
        $subjectId = intval($subjectId);
        $schoolProgramSubjectsInBd = SchoolProgramSubject::getSchoolProgramSubjectsBySubjectId($subjectId);
        if (is_numeric($schoolProgramSubjectsInBd)&& $schoolProgramSubjectsInBd == 0){
            return 0;
        }
        $schoolProgramSubjectsUpdated = [];
        foreach ($schoolPrograms as $schoolProgram){
            $existSchoolProgram = false;
            foreach ($schoolProgramSubjectsInBd as $schoolProgramSubjectInBd){
                if ($schoolProgramSubjectInBd['school_program_id']==$schoolProgram['school_program_id']){
                    $schoolProgram['subject_id']=$subjectId;
                    if (isset($schoolProgram['with_subjects'])){
                        $schoolProgram['subject_group']=$schoolProgramSubjectInBd['subject_group'];
                        $result = SchoolProgramSubject::updateSchoolProgramSubject($schoolProgramSubjectInBd['id'],
                            $schoolProgram);
                        if (is_numeric($result)&& $result == 0){
                            return 0;
                        }
                        $schoolProgramSubjectsUpdated[] = $schoolProgramSubjectInBd['id'];
                        $subjectGroup = SchoolProgramSubject::getSubjectGroup($schoolProgram['subject_group']);
                        if (is_numeric($subjectGroup)&&$subjectGroup===0){
                            return 0;
                        }
                        if (count($subjectGroup)>0){
                            $subjectGroupSubjectId = array_column($subjectGroup->toArray(),'subject_id');
                            $subjectsJoin = [$subjectId];
                            foreach ($schoolProgram['with_subjects'] as $subjectIdJoin){
                                if (!in_array($subjectIdJoin['subject_id'],$subjectGroupSubjectId)){
                                    $schoolProgramSubject =
                                        SchoolProgramSubject::getSchoolProgramSubjectBySubjectAndSchoolProgram(
                                        $subjectIdJoin['subject_id'],$schoolProgram['school_program_id']);
                                    if (is_numeric($schoolProgramSubject)&&$schoolProgramSubject==0){
                                        return 0;
                                    }
                                    $schoolProgramSubject[0]['subject_group']=$schoolProgram['subject_group'];
                                    $result=SchoolProgramSubject::updateSchoolProgramSubject(
                                        $schoolProgramSubject[0]['id'], $schoolProgramSubject[0]->toArray());
                                    if (is_numeric($result)&& $result == 0){
                                        return 0;
                                    }
                                    $subjectsJoin[] = $subjectIdJoin['subject_id'];
                                }else{
                                    $subjectsJoin[] = $subjectIdJoin['subject_id'];
                                }
                                $result = self::updateSubjectToFinalOrProject($subjectIdJoin['subject_id'],
                                    $organizationId,$subjectId);
                                if (is_numeric($result)&& $result == 0){
                                    return 0;
                                }
                            }
                            foreach ($subjectGroup as $aSubjectGroup){
                                if (!in_array($aSubjectGroup['subject_id'],$subjectsJoin)){
                                    $schoolProgramSubject =
                                        SchoolProgramSubject::getSchoolProgramSubjectBySubjectAndSchoolProgram(
                                        $aSubjectGroup['subject_id'],$schoolProgram['school_program_id']);
                                    if (is_numeric($schoolProgramSubject)&&$schoolProgramSubject==0){
                                        return 0;
                                    }
                                    $schoolProgramSubject[0]['subject_group']=$schoolProgramSubject[0]['id'];
                                    $result=
                                        SchoolProgramSubject::updateSchoolProgramSubject($schoolProgramSubject[0]['id'],
                                        $schoolProgramSubject[0]->toArray());
                                    if (is_numeric($result)&& $result == 0){
                                        return 0;
                                    }
                                }
                            }
                        }
                    }else{
                        $result = self::defineNewSubjectGroup($schoolProgram);
                        if (is_numeric($result)&& $result == 0){
                            return 0;
                        }
                        $schoolProgram['subject_group']=$schoolProgramSubjectInBd['id'];
                        $result = SchoolProgramSubject::updateSchoolProgramSubject($schoolProgramSubjectInBd['id'],
                            $schoolProgram);
                        if (is_numeric($result)&& $result == 0){
                            return 0;
                        }
                    }
                    $schoolProgramSubjectsUpdated[]=$schoolProgramSubjectInBd['id'];
                    $existSchoolProgram = true;
                    break;
                }
            }
            if ($existSchoolProgram == false) {
                self::addSchoolProgramInSubject([$schoolProgram],$subjectId,$organizationId);
                $idSchoolProgramSubject = SchoolProgramSubject::getSchoolProgramSubjectBySubjectAndSchoolProgram(
                    $subjectId, $schoolProgram['school_program_id']);
                if (is_numeric($idSchoolProgramSubject)&&$idSchoolProgramSubject==0){
                    return 0;
                }
                $postgraduatesUpdated[]=$idSchoolProgramSubject[0]['id'];
            }
        }
        foreach ($schoolProgramSubjectsInBd as $schoolProgramId){
            if (!in_array($schoolProgramId['id'],$schoolProgramSubjectsUpdated)){
               $result = SchoolProgramSubject::deleteSchoolProgramSubject($schoolProgramId['id']);
                if (is_numeric($result)&& $result == 0){
                    return 0;
                }
            }
        }
    }

    /**
     * Edita una asignaturas en un programa escolar de la organización  con el método
     * Subject::updateSubject($id,$request).
     * @param Request $request Objeto con los datos de la petición
     * @param string $id Id de la asignatura
     * @param string $organizationId Id de la organiación
     * @return Response|Subject de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcta
     * devolvera el objeto Subject.
     */
    public static function updateSubject(Request $request, $id,$organizationId)
    {
        self::validate($request);
        $result = Subject::existSubjectById($id,$organizationId);
        if (is_numeric($result)&& $result == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        if($result){
            $validateSchoolProgram = self::validateSchoolProgram($request['school_programs'],$organizationId);
            if (is_numeric($validateSchoolProgram) && $validateSchoolProgram === 0){
                return response()->json(['message'=>self::taskError],500);
            }
            if ($validateSchoolProgram){
                $subjectCode = Subject::getSubjectByCode($request['code'],$organizationId);
                if (is_numeric($subjectCode)&& $subjectCode == 0){
                    return response()->json(['message'=>self::taskError],500);
                }
                if (count($subjectCode)>0) {
                    if ($subjectCode[0]['id'] != $id) {
                        return response()->json(['message' => self::busySubjectCode], 206);
                    }
                }
                $validateSubjectGroup = self::validateSubjectGroup($request['school_programs'],$organizationId);
                if($validateSubjectGroup === 0){
                    return response()->json(['message'=>self::taskError],500);
                }
                if ($validateSubjectGroup){
                    $result = Subject::updateSubject($id,$request);
                    if (is_numeric($result)&& $result == 0){
                        return response()->json(['message'=>self::taskError],500);
                    }
                    $result = self::updateSchoolProgramSubjectsInSubject($request['school_programs'],$id,
                        $organizationId);
                    if (is_numeric($result)&& $result == 0){
                        return response()->json(['message' => self::taskPartialError], 500);
                    }
                    $log = Log::addLog(auth('api')->user()['id'],self::logUpdateSubject.$request['name'].self::whitId.
                        $id);
                    if (is_numeric($log)&&$log==0){
                        return response()->json(['message' => self::taskPartialError], 500);
                    }
                    return self::getSubjectById($id,$organizationId);
                }
                return response()->json(['message'=>self::invalidSubjectGroup],206);
            }
            return response()->json(['message'=> self::invalidProgram],206);
        }
        return response()->json(['message'=> self::notFoundSubject],206);
    }

    /**
     *Lista todas las asignaturas que están asociadas al id del programa escolar que se encuentren en una organización
     * con el método Subject::getSubjectsBySchoolProgram($schoolProgramId,$organizationId).
     * @param string $schoolProgramId: Id del programa escolar
     * @param string $organizationId Id de la organiación
     * @param integer $perPage Parámetro opcional, cantidad de elementos por página, default:0
     * @return Subject|Response Obtiene la asignatura asociadas a un programaescolar dado el id del programa escolar en
     * la organizacion.
     */
    public static function getSubjectsBySchoolProgramId($schoolProgramId, $organizationId,$perPage=0)
    {
        $perPage == 0 ? $subjects = Subject::getSubjectsBySchoolProgram($schoolProgramId,$organizationId) :
            $subjects = Subject::getSubjectsBySchoolProgram($schoolProgramId,$organizationId,$perPage);
        if (is_numeric($subjects)&&$subjects===0){
            return response()->json(['message'=>self::taskError],500);
        }
        if ($perPage == 0){
            if (count($subjects)>0){
                return $subjects;
            }
            return response()->json(['message'=>self::emptySubject],206);
        }else{
            return $subjects;
        }
    }

    /**
     *Lista todas las asignaturas que están asociadas a algún programa escolar que se encuentren en una organización sin
     * el proyecto y sin el trabajo especial de grado con el método
     * Subject::getSubjectsWithoutFinalWorks($organizationId).
     * @param string $organizationId Id de la organiación
     * @param integer $perPage Parámetro opcional, cantidad de elementos por página, default:0
     * @return object|Response Obtiene las asignaturas sin las asignaturas del proyecto y los trabajos especiales de
     * grado en la organizacion.
     */
    public static function getSubjectsWithoutFinalWorks($organizationId, $perPage=0)
    {
        $perPage == 0 ? $subjects = Subject::getSubjectsWithoutFinalWorks($organizationId) :
            $subjects = Subject::getSubjectsWithoutFinalWorks($organizationId,$perPage);
        if (is_numeric($subjects)&&$subjects == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        if ($perPage == 0){
            if (count($subjects)>0){
                return $subjects;
            }
            return response()->json(['message'=>self::emptySubject],206);
        }else{
            return $subjects;
        }
    }
}
