<?php
/**
 * Created by PhpStorm.
 * User: halayon
 * Date: 30/08/19
 * Time: 09:08 AM
 */

namespace App\Services;

use App\Advisor;
use App\FinalWork;
use App\FinalWorkSchoolPeriod;
use App\SchoolProgram;
use App\Subject;
use Illuminate\Http\Request;
use App\SchoolPeriodStudent;
use App\Student;
use App\SchoolPeriod;
use App\SchoolPeriodSubjectTeacher;
use App\StudentSubject;


class InscriptionService
{

    const taskError = 'No se puede proceder con la tarea';
    const taskPartialError = 'No se pudo proceder con la tarea en su totalidad';
    const emptyInscriptions ='No hay inscripciones';
    const notFoundInscription = 'Inscripcion no encontrada';
    const emptyInscriptionInCurrentSchoolPeriod = 'Periodo escolar no posee inscripciones';
    const OK = 'OK';
    const notFoundStudentGivenId = 'No existe el estudiante dado el id';
    const thereAreNoSubjectsAvailabletoRegister = 'No hay materias disponibles para inscribir';
    const thereAreSchoolPeriodWithoutPaying = 'Hay periodo escolar sin pagar';
    const warningAverage = 'tu promedio es inferior a 14, te sugerimos que vuelvas a ver una materia reprobada o una 
    materia ya aprobada si no lo has hecho antes, te encuentras en periodo de prueba en este semestre';
    const notAllowedRegister = 'No tienes permitido inscribir';
    const endProgram = 'Ya culmino el programa';
    const noCurrentSchoolPeriod='No hay periodo escolar en curso';
    const invalidSubject = 'Materia invalida';
    const notAvailableInscriptions = 'No estan disponibles las inscripciones';
    const inscriptionReady = 'Inscripcion ya realizada';
    const invalidRelation = 'Relacion Invalida';
    const invalidData = 'Datos invalidos';
    const busySchoolPeriodStudent ='El estudiante ya esta inscrito en el periodo escolar y se encuentra en otro registro';
    const expiredDate = 'No se puede realizar retiros la fecha ya ha pasado';
    const notCurrentInscription ='No hay inscripcion actual para usted';

    public static function getInscriptions($organizationId)
    {
        $inscriptions = SchoolPeriodStudent::getSchoolPeriodStudent($organizationId);
        if (is_numeric($inscriptions)&&$inscriptions==0){
            return response()->json(['message' => self::taskError], 206);
        }
        if (count($inscriptions)>0){
            return ($inscriptions);
        }
        return response()->json(['message'=>self::emptyInscriptions],206);
    }

    public static function getInscriptionById($id,$organizationId)
    {
        $inscription = SchoolPeriodStudent::getSchoolPeriodStudentById($id,$organizationId);
        if (is_numeric($inscription)&&$inscription==0){
            return response()->json(['message' => self::taskError], 206);
        }
        if (count($inscription)>0){
            return ($inscription)[0];
        }
        return response()->json(['message'=>self::notFoundInscription],206);
    }

    public static function getInscriptionsBySchoolPeriod($schoolPeriodId,$organizationId)
    {
        $inscriptions = SchoolPeriodStudent::getSchoolPeriodStudentBySchoolPeriod($schoolPeriodId,$organizationId);
        if (is_numeric($inscriptions)&&$inscriptions==0){
            return response()->json(['message' => self::taskError], 206);
        }
        if (count($inscriptions)>0){
            return ($inscriptions);
        }
        return response()->json(['message'=>self::emptyInscriptionInCurrentSchoolPeriod],206);
    }

    public static function getUnregisteredSubjects($studentId,$subjectsInSchoolPeriod)
    {
        $allSubjectsEnrolled = StudentSubject::getAllSubjectsEnrolledWithoutRET($studentId);
        $allSubjectsEnrolledId = array_column( $allSubjectsEnrolled->toArray(),'id');
        if (is_numeric($allSubjectsEnrolled) && $allSubjectsEnrolled==0){
            return 0;
        }
        $availableSubjects=[];
        foreach ($subjectsInSchoolPeriod as $subjectInSchoolPeriod){
            if ($subjectInSchoolPeriod['enrolled_students']<$subjectInSchoolPeriod['limit']){
                if (count($allSubjectsEnrolledId)>0){
                    if (!in_array($subjectInSchoolPeriod['subject_id'],$allSubjectsEnrolledId)){
                        $availableSubjects[]=$subjectInSchoolPeriod;
                    }
                }else{
                    $availableSubjects[]=$subjectInSchoolPeriod;
                }
            }
        }

        return $availableSubjects;
    }

    public static function filterSubjectsBySchoolProgram($student, $organizationId, $availableSubjects)
    {
        $subjectsInSchoolProgram = Subject::getSubjectsBySchoolProgram($student['school_program_id'],$organizationId);
        if (is_numeric($subjectsInSchoolProgram)&&$subjectsInSchoolProgram==0){
            return 0;
        }
        $subjectsInSchoolProgramId = array_column( $subjectsInSchoolProgram->toArray(),'id');
        $availableSubjectsInSchoolProgram=[];
        foreach ($availableSubjects as $availableSubject){
            if (in_array($availableSubject['subject_id'],$subjectsInSchoolProgramId)){
                $availableSubjectsInSchoolProgram[]=$availableSubject;
            }
        }
        return $availableSubjectsInSchoolProgram;
    }

    public static function filterSubjectsEnrolledInSchoolPeriod($studentId,$schoolPeriodId,$availableSubjects)
    {
        $enrolledSubjects = StudentSubject::getEnrolledSubjectsBySchoolPeriodStudent($studentId,$schoolPeriodId);
        if (is_numeric($enrolledSubjects)&&$enrolledSubjects==0){
            return 0;
        }
        if (count($enrolledSubjects)>0){
            $filterSubjectsEnrolled = [];
            foreach ($availableSubjects as $availableSubject){
                $subjectFound=false;
                foreach ($enrolledSubjects as $enrolledSubject){
                    if ($availableSubject['subject_id']==$enrolledSubject['dataSubject']['subject_id']){
                        $subjectFound = true;
                        break;
                    }
                }
                if ($subjectFound ==false){
                    $filterSubjectsEnrolled[]=$availableSubject;
                }
            }
            return $filterSubjectsEnrolled;
        }
        return $availableSubjects;
    }

    public static function getAvailableSubjects($studentId,$schoolPeriodId,Request $request,$organizationId,$internalCall)
    {
        $student = Student::getStudentById($studentId,$organizationId);
        if (is_numeric($student)&&$student==0){
            return response()->json(['message' => self::taskError], 206);
        }
        if (count($student)>0){
            $student=$student[0];
            if ($student['current_status']!='REG' && $student['current_status']!='REI-A'
                && $student['current_status']!='REI-B' && $student['current_status']!='RIN-A'
                && $student['current_status']!='RIN-B'){
                if ($internalCall){
                    return [];
                }
                return response()->json(['message' => self::notAllowedRegister], 206);
            }
            if ($student['end_program']==true){
                if ($internalCall){
                    return [];
                }
                return response()->json(['message' => self::endProgram], 206);
            }
            $thereIsUnpaidSchoolPeriod=SchoolPeriodStudent::isThereUnpaidSchoolPeriod($studentId);
            if (is_numeric($thereIsUnpaidSchoolPeriod)&&$thereIsUnpaidSchoolPeriod==0){
                if ($internalCall){
                    return 0;
                }
                return response()->json(['message' => self::taskError], 206);
            }
            if (!$thereIsUnpaidSchoolPeriod){
                $subjectsInSchoolPeriod = SchoolPeriodSubjectTeacher::getSchoolPeriodSubjectTeacherBySchoolPeriod($schoolPeriodId);
                if (is_numeric($subjectsInSchoolPeriod)&&$subjectsInSchoolPeriod==0){
                    if ($internalCall){
                        return 0;
                    }
                    return response()->json(['message' => self::taskError], 206);
                }
                if (count($subjectsInSchoolPeriod)>0){
                    $unregisteredSubjects = self::getUnregisteredSubjects($studentId,$subjectsInSchoolPeriod);
                    if (is_numeric($unregisteredSubjects)&&$unregisteredSubjects==0){
                        if ($internalCall){
                            return 0;
                        }
                        return response()->json(['message' => self::taskError], 206);
                    }
                    if (count($unregisteredSubjects)>0){
                        $filterSubjectsBySchoolProgram = self::filterSubjectsBySchoolProgram($student,$organizationId,$unregisteredSubjects);
                        if(is_numeric($filterSubjectsBySchoolProgram)&&$filterSubjectsBySchoolProgram==0){
                            if ($internalCall){
                                return 0;
                            }
                            return response()->json(['message' => self::taskError], 206);
                        }
                        if (count($filterSubjectsBySchoolProgram)>0){
                            $availableSubjects= self::filterSubjectsEnrolledInSchoolPeriod($studentId,$schoolPeriodId,$filterSubjectsBySchoolProgram);
                            if (is_numeric($availableSubjects)&&$availableSubjects==0){
                                if ($internalCall){
                                    return 0;
                                }
                                return response()->json(['message' => self::taskError], 206);
                            }
                            if (count($availableSubjects)>0){
                                $totalQualification = self::getTotalQualification($studentId);
                                if (is_string($totalQualification)&&$totalQualification=='e'){
                                    if ($internalCall){
                                        return 0;
                                    }
                                    return response()->json(['message' => self::taskError], 206);
                                }
                                $cantSubjectsEnrolled=StudentSubject::cantAllSubjectsEnrolledWithoutRETCUR($studentId);
                                if (is_string($cantSubjectsEnrolled)&&$cantSubjectsEnrolled=='e'){
                                    if ($internalCall){
                                        return 0;
                                    }
                                    return response()->json(['message' => self::taskError], 206);
                                }
                                if($cantSubjectsEnrolled>0 && ($totalQualification/$cantSubjectsEnrolled)<14){
                                    $response['message']=self::warningAverage;
                                }
                                $response['available_subjects']=$availableSubjects;
                                if (!$internalCall){
                                    $approvedProject = FinalWork::existApprovedFinalWork($studentId, true);
                                    if(is_numeric($approvedProject)&&$approvedProject==0){
                                        return 0;
                                    }
                                    if ($approvedProject==true){
                                        $response['available_tesis']=true;
                                    }else{
                                        $availableProject = self::availableProject($student,$organizationId);
                                        if (is_numeric($availableProject)&&$availableProject==0){
                                            return 0;
                                        }
                                        if ($availableProject==true){
                                            $response['available_project']=true;
                                        }
                                    }
                                }
                                return $response;
                            }
                        }
                    }
                }
                if ($internalCall){
                    return [];
                }
                return response()->json(['message'=>self::thereAreNoSubjectsAvailabletoRegister],206);
            }
            if ($internalCall){
                return [];
            }
            return response()->json(['message'=>self::thereAreSchoolPeriodWithoutPaying],206);

        }
        return response()->json(['message'=>self::notFoundStudentGivenId],206);
    }

    public static function availableProject($student, $organizationId){
        $schoolProgram=SchoolProgram::getSchoolProgramById($student['school_program_id'],$organizationId);
        if (is_numeric($schoolProgram) && $schoolProgram==0){
            return 0;
        }
        $cantSchoolPeriods = SchoolPeriodStudent::getCantEnrolledSchoolPeriodByStudent($student['id'],$organizationId);
        if (is_string($cantSchoolPeriods)&&$cantSchoolPeriods==='e'){
            return 0;
        }
        $enrolledSubjects = SchoolPeriodStudent::getEnrolledSchoolPeriodsByStudent($student['id'],$organizationId);
        if (is_numeric($enrolledSubjects)&&$enrolledSubjects==0){
            return response()->json(['message'=>self::taskError],206);
        }
        if (count($enrolledSubjects)>0){
            $dataPercentageStudent=ConstanceService::stadisticsDataHistorical($enrolledSubjects);
            if ($cantSchoolPeriods>=$schoolProgram[0]['min_duration'] &&
                $dataPercentageStudent['enrolled_credits']>=$schoolProgram[0]['min_num_cu_final_work'] ){
                return true;
            }
        }

    }

    public static function validate(Request $request)
    {
        $request->validate([
            'student_id'=>'required|numeric',
            'school_period_id'=>'required|numeric',
            'status'=>'max:5|ends_with:RET-A,RET-B,DES-A,DES-B,RIN-A,RIN-B,REI-A,REI-B,REG',//REI REINCORPORADO RIN REINGRESO
            'pay_ref'=>'max:50',
            'financing'=>'max:3|ends_with:EXO,SFI,SCS,FUN',//EXO exonerated, FUN Funded, SFI Self-financing, SCS Scholarship
            'amount_paid'=>'numeric',
            'financing_description'=>'max:60',
            'subjects.*.school_period_subject_teacher_id'=>'required|numeric',
            'subjects.*.status'=>'max:3|ends_with:CUR,RET,APR,REP',
            'subjects.*.qualification'=>'numeric'
        ]);
    }

    public static function validateRelation($organizationId,Request $request)
    {
        $student= Student::getStudentById($request['student_id'],$organizationId);
        if(is_numeric($student)&&$student==0){
            return 0;
        }
        if (count($student)<=0) {
            return false;
        }
        $existSchoolPeriodById=SchoolPeriod::existSchoolPeriodById($request['school_period_id'],$organizationId);
        if (is_numeric($existSchoolPeriodById)&&$existSchoolPeriodById==0){
            return 0;
        }
        if (!$existSchoolPeriodById){
            return false;
        }
        $availableSubjects=self::getAvailableSubjects($request['student_id'],$request['school_period_id'],$request,$organizationId,true);
        if (is_numeric($availableSubjects)&&$availableSubjects==0){
            return 0;
        }
        if (count($availableSubjects)<=0){
            return false;
        }
        $availableSubjectsId=array_column($availableSubjects['available_subjects'],'id');
        foreach ($request['subjects'] as $subject){
            if (!in_array($subject['school_period_subject_teacher_id'],$availableSubjectsId)){
                return false;
            }
        }
        return true;
    }

    public static function prepareArrayOfSubject($subject,$schoolPeriodStudentId,$isWithdrawn)
    {
        if (isset($subject['qualification'])){
            if ($subject['qualification']>=10){
                $studentSubject=[
                    'school_period_student_id'=>$schoolPeriodStudentId,
                    'school_period_subject_teacher_id'=>$subject['school_period_subject_teacher_id'],
                    'qualification'=>$subject['qualification'],
                    'status'=>'APR'
                ];
            }else{
                $studentSubject=[
                    'school_period_student_id'=>$schoolPeriodStudentId,
                    'school_period_subject_teacher_id'=>$subject['school_period_subject_teacher_id'],
                    'qualification'=>$subject['qualification'],
                    'status'=>'REP'
                ];
            }
        }else{
            if ($isWithdrawn){
                $studentSubject=[
                    'school_period_student_id'=>$schoolPeriodStudentId,
                    'school_period_subject_teacher_id'=>$subject['school_period_subject_teacher_id'],
                    'status'=>'RET'
                ];
            }else{
                if (isset($subject['status'])){
                    $studentSubject=[
                        'school_period_student_id'=>$schoolPeriodStudentId,
                        'school_period_subject_teacher_id'=>$subject['school_period_subject_teacher_id'],
                        'status'=>$subject['status']
                    ];
                }else{
                    $studentSubject=[
                        'school_period_student_id'=>$schoolPeriodStudentId,
                        'school_period_subject_teacher_id'=>$subject['school_period_subject_teacher_id'],
                        'status'=>'CUR'
                    ];
                }
            }
        }
        return $studentSubject;
    }

    public static function addSubjects($subjects,$schoolPeriodStudentId,$isWithdrawn)
    {
        foreach ($subjects as $subject){
            $studentSubject = self::prepareArrayOfSubject($subject,$schoolPeriodStudentId,$isWithdrawn);
            $result = StudentSubject::addStudentSubject($studentSubject);
            if (is_numeric($result)&&$result==0){
                return 0;
            }
            $result =SchoolPeriodSubjectTeacher::updateEnrolledStudent($subject['school_period_subject_teacher_id']);
            if (is_numeric($result)&&$result==0){
                return 0;
            }
        }
    }

    public static function getTotalQualification($studentId)
    {
        $totalQualification = 0;
        $approvedSubjects = StudentSubject::getAllSubjectsEnrolledWithoutRETCUR($studentId);
        if(is_numeric($approvedSubjects)&&$approvedSubjects==0){
            return 'e'; //se coloca e porque en un caso este valor aldevolverlo puede ser 0
        }
        if (count($approvedSubjects)>0){
            foreach ($approvedSubjects as $approvedSubject){
                $totalQualification += $approvedSubject['qualification'];
            }
        }
        return $totalQualification;
    }

    public static function addAdvisors($advisors,$finalWorkId)
    {
        foreach($advisors as $advisor){
            $result = Advisor::addAdvisor(
                [
                    'final_work_id'=>$finalWorkId,
                    'teacher_id'=>$advisor['teacher_id']
                ]
            );
            if (is_numeric($result)&&$result==0){
                return 0;
            }
        }
    }

    public static function createProject($subjectId, $finalWork, $student, $schoolPeriodStudentId)
    {
        $projectId=FinalWork::addFinalWork([
            'title'=>$finalWork['title'],
            'student_id'=>$student['id'],
            'subject_id'=>$subjectId,
            'is_project?'=>true,
        ]);
        if (is_numeric($projectId)&&$projectId==0){
            return 0;
        }
        $result = FinalWorkSchoolPeriod::addFinalWorkSchoolPeriod([
            'status'=>$finalWork['status'],
            'description_status'=>$finalWork['description'],
            'final_work_id'=>$projectId,
            'school_period_student_id'=>$schoolPeriodStudentId
        ]);
        if (is_numeric($result)&&$result==0){
            return 0;
        }
        if (isset($finalWork['advisors'])){
            $result=self::addAdvisors($finalWork['advisors'],$projectId);
            if (is_numeric($result)&&$result==0){
                return 0;
            }
        }
    }

    public static function editProject($project, $finalWork, $schoolPeriodStudentId)
    {
        $result=FinalWork::updateFinalWork($project['id'],[
            'title'=>$finalWork['title'],
            'student_id'=>$project['student_id'],
            'subject_id'=>$project['subject_id'],
            'is_project?'=>true,
        ]);
        if (is_numeric($result)&&$result==0){
            return 0;
        }
        $index =0;
        foreach ($project['school_periods'] as $schoolPeriod){
            if ($schoolPeriod['id']==$schoolPeriodStudentId){
                break;
            }
            $index++;
            if ($index>count($project['school_periods'])){
                $index=-1;
                break;
            }
        }
        if ($index==-1){//nuevo status por periodo escolar
            $result = FinalWorkSchoolPeriod::addFinalWorkSchoolPeriod([
                'status'=>'progress',
                'final_work_id'=>$project['id'],
                'school_period_student_id'=>$schoolPeriodStudentId
            ]);
        }else{//editar el status que esta en el semestre
            $result =FinalWorkSchoolPeriod::updateFinalWorkSchoolPeriod($project['school_periods']['index']['id'],
            [
                'status'=>$finalWork['status'],
                'description_status'=>$finalWork['description_status'],
                'final_work_id'=>$project['id'],
                'school_period_student_id'=>$schoolPeriodStudentId
            ]);
        }
        if (is_numeric($result)&&$result==0){
            return 0;
        }
        $result = Advisor::deleteAllAdvisor($project['id']);
        if (is_numeric($result)&&$result==0){
            return 0;
        }
        if (isset($finalWork['advisors'])){
            $result=self::addAdvisors($finalWork['advisors'],$project['id']);
            if (is_numeric($result)&&$result==0){
                return 0;
            }
        }
    }

    public static function createTesis($subjectId, $finalWork, $student, $schoolPeriodStudentId, $project)
    {
        $tesisId=FinalWork::addFinalWork([
            'title'=>$finalWork['title'],
            'student_id'=>$student['id'],
            'subject_id'=>$subjectId,
            'is_project?'=>false,
            'project_id'=>$project['id']
        ]);
        if (is_numeric($tesisId)&&$tesisId==0){
            return 0;
        }
        $result = FinalWorkSchoolPeriod::addFinalWorkSchoolPeriod([
            'status'=>$finalWork['status'],
            'description_status'=>$finalWork['description'],
            'final_work_id'=>$tesisId,
            'school_period_student_id'=>$schoolPeriodStudentId
        ]);
        if (is_numeric($result)&&$result==0){
            return 0;
        }
        if (isset($finalWork['advisors'])){
            $result=self::addAdvisors($finalWork['advisors'],$tesisId);
            if (is_numeric($result)&&$result==0){
                return 0;
            }
        }
    }

    public static function editTesis($tesis, $finalWork, $schoolPeriodStudentId, $project)
    {
        $result=FinalWork::updateFinalWork($tesis['id'],[
            'title'=>$finalWork['title'],
            'student_id'=>$tesis['student_id'],
            'subject_id'=>$tesis['subject_id'],
            'is_project?'=>false,
            'project_id'=>$project['id']
        ]);
        if (is_numeric($result)&&$result==0){
            return 0;
        }
        $index =0;
        foreach ($tesis['school_periods'] as $schoolPeriod){
            if ($schoolPeriod['id']==$schoolPeriodStudentId){
                break;
            }
            $index++;
            if ($index>count($project['school_periods'])){
                $index=-1;
                break;
            }
        }
        if ($index==-1){//nuevo status por periodo escolar
            $result = FinalWorkSchoolPeriod::addFinalWorkSchoolPeriod([
                'status'=>'progress',
                'final_work_id'=>$tesis['id'],
                'school_period_student_id'=>$schoolPeriodStudentId
            ]);
        }else{//editar el status que esta en el semestre
            $result =FinalWorkSchoolPeriod::updateFinalWorkSchoolPeriod($tesis['school_periods']['index']['id'],
                [
                    'status'=>$finalWork['status'],
                    'description_status'=>$finalWork['description_status'],
                    'final_work_id'=>$tesis['id'],
                    'school_period_student_id'=>$schoolPeriodStudentId
                ]);
        }
        if (is_numeric($result)&&$result==0){
            return 0;
        }
        $result = Advisor::deleteAllAdvisor($tesis['id']);
        if (is_numeric($result)&&$result==0){
            return 0;
        }
        if (isset($finalWork['advisors'])){
            $result=self::addAdvisors($finalWork['advisors'],$tesis['id']);
            if (is_numeric($result)&&$result==0){
                return 0;
            }
        }
    }

    public static function existStatusREP($schoolPeriods)
    {
        foreach($schoolPeriods as $schoolPeriod){
            if ($schoolPeriod[status]=='REP'){
                return true;
            }
        }
        return false;
    }

    public static function setProjectOrFinalWork($student, $finalWork, $schoolPeriodStudentId, $organizationId)
    {
        $approvedProject = FinalWork::existApprovedFinalWork($student['id'], true);
        if (is_numeric($approvedProject)&&$approvedProject==0){
            return 0;
        }
        if ($approvedProject==false){//project
            $cantProjects = FinalWork::getFinalWorksByStudent($student['id'],true);
            if (is_numeric($cantProjects)&&$cantProjects==0){
                return 0;
            }
            $subjectId=Subject::getProjectIdBySchoolProgram($student['school_program_id'],$organizationId);
            if (is_numeric($subjectId)&&$subjectId==0){
                return 0;
            }
            if (count($cantProjects)==0){//crear primer intento
                $result = self::createProject($subjectId,$finalWork,$student,$schoolPeriodStudentId);
                if (is_numeric($result)&&$result==0){
                    return 0;
                }
            }
            if (count($cantProjects)==1){//crear segundo intento o actualizar primer intento
                if (self::existStatusREP($cantProjects['school_periods'])){//crear segundo intento
                    $result = self::createProject($subjectId,$finalWork,$student,$schoolPeriodStudentId);

                }else{//editar el que existe
                    $result = self::editProject($cantProjects[0],$finalWork,$schoolPeriodStudentId);
                }
                if (is_numeric($result)&&$result==0){
                    return 0;
                }
            }
            if (count($cantProjects)==2){
                $project = FinalWork::getFinalWorkByStudentAndStatus($student['id'], true, 'PRS');
                if (is_numeric($project)&&$project==0){
                    return 0;
                }
                $result = self::editProject($project[0],$finalWork,$schoolPeriodStudentId);
                if (is_numeric($result)&&$result==0){
                    return 0;
                }
            }
        }else{
            $approvedTesis = FinalWork::existApprovedFinalWork($student['id'], false);
            if (!$approvedTesis){
                $approvedProject = FinalWork::getFinalWorkByStudentAndStatus($student['id'],true,'APR');
                if (is_numeric($approvedProject)&&$approvedProject==0){
                    return 0;
                }
                $cantTesis = FinalWork::getFinalWorksByStudent($student['id'],false);
                if (is_numeric($cantTesis)&&$cantTesis==0){
                    return 0;
                }
                $subjectId=Subject::getFinalWorkIdBySchoolProgram($student['school_program_id'],$organizationId);
                if (is_numeric($subjectId)&&$subjectId==0){
                    return 0;
                }
                if (count($cantTesis)==0){//crear primer intento
                    $result = self::createTesis($subjectId,$finalWork,$student,$schoolPeriodStudentId,$approvedProject[0]);
                    if (is_numeric($result)&&$result==0){
                        return 0;
                    }
                }
                if (count($cantTesis)==1){//crear segundo intento o actualizar primer intento
                    if (self::existStatusREP($cantTesis['school_periods'])){//crear segundo intento
                        $result = self::createTesis($subjectId,$finalWork,$student,$schoolPeriodStudentId,$approvedProject[0]);
                    }else{//editar el que existe
                        $result = self::editTesis($cantTesis[0],$finalWork,$schoolPeriodStudentId,$approvedProject[0]);
                    }
                    if (is_numeric($result)&&$result==0){
                        return 0;
                    }
                }
                if (count($cantTesis)==2){
                    $tesis = FinalWork::getFinalWorkByStudentAndStatus($student['id'], false, 'PRS');
                    if (is_numeric($tesis)&&$tesis==0){
                        return 0;
                    }
                    $result = self::editTesis($tesis[0],$finalWork,$schoolPeriodStudentId,$approvedProject[0]);
                    if (is_numeric($result)&&$result==0){
                        return 0;
                    }
                }
            }
        }
    }

    public static function addInscription(Request $request,$organizationId)
    {
        self::validate($request);
        $existSchoolPeriod=SchoolPeriodStudent::existSchoolPeriodStudent($request['student_id'],$request['school_period_id']);
        if (is_numeric($existSchoolPeriod)&&$existSchoolPeriod==0){
            return response()->json(['message' => self::taskError], 206);
        }
        if (!$existSchoolPeriod) {
            $validateRelation=self::validateRelation($organizationId,$request);
            if (is_numeric($validateRelation)&&$validateRelation==0){
                return response()->json(['message' => self::taskError], 206);
            }
            if($validateRelation){
                $student=Student::getStudentById($request['student_id'],$organizationId);
                if (is_numeric($student)&&$student==0){
                    return response()->json(['message' => self::taskError], 206);
                }
                $request['status']=$student[0]['current_status'];
                $schoolPeriodStudentId=SchoolPeriodStudent::addSchoolPeriodStudent($request);
                if (is_numeric($schoolPeriodStudentId)&&$schoolPeriodStudentId==0){
                    return response()->json(['message' => self::taskError], 206);
                }
                if ($request['status']!='RET-A'&&$request['status']!='RET-B'&&$request['status']!='DES-A'&&$request['status']!='DES-B'){
                    $result = self::addSubjects($request['subjects'],$schoolPeriodStudentId,false);
                    if (is_numeric($result)&&$result==0){
                        return response()->json(['message' => self::taskPartialError], 206);
                    }
                    if (isset($request['final_work'])){
                        $result = self::setProjectOrFinalWork($student[0],$request['final_work'],$schoolPeriodStudentId,$organizationId);
                        if (is_numeric($result)&&$result==0){
                            return response()->json(['message' => self::taskPartialError], 206);
                        }
                    }
                }else{
                    return response()->json(['message' => self::notAllowedRegister], 206);
                }
                return self::getInscriptionById($request,$schoolPeriodStudentId,$organizationId);
            }
            return response()->json(['message'=>self::invalidRelation],206);
        }
        return response()->json(['message'=>self::inscriptionReady],206);
    }

    public static function deleteInscription(Request $request,$id,$organizationId)
    {
        $existSchoolPeriodStudentById=SchoolPeriodStudent::existSchoolPeriodStudentById($id,$organizationId);
        if (is_numeric($existSchoolPeriodStudentById) && $existSchoolPeriodStudentById==0){
            return response()->json(['message' => self::taskError], 206);
        }
        if($existSchoolPeriodStudentById){
            $result=SchoolPeriodStudent::deleteSchoolPeriodStudent($id);
            if (is_numeric($result)&&$result==0){
                return response()->json(['message' => self::taskError], 206);
            }
            return response()->json(['message'=>self::OK]);
        }
        return response()->json(['message'=>self::notFoundInscription],206);
    }

    public static function validateRelationUpdate($organizationId,Request $request)
    {
        $existStudentById = Student::existStudentById($request['student_id'],$organizationId);
        if (is_numeric($existStudentById)&&$existStudentById ==0){
            return 0;
        }
        if (!$existStudentById){
            return false;
        }
        $existSchoolPeriodById=SchoolPeriod::existSchoolPeriodById($request['school_period_id'],$organizationId);
        if (is_numeric($existSchoolPeriodById)&&$existSchoolPeriodById==0){
            return 0;
        }
        if (!$existSchoolPeriodById){
            return false;
        }
        $availableSubjects=self::getAvailableSubjects($request['student_id'],$request['school_period_id'],$request,$organizationId,true);
        if (is_numeric($availableSubjects)&&$availableSubjects==0){
            return 0;
        }
        $subjectsEnrolledInSchoolPeriod = SchoolPeriodStudent::findSchoolPeriodStudent($request['student_id'],$request['school_period_id'])[0]['enrolledSubjects'];
        if (is_numeric($subjectsEnrolledInSchoolPeriod)&&$subjectsEnrolledInSchoolPeriod==0){
            return 0;
        }
        if (count($availableSubjects)<=0 && count($subjectsEnrolledInSchoolPeriod)<=0){
            return false;
        }
        $availableSubjectsId=array_column($availableSubjects,'id');
        foreach ($subjectsEnrolledInSchoolPeriod as $subjectEnrolledInSchoolPeriod){
            if (!in_array($subjectEnrolledInSchoolPeriod['school_period_subject_teacher_id'],$availableSubjectsId)){
                $availableSubjectsId[]=$subjectEnrolledInSchoolPeriod['school_period_subject_teacher_id'];
            }
        }
        foreach ($request['subjects'] as $subject){
            if (!in_array($subject['school_period_subject_teacher_id'],$availableSubjectsId)){
                return false;
            }
        }
        return true;
    }

    public static function updateStatus($schoolPeriodStudentId,$organizationId) //Actualiza el status del estudiante sobre el periodo escolar
    {
        $schoolPeriodStudent = SchoolPeriodStudent::getSchoolPeriodStudentById($schoolPeriodStudentId,$organizationId);
        if (is_numeric($schoolPeriodStudent)&&$schoolPeriodStudent==0){
            return 0;
        }
        $schoolPeriodStudent = $schoolPeriodStudent[0];
        $enrolledSubjects = $schoolPeriodStudent['enrolledSubjects'];
        $allWithdrawn=true;
        foreach ($enrolledSubjects as $enrolledSubject){
            if ($enrolledSubject['status']!='RET'){
                $allWithdrawn = false;
                break;
            }
        }
        if ($allWithdrawn){
            $schoolPeriodStudent['status']='DES-A'; //Si un estudiante retira todas las materias debe caer en DES-A
            $result =SchoolPeriodStudent::updateSchoolPeriodStudentLikeArray($schoolPeriodStudentId,
                ['student_id'=>$schoolPeriodStudent['student_id'],
                    'school_period_id'=>$schoolPeriodStudent['school_period_id'],
                    'pay_ref'=>$schoolPeriodStudent['pay_ref'],
                    'status'=>$schoolPeriodStudent['status'],
                    'financing'=>$schoolPeriodStudent['financing'],
                    'financing_description'=>$schoolPeriodStudent['financing_description'],
                    'amount_paid'=>$schoolPeriodStudent['amount_paid'],
                ]);
            if (is_numeric($result)&&$result==0){
                return 0;
            }
            $student = Student::getStudentById($schoolPeriodStudent['student_id'],$organizationId);
            if (is_numeric($student)&&$student==0){
                return 0;
            }
            $student[0]['current_status']='DES-A';
            $result = Student::updateStudent($schoolPeriodStudent['student_id'],$student[0]->toArray());
            if (is_numeric($result)&&$result==0){
                return 0;
            }
        }
    }

    public static function updateSubjects($subjects,$schoolPeriodStudentId,$organizationId,$isWithdrawn)
    {
        $subjectsInBd=StudentSubject::studentSubjectBySchoolPeriodStudent($schoolPeriodStudentId);
        if (is_numeric($subjectsInBd)&&$subjectsInBd==0){
            return 0;
        }
        $subjectsUpdated=[];
        foreach ($subjects as $subject){
            $existSubject = false;
            foreach ($subjectsInBd as $subjectInBd){
                if ($subject['school_period_subject_teacher_id']==$subjectInBd['school_period_subject_teacher_id']){
                    $studentSubject = self::prepareArrayOfSubject($subject,$schoolPeriodStudentId,$isWithdrawn);
                    $result = StudentSubject::updateStudentSubject($subjectInBd['id'],$studentSubject);
                    if (is_numeric($result)&&$result){
                        return 0;
                    }
                    $subjectsUpdated[]=$subjectInBd['id'];
                    $existSubject=true;
                    break;
                }
            }
            if ($existSubject==false){
                $result=self::addSubjects([$subject],$schoolPeriodStudentId,$isWithdrawn);
                if (is_numeric($result)&&$result==0){
                    return 0;
                }
                $schoolPeriodStudentIdAdd=StudentSubject::findStudentSubjectId($schoolPeriodStudentId,$subject['school_period_subject_teacher_id'])[0]['id'];
                if (is_numeric($schoolPeriodStudentIdAdd)&&$schoolPeriodStudentIdAdd){
                    return 0;
                }
                $subjectsUpdated[]=$schoolPeriodStudentIdAdd;
            }
        }
        foreach ($subjectsInBd as $subjectId){
            if (!in_array($subjectId['id'],$subjectsUpdated)){
                $deleteStudentSubject=StudentSubject::deleteStudentSubject($subjectId['id']);
                if (is_numeric($deleteStudentSubject)&&$deleteStudentSubject==0){
                    return 0;
                }
            }
        }
        $result = self::updateStatus($schoolPeriodStudentId,$organizationId);
        if (is_numeric($result)&&$result==0){
            return 0;
        }
    }

    public static function updateInscription(Request $request, $id,$organizationId)
    {
        self::validate($request);
        $existSchoolPeriod=SchoolPeriodStudent::existSchoolPeriodStudentById($id,$organizationId);
        if (is_numeric($existSchoolPeriod)&&$existSchoolPeriod==0){
            return response()->json(['message' => self::taskError], 206);
        }
        if ($existSchoolPeriod) {
            $schoolPeriodStudentIdInBd= SchoolPeriodStudent::findSchoolPeriodStudent($request['student_id'],$request['school_period_id']);
            if (is_numeric($schoolPeriodStudentIdInBd)&&$schoolPeriodStudentIdInBd==0){
                return response()->json(['message'=>self::taskError],206);
            }
            if(count($schoolPeriodStudentIdInBd)>0){
                if ($schoolPeriodStudentIdInBd[0]['id']!=$id){
                    return response()->json(['message'=>self::busySchoolPeriodStudent],206);
                }
            }
            $validateRelationUpdate=self::validateRelationUpdate($organizationId,$request);
            if (is_numeric($validateRelationUpdate)&&$validateRelationUpdate==0){
                return 0;
            }
            if($validateRelationUpdate){
                $student=Student::getStudentById($request['student_id'],$organizationId);
                if (is_numeric($student)&&$student==0){
                    return response()->json(['message' => self::taskError], 206);
                }
                $request['status']=$student[0]['current_status'];
                $result = SchoolPeriodStudent::updateSchoolPeriodStudent($id,$request);
                if (is_numeric($result)&&$result==0){
                    return response()->json(['message' => self::taskError], 206);
                }
                if($request['status']!='RET-A'||$request['status']!='RET-B'&&$request['status']!='DES-A'&&
                    $request['status']!='DES-B'){
                    $result =self::updateSubjects($request['subjects'],$id,$organizationId,false);
                    if (is_numeric($result)&&$result==0){
                        return response()->json(['message' => self::taskPartialError], 206);
                    }
                }else{
                    return response()->json(['message' => self::notAllowedRegister], 206);
                }
                return self::getInscriptionById($request,$id,$organizationId);
            }
            return response()->json(['message'=>self::invalidRelation],206);
        }
        return response()->json(['message'=>self::notFoundInscription],206);
    }

    public static function studentAvailableSubjects($studentId,Request $request,$organizationId)
    {
        $isValid=StudentService::validateStudent($request,$organizationId,$studentId);
        if ($isValid=='valid'){
            $currentSchoolPeriod= SchoolPeriod::getCurrentSchoolPeriod($organizationId);
            if(is_numeric($currentSchoolPeriod)&&$currentSchoolPeriod==0){
                return response()->json(['message'=>self::taskError],206);
            }
            if (count($currentSchoolPeriod)>0){
                if ($currentSchoolPeriod[0]['inscription_visible']==true){
                    return self::getAvailableSubjects($studentId,$currentSchoolPeriod[0]['id'],$request,$organizationId,false);
                }
                return response()->json(['message'=>self::notAvailableInscriptions],206);
            }
            return response()->json(['message'=>self::noCurrentSchoolPeriod],206);
        }
        return $isValid;
    }

    public static function validateGroupSubject($subjects,$schoolProgramId,$organizationId){
        $subjectsInProgram=Subject::getSubjectsBySchoolProgram($schoolProgramId,$organizationId);
        if (is_numeric($subjectsInProgram)&&$subjectsInProgram == 0){
            return 0;
        }
        $subjectsValues= array_values($subjects[0]);
        dd($subjectsValues);
        foreach ($subjects as $subject){

        }
    }

    public static function studentAddInscription(Request $request,$organizationId)
    {
        $isValid=StudentService::validateStudent($request,$organizationId,$request['student_id']);
        if ($isValid=='valid'){
            $currentSchoolPeriod= SchoolPeriod::getCurrentSchoolPeriod($organizationId);
            if (is_numeric($currentSchoolPeriod)&&$currentSchoolPeriod==0){
                 return response()->json(['message'=>self::taskError],206);
            }
            if (count($currentSchoolPeriod)>0){
                if ($currentSchoolPeriod[0]['inscription_visible']==true){
                    $request['school_period_id']=$currentSchoolPeriod[0]['id'];
                    return self::addInscription($request,$organizationId);
                }
                return response()->json(['message'=>self::notAvailableInscriptions],206);
            }
            return response()->json(['message'=>self::noCurrentSchoolPeriod],206);
        }
        return $isValid;
    }

    public static function getCurrentEnrolledSubjects($studentId,$organizationId,Request $request){
        $isValid=StudentService::validateStudent($request,$organizationId,$studentId);
        if ($isValid=='valid'){
            $currentSchoolPeriod= SchoolPeriod::getCurrentSchoolPeriod($organizationId);
            if (is_numeric($currentSchoolPeriod)&&$currentSchoolPeriod==0){
                return response()->json(['message'=>self::taskError],206);
            }
            if (count($currentSchoolPeriod)>0){
                $inscription = SchoolPeriodStudent::findSchoolPeriodStudent($studentId,$currentSchoolPeriod[0]['id']);
                if (is_numeric($inscription)&&$inscription==0){
                    return response()->json(['message'=>self::taskError],206);
                }
                if (count($inscription)>0){
                    return $inscription[0];
                }
                return response()->json(['message'=>self::emptyInscriptions],206);
            }
            return response()->json(['message'=>self::noCurrentSchoolPeriod],206);
        }
        return $isValid;
    }

    public static function validateWithdrawSubjects($withdrawSubjects,$enrolledSubjects)
    {
        foreach ($withdrawSubjects as $withdrawSubject){
            $found = false;
            foreach ($enrolledSubjects as $enrolledSubject){
                if ($withdrawSubject['student_subject_id']==$enrolledSubject['id']){
                    $found=true;
                    break;
                }
            }
            if ($found ==false){
                return false;
            }
        }
        return true;
    }

    public static function changeStatusSubjectsToRET($schoolPeriodStudentId,$organizationId,$withdrawSubjects)
    {
        foreach ($withdrawSubjects as $withdrawSubject){
            $studentSubject = StudentSubject::getStudentSubjectById($withdrawSubject['student_subject_id']);
            if (is_numeric($studentSubject)&&$studentSubject==0){
                return 0;
            }
            $studentSubject = $studentSubject[0];
            $studentSubject=StudentSubject::updateStudentSubject($withdrawSubject['student_subject_id'],[
                "school_period_student_id"=>$studentSubject['school_period_student_id'],
                "school_period_subject_teacher_id"=>$studentSubject['school_period_subject_teacher_id'],
                "qualification"=>$studentSubject['qualification'],
                "status"=>'RET'
            ]);
            if (is_numeric($studentSubject)&&$studentSubject==0){
                return 0;
            }
            $result=self::updateStatus($schoolPeriodStudentId,$organizationId);
            if (is_numeric($result)&&$result==0){
                return 0;
            }
        }
    }

    public static function withdrawSubjects(Request $request,$organizationId)
    {
        $isValid=StudentService::validateStudent($request,$organizationId,$request['student_id']);
        if ($isValid=='valid'){
            $currentSchoolPeriod= SchoolPeriod::getCurrentSchoolPeriod($organizationId);
            if (is_numeric($currentSchoolPeriod)&&$currentSchoolPeriod==0){
                return response()->json(['message'=>self::taskError],206);
            }
            if (count($currentSchoolPeriod)>0){
                if (strtotime($currentSchoolPeriod[0]['withdrawal_deadline'])>=strtotime(now()->toDateTimeString())){
                    $inscription = SchoolPeriodStudent::findSchoolPeriodStudent($request['student_id'],$currentSchoolPeriod[0]['id']);
                    if (is_numeric($inscription)&&$inscription==0){
                        return response()->json(['message'=>self::taskError],206);
                    }
                    if (count($inscription)>0){
                        if (self::validateWithdrawSubjects($request['withdrawSubjects'],$inscription[0]['enrolledSubjects'])){
                            $result =self::changeStatusSubjectsToRET($inscription[0]['id'],$organizationId,$request['withdrawSubjects']);
                            if (is_numeric($result)&&$result==0){
                                return 0;
                            }
                            return response()->json(['message'=>self::OK],200);
                        }
                        return response()->json(['message'=>self::invalidSubject],206);
                    }
                    return response()->json(['message'=>self::notCurrentInscription],206);
                }
                return response()->json(['message'=>self::expiredDate],206);
            }
            return response()->json(['message'=>self::noCurrentSchoolPeriod],206);
        }
        return $isValid;
    }

    public static function getEnrolledStudentsInSchoolPeriod($teacherId,$schoolPeriodSubjectTeacherId,$organizationId,Request $request)
    {
        $isValid=TeacherService::validateTeacher($request,$teacherId,$organizationId);
        if ($isValid=='valid'){
            $currentSchoolPeriod= SchoolPeriod::getCurrentSchoolPeriod($organizationId);
            if (is_numeric($currentSchoolPeriod)&&$currentSchoolPeriod==0){
                return response()->json(['message' => self::taskError], 206);
            }
            if (count($currentSchoolPeriod)>0){
                $existSchoolPeriodSubjectTeacherById=SchoolPeriodSubjectTeacher::existSchoolPeriodSubjectTeacherById($schoolPeriodSubjectTeacherId);
                if (is_numeric($existSchoolPeriodSubjectTeacherById)&&$existSchoolPeriodSubjectTeacherById==0){
                    return response()->json(['message' => self::taskError], 206);
                }
                if ($existSchoolPeriodSubjectTeacherById){
                    $schoolPeriodSubjectTeacher= SchoolPeriodSubjectTeacher::getSchoolPeriodSubjectTeacherById($schoolPeriodSubjectTeacherId);
                    if (is_numeric($schoolPeriodSubjectTeacher)&&$schoolPeriodSubjectTeacher==0){
                        return response()->json(['message' => self::taskError], 206);
                    }
                    if ($schoolPeriodSubjectTeacher[0]['teacher_id']==$teacherId && $schoolPeriodSubjectTeacher[0]['school_period_id']==$currentSchoolPeriod[0]['id'] ){
                        $enrolledStudents=StudentSubject::studentSubjectBySchoolPeriodSubjectTeacherId($schoolPeriodSubjectTeacherId);
                        if (is_numeric($enrolledStudents)&&$enrolledStudents==0){
                            return response()->json(['message' => self::taskError], 206);
                        }
                        if (count($enrolledStudents)>0){
                            return $enrolledStudents;
                        }
                        return response()->json(['message'=>self::emptyInscriptions],206);
                    }
                }
                return response()->json(['message'=>self::invalidSubject],206);
            }
            return response()->json(['message'=>self::noCurrentSchoolPeriod],206);
        }
        return $isValid;
    }

    public static function validateLoadNotesRequest(Request $request)
    {
        $request->validate([
            'teacher_id'=>'required|numeric',
            'school_period_subject_teacher_id'=>'required|numeric',
            'student_notes.*.student_subject_id'=>'required|numeric',
            'student_notes.*.qualification'=>'required|numeric'
        ]);
    }

    public static function validateLoadNotes(Request $request,$schoolPeriodId)
    {
        $teacherId=$request['teacher_id'];
        $schoolPeriodSubjectTeacherId=$request['school_period_subject_teacher_id'];
        $studentNotes=$request['student_notes'];
        $existSchoolPeriodSubjectTeacher =SchoolPeriodSubjectTeacher::existSchoolPeriodSubjectTeacherById($schoolPeriodSubjectTeacherId);
        if(is_numeric($existSchoolPeriodSubjectTeacher)&&$existSchoolPeriodSubjectTeacher==0){
            return 0;
        }
        if (!$existSchoolPeriodSubjectTeacher){
            return false;
        }
        $schoolPeriodSubjectTeacher = SchoolPeriodSubjectTeacher::getSchoolPeriodSubjectTeacherById($schoolPeriodSubjectTeacherId);
        if(is_numeric($schoolPeriodSubjectTeacher)&&$schoolPeriodSubjectTeacher==0){
            return 0;
        }
        if ($schoolPeriodSubjectTeacher[0]['teacher_id']!=$teacherId ||$schoolPeriodSubjectTeacher[0]['school_period_id']!=$schoolPeriodId){
            return false;
        }
        $enrolledStudents=StudentSubject::studentSubjectBySchoolPeriodSubjectTeacherId($schoolPeriodSubjectTeacherId)->toArray();
        if (count($enrolledStudents)<=0){
            return false;
        }
        foreach ($studentNotes as $studentNote){
            $found = false;
            foreach ($enrolledStudents as $enrolledStudent){
                if($enrolledStudent['id']==$studentNote['student_subject_id'] && $enrolledStudent['status']!='RET'){
                    $found =true;
                }
            }
            if ($found == false){
                return false;
            }
        }
        return true;
    }

    public static function changeNotes($studentNotes){
        $schoolPeriodStudentForUpdate= [];
        foreach($studentNotes as $studentNote){
            $studentSubject= StudentSubject::getStudentSubjectById($studentNote['student_subject_id']);
            if (is_numeric($studentSubject)&&$studentSubject==0){
                return 0;
            }
            $studentSubject[0]['qualification']=$studentNote['qualification'];
            $studentSubjectPrepare = self::prepareArrayOfSubject($studentSubject[0],$studentSubject[0]['school_period_student_id'],false);
            $result=StudentSubject::updateStudentSubject($studentSubject[0]['id'],$studentSubjectPrepare);
            if (is_numeric($result)&&$result==0){
                return 0;
            }
            if (!in_array($studentSubject[0]['school_period_student_id'],$schoolPeriodStudentForUpdate)){
                $schoolPeriodStudentsForUpdate[]=$studentSubject[0]['school_period_student_id'];
            }
        }
        return $schoolPeriodStudentsForUpdate;
    }

    public static function loadNotes(Request $request,$organizationId)
    {
        self::validateLoadNotesRequest($request);
        $isValid=TeacherService::validateTeacher($request,$request['teacher_id'],$organizationId);
        if ($isValid=='valid'){
            $currentSchoolPeriod= SchoolPeriod::getCurrentSchoolPeriod($organizationId);
            if(is_numeric($currentSchoolPeriod)&&$currentSchoolPeriod==0){
                return response()->json(['message' => self::taskError], 206);
            }
            if (count($currentSchoolPeriod)>0){
                $validNotes = self::validateLoadNotes($request,$currentSchoolPeriod[0]['id']);
                if (is_numeric($validNotes)&&$validNotes==0){
                    return response()->json(['message' => self::taskError], 206);
                }
                if ($validNotes && $currentSchoolPeriod[0]['load_notes']==true){
                    $schoolPeriodsStudentForUpdate=self::changeNotes($request['student_notes']);
                    if (is_numeric($schoolPeriodsStudentForUpdate)&&$schoolPeriodsStudentForUpdate==0){
                        return response()->json(['message' => self::taskError], 206);
                    }
                    foreach ($schoolPeriodsStudentForUpdate as $schoolPeriodStudentForUpdate){
                        $result = self::updateStatus($schoolPeriodStudentForUpdate,$organizationId);
                        if (is_numeric($result)&&$result==0){
                            return response()->json(['message' => self::taskPartialError], 206);
                        }
                    }
                    return response()->json(['message'=>self::OK],200);
                }
                return response()->json(['message'=>self::invalidData],206);
            }
            return response()->json(['message'=>self::noCurrentSchoolPeriod],206);
        }
        return $isValid;
    }

}
