<?php
/**
 * Created by PhpStorm.
 * User: halayon
 * Date: 30/08/19
 * Time: 09:08 AM
 */

namespace App\Services;

use App\Advisor;
use App\DoctoralExam;
use App\FinalWork;
use App\FinalWorkSchoolPeriod;
use App\Log;
use App\SchoolProgram;
use App\Subject;
use Illuminate\Http\Request;
use App\SchoolPeriodStudent;
use App\Student;
use App\SchoolPeriod;
use App\SchoolPeriodSubjectTeacher;
use App\StudentSubject;
use Illuminate\Http\Response;
use PhpParser\Comment\Doc;

/**
 * @package : Services
 * @author : Hector Alayon
 * @version : 1.0
 */
class InscriptionService
{
    const taskError = 'No se puede proceder con la tarea';
    const taskPartialError = 'No se pudo proceder con la tarea en su totalidad';
    const emptyInscriptions ='No hay inscripciones';
    const notFoundInscription = 'Inscripcion no encontrada';
    const emptyInscriptionInCurrentSchoolPeriod = 'Periodo escolar no posee inscripciones';
    const OK = 'OK';
    const notFoundStudentGivenId = 'No existe el estudiante dado el id';
    const thereAreNotSubjectsAvailableToRegister = 'No hay asignaturas disponibles para inscribir';
    const thereAreSchoolPeriodWithoutPaying = 'Hay periodo escolar sin pagar';
    const warningAverage = 'tu promedio es inferior a 14, te sugerimos que vuelvas a ver una asignatura reprobada o una 
    asignatura ya aprobada si no lo has hecho antes, te encuentras en periodo de prueba en este semestre';
    const notAllowedRegister = 'No tienes permitido inscribir';
    const endProgram = 'Ya culmino el programa';
    const noCurrentSchoolPeriod='No hay periodo escolar en curso';
    const invalidSubject = 'Asignatura invalida';
    const notAvailableInscriptions = 'No estan disponibles las inscripciones';
    const inscriptionReady = 'Inscripcion ya realizada';
    const invalidSubjectToInscription = 'Las asignaturas a inscribir no estan disponibles';
    const invalidData = 'Datos invalidos o no esta disponible la carga de notas';
    const busySchoolPeriodStudent ='El estudiante ya esta inscrito en el periodo escolar y se encuentra en 
    otro registro';
    const expiredDate = 'No se puede realizar retiros la fecha ya ha pasado';
    const notCurrentInscription ='No hay inscripcion actual para usted';
    const notRegister = 'Debes mandar un body valido para inscribir';
    const notFoundFinalWork = 'Proyecto o trabajo de grado no encontrada';
    const logAddInscription = 'Inscripcion al estudiante con id ';
    const logUpdateInscription = 'Actualiza inscripcion al estudiante con id ';
    const logDeleteInscription = 'elimina inscripcion al estudiante con id ';
    const logWithdrawSubject = 'Ha retirado asignaturas el estudiante con id ';
    const logLoadNotes = 'Realizo carga de notas';
    const logDeleteFinalWork = 'Elimina trabajo de grado o proyecto al estudiante con id ';

    /**
     * Si los Flag pasados por parámetros son false, se devolverá el json de error común de lo contrario se devolverá de
     * acuerdo a los Flag
     * @param boolean $internalCall: Booleano que define si se debe generar un json o un 0
     * @param boolean $isPartial: Define si se devolverá un mensaje de error parcial
     * @return integer|Response en caso de ser una llamada interna devolvera 0 de lo contrario devolvera un mensaje de
     * error contenido en un objeto.
     */
    public static function taskError($internalCall,$isPartial)
    {
        if ($internalCall){
            return 0;
        }
        if ($isPartial){
            return response()->json(['message' => self::taskPartialError], 500);
        }
        return response()->json(['message'=>self::taskError],500);
    }

    /**
     * Lista todas las inscripciones que se han realizado en la organización con el método
     * SchoolPeriodStudent::getSchoolPeriodStudent($organizationId).
     * @param string $organizationId Id de la organización
     * @return array|Response Obtiene todas las inscripciones presentes en la organizacion.
     */
    public static function getInscriptions($organizationId)
    {
        $inscriptions = SchoolPeriodStudent::getSchoolPeriodStudent($organizationId);
        if (is_numeric($inscriptions)&&$inscriptions==0){
            return self::taskError(false,false);
        }
        if (count($inscriptions)>0){
            return ($inscriptions);
        }
        return response()->json(['message'=>self::emptyInscriptions],206);
    }

    /**
     * Devuelve una inscripción dado su id y la organización con el método
     * SchoolPeriodStudent::getSchoolPeriodStudentById($id,$organizationId).
     * @param integer $id Id de la inscripción
     * @param string $organizationId Id de la organización
     * @return array|Response Obtiene una inscripcion dado su id en la organizacion.
     */
    public static function getInscriptionById($id,$organizationId)
    {
        $inscription = SchoolPeriodStudent::getSchoolPeriodStudentById($id,$organizationId);
        if (is_numeric($inscription)&&$inscription==0){
            return self::taskError(false,false);
        }
        if (count($inscription)>0){
            return ($inscription)[0];
        }
        return response()->json(['message'=>self::notFoundInscription],206);
    }

    /**
     * Devuelve todas las inscripciones asociadas al periodo escolar dado su id con  el método
     * SchoolPeriodStudent::getSchoolPeriodStudentBySchoolPeriod($schoolPeriodId,$organizationId)
     * @param integer $schoolPeriodId: Id del periodo escolar asociado
     * @param string $organizationId Id de la organización
     * @return SchoolPeriodStudent|Response Obtiene todas las inscripciones que se realizaron en un periodo escolar en
     * la organizacion.
     */
    public static function getInscriptionsBySchoolPeriod($schoolPeriodId,$organizationId)
    {
        $inscriptions = SchoolPeriodStudent::getSchoolPeriodStudentBySchoolPeriod($schoolPeriodId,$organizationId);
        if (is_numeric($inscriptions)&&$inscriptions==0){
            return self::taskError(false,false);
        }
        if (count($inscriptions)>0){
            return ($inscriptions);
        }
        return response()->json(['message'=>self::emptyInscriptionInCurrentSchoolPeriod],206);
    }

    /**
     * Deja las asignaturas disponibles, filtrando de subjectsInSchoolPeriod por limite de inscritos y asignaturas que
     * el estudiante dado su id ha inscrito anteriormente sin haber retirado, usa el metodo
     * StudentSubject::getAllSubjectsEnrolledWithoutRET($studentId) para obtener las asignaturas que curso el estudiante.
     * @param object $student Datos del estudiante asociado al usuario
     * @param array $subjectsInSchoolPeriod Asignaturas en un determinado periodo escolar, arreglo de tipo
     * schoolPeriodSubjectTeacher
     * @return array|integer Obtiene un arreglo con las asignaturas aun no inscritas del estudiante y que estan
     * disponibles en el periodo escolar, de fallar devolvera 0.
     */
    public static function getUnregisteredSubjects($student,$subjectsInSchoolPeriod)
    {
        $allSubjectsEnrolled = StudentSubject::getAllSubjectsEnrolledWithoutRET($student['id']);
        if (is_numeric($allSubjectsEnrolled) && $allSubjectsEnrolled===0){
            return 0;
        }
        $allSubjectsEnrolledId = [];
        foreach ($allSubjectsEnrolled as $subjectEnrolled){
            $allSubjectsEnrolledId[]=$subjectEnrolled['dataSubject']['subject_id'];
        }
        $equivalencesSubjectsId = array_column($student['equivalence']->toArray(),'subject_id');
        $availableSubjects=[];
        foreach ($subjectsInSchoolPeriod as $subjectInSchoolPeriod){
            if ($subjectInSchoolPeriod['enrolled_students']<$subjectInSchoolPeriod['limit']){
                if (count($allSubjectsEnrolledId)>0){
                    if (!in_array($subjectInSchoolPeriod['subject_id'],$allSubjectsEnrolledId) &&
                        !in_array($subjectInSchoolPeriod['subject_id'],$equivalencesSubjectsId)){
                        $availableSubjects[]=$subjectInSchoolPeriod;
                    }
                }else{
                    if (!in_array($subjectInSchoolPeriod['subject_id'],$equivalencesSubjectsId)){
                        $availableSubjects[]=$subjectInSchoolPeriod;
                    }
                }
            }
        }
        return $availableSubjects;
    }

    /**
     * Deja las asignaturas disponibles por programa escolar, filtrando de availableSubjects, usa el método S
     * ubject::getSubjectsBySchoolProgram($schoolProgramId,$organizationId) para obtener las asignaturas asociadas al
     * programa escolar.
     * @param integer $schoolProgramId Id del programa escolar asociado al estudiante
     * @param string $organizationId Id de la organización
     * @param array $availableSubjects asignaturas del periodo escolar filtradas con las que ya curso el estudiante,
     * arreglo de tipo schoolPeriodSubjectTeacher
     * @return array|integer Filtra las asinaturas disponibles del programa academico del cual esta asociado el
     * estudiante.
     */
    public static function filterSubjectsBySchoolProgram($schoolProgramId, $organizationId, $availableSubjects)
    {
        $subjectsInSchoolProgram = Subject::getSubjectsBySchoolProgram($schoolProgramId,$organizationId);
        if (is_numeric($subjectsInSchoolProgram)&&$subjectsInSchoolProgram===0){
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

    /**
     * En dado caso que el estudiante quiera modificar su inscripción, cuando verifique que asignaturas le quedan
     * disponibles, esta función filtra por las que ya tiene inscritas en el semestre de las que tiene disponible, dando
     * como resultado las asignaturas disponibles que aún no ha inscrito, usa el método
     * StudentSubject::getEnrolledSubjectsBySchoolPeriodStudent($studentId,$schoolPeriodId) para obtener las asignaturas
     * inscritas  de un estudiante en un determinado periodo escolar.
     * @param integer $studentId Id del estudiante asociado al usuario
     * @param integer $schoolPeriodId Id del periodo escolar asociado al estudiante
     * @param string $availableSubjects Asignaturas del periodo escolar filtradas con las que ya curso el estudiante,
     * arreglo de tipo schoolPeriodSubjectTeacher
     * @return array|integer Filtra las asinaturas disponibles del programa academico del cual esta asociado el
     * estudiante.
     */
    public static function filterSubjectsEnrolledInSchoolPeriod($studentId,$schoolPeriodId,$availableSubjects)
    {
        $enrolledSubjects = StudentSubject::getEnrolledSubjectsBySchoolPeriodStudent($studentId,$schoolPeriodId);
        if (is_numeric($enrolledSubjects)&&$enrolledSubjects===0){
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

    /**
     * Obtiene la suma de las calificaciones de las asignaturas aprobadas y reprobadas usando el método
     * StudentSubject::getAllSubjectsEnrolledWithoutRETCUR($studentId).
     * @param integer $studentId Id del estudiante asociado al usuario
     * @return string|integer Obtiene la suma de las notas de todas las asignaturas inscritas por el estudiante de
     * fallar devolvera e.
     */
    public static function getTotalQualification($studentId)
    {
        $totalQualification = 0;
        $approvedSubjects = StudentSubject::getAllSubjectsEnrolledWithoutRETCUR($studentId);
        if(is_numeric($approvedSubjects)&&$approvedSubjects==0){
            return 'e'; //se coloca e porque puede existir un caso  en que este valor devolvera 0
        }
        if (count($approvedSubjects)>0){
            foreach ($approvedSubjects as $approvedSubject){
                $totalQualification += $approvedSubject['qualification'];
            }
        }
        return $totalQualification;
    }

    /**
     * Valida que el estudiante pueda inscribir las asignaturas de proyectos asociados al programa escolar, de poder
     * hacerlo retorna true de lo contrario será false.
     * @param Student $student datos del estudiante, objeto de tipo Student
     * @param SchoolProgram $schoolProgram Datos del programa escolar, objeto de tipo schoolProgram
     * @param string $organizationId Id de la organización
     * @param array $dataPercentageStudent Contiene la cantidad de créditos inscritos, sumatoria de notas inscritas y
     * cantidad de asignaturas inscritas
     * @return boolean|integer Si el estudiante tiene o no el proyecto disponible retornara un booleano, de fallar
     * devolvera 0.
     */
    public static function availableProject($student,$schoolProgram, $organizationId, $dataPercentageStudent){
        $cantSchoolPeriods = SchoolPeriodStudent::getCantEnrolledSchoolPeriodByStudent($student['id'],$organizationId);
        if (is_string($cantSchoolPeriods)&&$cantSchoolPeriods==='e'){
            return 0;
        }
        if ($cantSchoolPeriods>=$schoolProgram['min_duration'] &&
            $dataPercentageStudent['enrolled_credits']+$student['credits_granted']>=
            $schoolProgram['min_num_cu_final_work'] ){
            return true;
        }
        return false;
    }

    /**
     * Obtiene las asignaturas que sean proyecto o trabajo final dado su programa escolar asociado y filtra las
     * asignaturas que aún no hayan sido aprobadas en caso de ser más de una, usa el método $finalWorksInSchoolProgram =
     * Subject::getProjectBySchoolProgram($schoolProgram['id'],$organizationId) para obtener el(los) proyecto(s) o
     * Subject::getFinalWorkBySchoolProgram($schoolProgram['id'],$organizationId) para obtener el(los) trabajo(s)
     * final(es) de acuerdo con el caso.
     * @param Student $student  datos del estudiante, objeto de tipo Student
     * @param SchoolProgram $schoolProgram Datos del programa escolar, objeto de tipo schoolProgram
     * @param string $organizationId Id de la organización
     * @param boolean $isProject Flag para determinar si lo que se crea es Proyecto o trabajo final
     * @return array|integer Devuelve las asignaturas de proyecto o trabajo especial de grado, de fallar devolvera 0.
     */
    public static function getAvailableFinalSubjects($student,$schoolProgram,$organizationId,$isProject)
    {
        $availableFinalWorks = [];
        if ($isProject){ //Caso proyecto
            $finalWorksInSchoolProgram = Subject::getProjectBySchoolProgram($schoolProgram['id'],$organizationId);
        }else{// Caso trabajo final
            $finalWorksInSchoolProgram = Subject::getFinalWorkBySchoolProgram($schoolProgram['id'],$organizationId);
        }
        $approvedFinalWorks = FinalWork::getFinalWorkByStudentAndStatus($student['id'],$isProject,'APPROVED');
        if ((is_numeric($finalWorksInSchoolProgram)&&$finalWorksInSchoolProgram===0)||(is_numeric($approvedFinalWorks)&&
            $approvedFinalWorks===0)){
            return 0;
        }
        if (count($finalWorksInSchoolProgram)>0){
            if (count($approvedFinalWorks)>0){
                $approvedFinalWorksId=array_column($approvedFinalWorks->toArray(),'subject_id');
                foreach ($finalWorksInSchoolProgram as $finalWorkInSchoolProgram){
                    if (!in_array($finalWorkInSchoolProgram['id'],$approvedFinalWorksId)){
                        $availableFinalWorks[]=$finalWorkInSchoolProgram;
                    }
                }
                return $availableFinalWorks;
            }
            return $finalWorksInSchoolProgram;
        }
        return $availableFinalWorks;
    }

    /**
     * Filtra las asignaturas que están en equivalencia con respecto a las que están disponibles en el semestre.
     * @param array $equivalence Equivalencias del estudiante
     * @param SchoolPeriodSubjectTeacher $subjectsInSchoolPeriod Asignaturas en un determinado periodo escolar, arreglo
     * de tipo schoolPeriodSubjectTeacher
     * @return array|integer filtra un arreglo con las asignaturasque fueron pasadas por equivalencia apsociadas al
     * estudiante y que estan disponibles en el periodo escolar, de fallar devolvera 0.
     */
    public static function filterEquivalenceToStudent($equivalence,$subjectsInSchoolPeriod)
    {
        $filter = [];
        $subjectInEquivalenceId=array_column($equivalence->toArray(),'subject_id');
        foreach($subjectsInSchoolPeriod as $subject){
            if (!in_array($subject['subject_id'],$subjectInEquivalenceId)){
                $filter[]=$subject;
            }
        }
        return $filter;
    }

    /**
     * Obtiene todas las asignaturas disponibles por inscribir dado un estudiante y un periodo escolar, también habilita
     * si se puede inscribir proyecto, trabajo especial de grado o examen doctoral.
     * @param integer $studentId Id del estudiante asociado al usuario
     * @param integer $schoolPeriodId d del periodo escolar en caso de ser administrador, en caso contrario se
     * colocará el id del periodo escolar actual
     * @param string $organizationId Id de la organización
     * @param boolean $internalCall: Bandera para determinar si se retorna a un servicio (true) o al controlador (false)
     * @return Response|integer|array Devolvera las asignatuiras disponibles para inscribir, de no existir asignaturas
     * disponibles o fallar devolvera un entero o un json con un mensaje asociado.
     */
    public static function getAvailableSubjects($studentId,$schoolPeriodId,$organizationId,$internalCall)
    {
        $student = Student::getStudentById($studentId,$organizationId);
        if (is_numeric($student)&&$student===0){
            return self::taskError($internalCall,false);
        }
        $schoolPeriod = SchoolPeriod::getSchoolPeriodById($schoolPeriodId,$organizationId);
        if (is_numeric($schoolPeriodId)&&$schoolPeriodId===0){
            return self::taskError($internalCall,false);
        }
        if (count($student)>0 && count($schoolPeriod)>0){
            $student=$student[0];
            if ($student['current_status']!='REG' && $student['current_status']!='REI-A'
                && $student['current_status']!='REI-B' && $student['current_status']!='RIN-A'
                && $student['current_status']!='RIN-B' &&  $student['current_status']!='ENDED'){
                if ($internalCall){
                    return 1;
                }
                return response()->json(['message' => self::notAllowedRegister], 206);
            }
            if ($student['end_program']==true){
                if ($internalCall){
                    return 2;
                }
                return response()->json(['message' => self::endProgram], 206);
            }
            $schoolProgram=SchoolProgram::getSchoolProgramById($student['school_program_id'],$organizationId);
            if (is_numeric($schoolProgram) && $schoolProgram===0){
                return self::taskError($internalCall,false);
            }
            if (count($schoolProgram)>0){
                $schoolProgram=$schoolProgram[0];
                $thereIsUnpaidSchoolPeriod=SchoolPeriodStudent::isThereUnpaidSchoolPeriod($studentId);
                if (is_numeric($thereIsUnpaidSchoolPeriod)&&$thereIsUnpaidSchoolPeriod===0){
                    return self::taskError($internalCall,false);
                }
                $usersRol = array_column(auth()->payload()['user']->roles,'user_type');
                if (!$thereIsUnpaidSchoolPeriod || in_array('A',$usersRol)){
                    $response = [];
                    $subjectsInSchoolPeriod = SchoolPeriodSubjectTeacher::getSchoolPeriodSubjectTeacherBySchoolPeriod(
                        $schoolPeriodId);
                    if (is_numeric($subjectsInSchoolPeriod)&&$subjectsInSchoolPeriod===0){
                        return self::taskError($internalCall,false);
                    }
                    if (count($subjectsInSchoolPeriod)>0){
                        if (count($student['equivalence'])>0){
                            $subjectsInSchoolPeriod = self::filterEquivalenceToStudent($student['equivalence'],
                                $subjectsInSchoolPeriod);
                        }
                        $unregisteredSubjects = self::getUnregisteredSubjects($student,$subjectsInSchoolPeriod);
                        if (is_numeric($unregisteredSubjects)&&$unregisteredSubjects===0){
                            return self::taskError($internalCall,false);
                        }
                        if (count($unregisteredSubjects)>0){
                            $filterSubjectsBySchoolProgram = self::filterSubjectsBySchoolProgram(
                                $student['school_program_id'], $organizationId,$unregisteredSubjects);
                            if(is_numeric($filterSubjectsBySchoolProgram)&&$filterSubjectsBySchoolProgram===0){
                                return self::taskError($internalCall,false);
                            }
                            if (count($filterSubjectsBySchoolProgram)>0){
                                $availableSubjects= self::filterSubjectsEnrolledInSchoolPeriod($studentId,
                                    $schoolPeriodId, $filterSubjectsBySchoolProgram);
                                if (is_numeric($availableSubjects)&&$availableSubjects===0){
                                    return self::taskError($internalCall,false);
                                }
                                if (count($availableSubjects)>0){
                                    $totalQualification = self::getTotalQualification($studentId);
                                    if (is_string($totalQualification)&&$totalQualification==='e'){
                                        return self::taskError($internalCall,false);
                                    }
                                    $cantSubjectsEnrolled=StudentSubject::cantAllSubjectsEnrolledWithoutRETCUR(
                                        $studentId);
                                    if (is_string($cantSubjectsEnrolled)&&$cantSubjectsEnrolled==='e'){
                                        return self::taskError($internalCall,false);
                                    }
                                    if($cantSubjectsEnrolled>0 && ($totalQualification/$cantSubjectsEnrolled)<14){
                                        $response['message']=self::warningAverage;
                                    }
                                    $response['available_subjects']=$availableSubjects;
                                }
                            }
                        }
                    }
                    if (!isset($response['available_subjects'])){
                        $response['available_subjects']=[];
                    }
                    if ($schoolProgram['conducive_to_degree']){ // a partir de aqui disponibilidad de trabajo final o proyecto
                        $projectsInSchoolProgram = Subject::getProjectBySchoolProgram($schoolProgram['id'],
                            $organizationId); //obtener todas las asignaturas de tipo proyecto que estan disponibles en el programa
                        $project = FinalWork::getFinalWorksByStudent($studentId, true);
                        if ((is_numeric($project)&&$project===0)||(is_numeric($projectsInSchoolProgram)&&
                                $projectsInSchoolProgram===0)){
                            return self::taskError($internalCall,false);
                        }
                        $notApprovedProject = FinalWork::existNotApprovedFinalWork($studentId, true);
                        if(is_numeric($notApprovedProject)&&$notApprovedProject===0){
                            return self::taskError($internalCall,false);
                        }
                        if ((!$notApprovedProject && count($project)===count($projectsInSchoolProgram))||
                            $student['is_available_final_work']){
                            $availableProjectSubjects = self::getAvailableFinalSubjects($student,
                                $schoolProgram,$organizationId,false);
                            if (is_numeric($availableProjectSubjects)&& $availableProjectSubjects===0){
                                return self::taskError($internalCall,false);
                            }
                            $majorDateApproved = $project->toArray()[0]['approval_date'];
                            foreach ($project->toArray() as $date){
                                if ($date['approval_date']>$majorDateApproved){
                                    $majorDateApproved=$date['approval_date'];
                                }
                            }
                            if (($majorDateApproved<$schoolPeriod->toArray()[0]['start_date'] &&
                                    count($availableProjectSubjects)>0)|| ($student['is_available_final_work'] &&
                                    count($availableProjectSubjects)>0)){
                                $response['available_final_work']=true;
                                $response['final_work_subjects']=$availableProjectSubjects;
                                $response['approved_projects']=$project;
                            }
                        }
                        if(count($project)!==count($projectsInSchoolProgram)){
                            $notApprovedProject=true;
                        }

                        if($notApprovedProject){
                            $enrolledSubjects = SchoolPeriodStudent::getEnrolledSchoolPeriodsByStudent($student['id'],
                                $organizationId);
                            if (is_numeric($enrolledSubjects)&&$enrolledSubjects===0){
                                return self::taskError($internalCall,false);
                            }
                            if (count($enrolledSubjects)>0){
                                $enrolledSubjectsUntilThis = []; //Toma las asignaturas hasta el semestre antes del que estoy operando
                                foreach($enrolledSubjects->toArray() as $schoolPeriodSubject) {
                                    if ($schoolPeriodSubject['school_period']['start_date']<
                                        $schoolPeriod->toArray()[0]['start_date']){
                                        $enrolledSubjectsUntilThis[] = $schoolPeriodSubject;
                                    }
                                }
                                if (count($enrolledSubjectsUntilThis)>0){
                                    $dataPercentageStudent=ConstanceService::statisticsDataHistorical(
                                        $enrolledSubjectsUntilThis);
                                    $approvedDoctoralExam =
                                        DoctoralExam::existDoctoralExamApprovedByStudentInNotSchoolPeriod(
                                            $student['id'],$schoolPeriodId);
                                    if (is_numeric($approvedDoctoralExam)&&$approvedDoctoralExam===0){
                                        return self::taskError($internalCall,false);
                                    }
                                    if (($schoolProgram['doctoral_exam']&&$approvedDoctoralExam)||
                                        !$schoolProgram['doctoral_exam']){
                                        $availableProject = self::availableProject($student,$schoolProgram,
                                            $organizationId,$dataPercentageStudent);
                                        if (is_numeric($availableProject)&&$availableProject===0){
                                            return self::taskError($internalCall,false);
                                        }
                                        if ($availableProject){
                                            $availableProjectSubjects = self::getAvailableFinalSubjects($student,
                                                $schoolProgram,$organizationId,true);
                                            if (is_numeric($availableProjectSubjects)&& $availableProjectSubjects===0){
                                                return self::taskError($internalCall,false);
                                            }
                                            if (count($availableProjectSubjects)>0){
                                                $response['available_project']=true;
                                                $response['project_subjects']=$availableProjectSubjects;
                                            }
                                        }
                                    }
                                    if ($schoolProgram['doctoral_exam']&&!$approvedDoctoralExam&&
                                        ($dataPercentageStudent['enrolled_credits']+$student['credits_granted']>=
                                            $schoolProgram['num_cu_to_doctoral_exam'])){
                                        $response['available_doctoral_exam']=true;
                                    }
                                }
                            }

                        }
                    }
                    if (count($response['available_subjects'])<1&&(!isset($response['available_project'])&&
                            !isset($response['available_final_work'])&&!isset($response['available_doctoral_exam']))){
                        if ($internalCall){return 3;}
                        return response()->json(['message'=>self::thereAreNotSubjectsAvailableToRegister],206);
                    }
                    return $response;
                }
                if ($internalCall){return 4;}
                return response()->json(['message'=>self::thereAreSchoolPeriodWithoutPaying],206);
            }
        }
        if ($internalCall){return 5;}
        return response()->json(['message'=>self::notFoundStudentGivenId],206);
    }

    /**
     *Valida que se cumpla las restricciones:
     * *student_id: requerido y numérico
     * *school_period_id: requerido y numérico
     * *status: máximo 3 y debe terminar en RET-A, RET-B, RIN-A, RIN-B, DES-A, DES-B, RIN-A, RIN-B, REI-A, REI-B o REG
     * *financing: máximo 3 y debe terminar en EXO, SFI, SCS o FUN.
     * *financing_description: máximo 60
     * *pay_ref: máximo 50
     * *amount_paid: numérico
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validate(Request $request)
    {
        $request->validate([
            'student_id'=>'required|numeric',
            'school_period_id'=>'required|numeric',
            'financing'=>'max:3|ends_with:EXO,SFI,SCS,FUN',//EXO exonerated, FUN Funded, SFI Self-financing, SCS Scholarship
            'financing_description'=>'max:60',
            'pay_ref'=>'max:50',
            'amount_paid'=>'numeric'
        ]);
    }

    /**
     *Valida que se cumpla las restricciones:
     * *subjects.*.school_period_teacher_id: requerido y numérico
     * *subjects.*.qualification: numérico y en un rango de 0 a 20
     * *subjects.*.status: máximo 3 y termina en CUR, RET, APR y REP
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validateSubjects(Request $request)
    {
        $request->validate([
            'subjects.*.school_period_subject_teacher_id'=>'required|numeric',
            'subjects.*.qualification'=>'numeric|between:0,20',
            'subjects.*.status'=>'max:3|ends_with:CUR,RET,APR,REP'
        ]);
    }

    /**
     *Valida que se cumpla las restricciones:
     * *projects.*.title: requerido y máximo 100
     * *projects.*.subject_id: requerido y numérico
     * *projects.*.status:  máximo 10 y debe terminar en APPROVED, REPROBATE o PROGRESS
     * *projects.*.description_status: máximo 200
     * *projects.*.approval_date: máximo 10
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validateProjects(Request $request)
    {
        $request->validate([
            'projects.*.title'=>'required|max:100',
            'projects.*.subject_id'=>'required|numeric',
            'projects.*.status'=>'max:10|ends_with:PROGRESS,APPROVED,REPROBATE',
            'projects.*.description_status'=>'max:200',
            'projects.*.approval_date'=>'size:10'
        ]);
    }

    /**
     *Valida que se cumpla las restricciones:
     * *final_works.*.title: requerido y máximo 100
     * *final_works.*.subject_id: requerido y numérico
     * *final_works.*.project_id: numérico
     * *final_works.*.status:  máximo 10 y debe terminar en APPROVED, REPROBATE o PROGRESS
     * *final_works.*.description_status: máximo 200
     * *final_works.*.approval_date: máximo 10
     * *final_works.*.advisors.*.teacher_id: numérico
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validateInscriptionFinalWork(Request $request)
    {
        $request->validate([
            'final_works.*.title'=>'required|max:100',
            'final_works.*.subject_id'=>'required|numeric',
            'final_works.*.project_id'=>'numeric',
            'final_works.*.status'=>'max:10|ends_with:PROGRESS,APPROVED,REPROBATE',
            'final_works.*.description_status'=>'max:200',
            'final_works.*.approval_date'=>'size:10',
            'final_works.*.advisors.*.teacher_id'=>'numeric'
        ]);
    }

    /**
     *Valida que se cumpla las restricciones:
     * *doctoral.exam:requerido, máximo 10 y debe terminar en APPROVED o  REPROBATE
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validateDoctoralExam(Request $request)
    {
        $request->validate([
            'doctoral_exam.status'=>'required|max:10|ends_with:APPROVED,REPROBATE'
        ]);
    }

    /**
     * Valida que las asignaturas que se desean inscribir sean las que el estudiante tiene disponible, si todas son
     * válidas devolverá true, de lo contrario será false.
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @param array $availableSubjects Objeto con las asignaturas disponibles para inscribir e información de los
     * proyectos o trabajos finales
     * @return integer|boolean Devuelve un booleano validando si las asignaturas solicitadas para inscribir estan
     * disponibles para el estudiante en caso de existir un error devolvera 0.
     */
    public static function validateSubjectsToInscription(Request $request, $organizationId,$availableSubjects)
    {
        $existSchoolPeriodById=SchoolPeriod::existSchoolPeriodById($request['school_period_id'],$organizationId);
        if (is_numeric($existSchoolPeriodById)&&$existSchoolPeriodById===0){
            return 0;
        }
        if (!$existSchoolPeriodById){
            return false;
        }
        if (count($availableSubjects)<1){
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

    /**
     * Construye un objeto de tipo StudentSubject de acuerdo con los valores pasados por parámetros.
     * @param object $subject Contenido parcial del objeto StudentSubject
     * @param integer $schoolPeriodStudentId  Id el objeto SchoolPeriodStudent
     * @param boolean $isWithdrawn: Bandera para determinar si se retiró de la asignatura
     * @return array Devuelve un arreglo con la estructura de un objeto StudentSubject.
     */
    public static function buildStudentSubject($subject,$schoolPeriodStudentId,$isWithdrawn)
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

    /**
     * Agrega las asignaturas inscritas a un estudiante en un periodo escolar con el método
     * StudentSubject::addStudentSubject($studentSubject)
     * @param array $subjects Contenido parcial del objeto StudentSubject proviene de la petición
     * @param integer $schoolPeriodStudentId  Id el objeto SchoolPeriodStudent
     * @param boolean $isWithdrawn: Bandera para determinar si se retiró de la asignatura
     * @return array|integer Inscribe un estudiante en un estudiante en una asignatura asociada a un periodo escolar y
     * actualiza la cantidad de estudiantes inscritos en dicha asignatura.
     */
    public static function addSubjects($subjects,$schoolPeriodStudentId,$isWithdrawn)
    {
        foreach ($subjects as $subject){
            $usersRol = array_column(auth()->payload()['user']->roles,'user_type');
            if (!in_array('A',$usersRol)){
                unset($subject['qualification']);
                unset($subject['status']);
            }
            $studentSubject = self::buildStudentSubject($subject,$schoolPeriodStudentId,$isWithdrawn);
            $result = StudentSubject::addStudentSubject($studentSubject);
            if (is_numeric($result)&&$result===0){
                return 0;
            }
            $result =SchoolPeriodSubjectTeacher::updateEnrolledStudent($subject['school_period_subject_teacher_id']);
            if (is_numeric($result)&&$result===0){
                return 0;
            }
        }
    }

    /**
     * Agrega los tutores asociados a un trabajo especial de grado con el método
     * Advisor::addAdvisor( [
     * 'final_work_id'=>$finalWorkId,
     * 'teacher_id'=>$advisor['teacher_id'] ] ).
     * @param array $advisors Contenido parcial del objeto StudentSubject proviene de la petición
     * @param integer $finalWorkId Id del objeto finalWork
     * @return integer Agrega tutores a un trabajo de grado de fallar devolvera 0.
     */
    public static function addAdvisors($advisors,$finalWorkId)
    {
        foreach($advisors as $advisor){
            $result = Advisor::addAdvisor(
                [
                    'final_work_id'=>$finalWorkId,
                    'teacher_id'=>$advisor['teacher_id']
                ]
            );
            if (is_numeric($result)&&$result===0){
                return 0;
            }
        }
    }

    /**
     * Crea el objeto FinalWork con el método FinalWork::addFinalWork($finalWork) y su objeto asociado
     * finalWorkSchoolPeriod con el método FinalWorkSchoolPeriod::addFinalWorkSchoolPeriod($finalWork).
     * @param integer $studentId Id del estudiante
     * @param object $finalWork Objeto con contenido parcial del proyecto o trabajo final
     * @param int $schoolPeriodStudentId Id del objeto SchoolPeriodStudent
     * @param boolean $isProject Flag para determinar si lo que se crea es Proyecto o trabajo final
     * @return integer Crea trabajos de grados o proyectos asociados a un estudiante, de fallar devolvera 0.
     */
    public static function createFinalWork($studentId,$finalWork,$schoolPeriodStudentId,$isProject)
    {
        $existProject = false;
        if (!$isProject && isset($finalWork['project_id'])){
            $existProject = FinalWork::existFinalWorkByIdAndStatus($finalWork['project_id'],true,'APPROVED');
            if (is_numeric($existProject)&&$existProject===0){
                return 0;
            }
        }
        if (!$existProject){
            $finalWork['project_id']=null;
        }
        $finalWork['student_id']=$studentId;
        $finalWork['is_project']=$isProject;
        $existAdvisors= false;
        $status = 'PROGRESS';
        $descriptionStatus = '';
        if (isset($finalWork['status'])){
            $status=$finalWork['status'];
            if ($status=='APPROVED' && !isset($finalWork['approval_date'])){
                $finalWork['approval_date']=(now()->toDateTimeString());
            }
            unset($finalWork['status']);
        }
        if (isset($finalWork['description_status'])){
            $descriptionStatus=$finalWork['description_status'];
            unset($finalWork['description_status']);
        }
        $advisors = [];
        if (isset($finalWork['advisors'])){
            $advisors=$finalWork['advisors'];
            $existAdvisors=true;
            unset($finalWork['advisors']);
        }
        $finalWorkId=FinalWork::addFinalWork($finalWork);
        if (is_numeric($finalWorkId)&&$finalWorkId===0){
            return 0;
        }
        if ($existAdvisors){
            $finalWork['advisors']=$advisors;
        }
        $finalWork['final_work_id']=$finalWorkId;
        $finalWork['school_period_student_id']=$schoolPeriodStudentId;
        $finalWork['status']=$status;
        $finalWork['description_status']=$descriptionStatus;
        $result = FinalWorkSchoolPeriod::addFinalWorkSchoolPeriod($finalWork);
        if (is_numeric($result)&&$result===0){
            return 0;
        }
        if (!$isProject && isset($finalWork['advisors'])){
            $result = self::addAdvisors($finalWork['advisors'],$finalWorkId);
            if (is_numeric($result)&&$result===0){
                return 0;
            }
        }
    }

    /**
     * Devuelve true si en alguno de los periodos escolares en los que se inscribió el proyecto o trabajo final fue
     * reprobado, de lo contrario devolverá false.
     * @param array $schoolPeriods Períodos escolares en los cuales se inscribió un proyecto o trabajo final
     * @return boolean Devuelve un booleano dependiendo de si existe o no un trabajo de grado  o proyecto reprobado
     */
    public static function existStatusREPROBATE($schoolPeriods)
    {
        foreach($schoolPeriods as $schoolPeriod){
            if ($schoolPeriod['status']=='REPROBATE'){
                return true;
            }
        }
        return false;
    }

    /**
     * Actualiza el objeto FinalWork con el método FinalWork::updateFinalWork($finalWorkInBd['id'],$finalWork) y su
     * objeto asociado finalWorkSchoolPeriod si se encuentra en base de datos el schoolPeriodStudentId se actualizará
     * con con el método FinalWorkSchoolPeriod::updateFinalWorkSchoolPeriod($schoolPeriod['id'],$finalWork) de lo
     * contrario se creará con el metodo FinalWorkSchoolPeriod::addFinalWorkSchoolPeriod($finalWork).
     * @param integer $studentId Id del estudiante
     * @param object $finalWork Objeto con contenido parcial del proyecto o trabajo final
     * @param int $schoolPeriodStudentId Id del objeto SchoolPeriodStudent
     * @param boolean $isProject Flag para determinar si lo que se crea es Proyecto o trabajo final
     * @param array $finalWorkInBd Proyecto o trabajo final en base de datos
     * @return integer Actualiza trabajos de grados o proyectos asociados a un estudiante, de fallar devolvera 0.
     */
    public static function updateFinalWork($studentId, $finalWork, $schoolPeriodStudentId, $isProject, $finalWorkInBd)
    {
        $existProject = false;
        if (!$isProject && isset($finalWork['project_id'])){
            $project = FinalWork::existFinalWorkByIdAndStatus($finalWork['project_id'],true,'APPROVED');
            if (is_numeric($project)&&$project===0){
                return 0;
            }
        }
        if (!$existProject){
            $finalWork['project_id']=null;
        }
        $finalWork['student_id']=$studentId;
        $finalWork['is_project']=$isProject;
        $result=FinalWork::updateFinalWork($finalWorkInBd['id'],$finalWork);
        if (is_numeric($result)&&$result===0){
            return 0;
        }
        $existFinalWorkSchoolPeriod=false;
        $finalWork['final_work_id']=$finalWorkInBd['id'];
        $finalWork['school_period_student_id']=$schoolPeriodStudentId;
        $usersRol = array_column(auth()->payload()['user']->roles,'user_type');
        if (!in_array('A',$usersRol) && $finalWork['status']!='PROGRESS'){
            $finalWork['status']='PROGRESS';
        }
        if (!isset($finalWork['description_status'])){
            $finalWork['description_status']=null;
        }
        foreach ($finalWorkInBd['schoolPeriods'] as $schoolPeriod){ //search for update finalWorkSchoolPeriod
            if ($schoolPeriod['finalWorkSchoolPeriod']['school_period_student_id']==$schoolPeriodStudentId){
                $result=FinalWorkSchoolPeriod::updateFinalWorkSchoolPeriod(
                    $schoolPeriod['finalWorkSchoolPeriod']['id'],$finalWork);
                $existFinalWorkSchoolPeriod=true;
                if (is_numeric($result)&&$result===0){
                    return 0;
                }
                break;
            }
        }
        if (!$existFinalWorkSchoolPeriod){
            $result = FinalWorkSchoolPeriod::addFinalWorkSchoolPeriod($finalWork);
            if (is_numeric($result)&&$result===0){
                return 0;
            }
        }
        if (!$isProject){
            $result = Advisor::deleteAllAdvisor($finalWorkInBd['id']);
            if (is_numeric($result)&&$result===0){
                return 0;
            }
            if (isset($finalWork['advisors'])){
                $result = self::addAdvisors($finalWork['advisors'],$finalWorkInBd['id']);
            }
            if (is_numeric($result)&&$result===0){
                return 0;
            }
        }
    }

    /**
     * Crea o actualiza los proyectos o trabajos finales dado sus estatus con los métodos
     * self::createFinalWork($studentId,$finalWork,$schoolPeriodStudentId,$isProject) y
     * self::updateFinalWork($studentId,$finalWork,$schoolPeriodStudentId,$isProject,$subjectFinalWork[0]), en caso de
     * que el estudiante repruebe dos intentos de proyectos o trabajo final este pasará a un estatus de retiro tipo b y
     * terminara el programa escolar, por otro lado si aprueba el trabajo de grado este pasará a un estatus ended el
     * cual culminará con su programa escolar completo.
     * @param integer $studentId Id del estudiante
     * @param array $finalWorks Array de objeto con contenido parcial del proyecto o trabajo final
     * @param int $schoolPeriodStudentId Id del objeto SchoolPeriodStudent
     * @param boolean $isProject Flag para determinar si lo que se crea es Proyecto o trabajo final
     * @param array $availableFinalWorks Proyectos o trabajos finales disponibles por inscribir
     * @param string $organizationId Id de la organiación
     * @return integer Crea o actualiza trabajos de grados o proyectos asociados a un estudiante, de fallar devolvera 0.
     */
    public static function setProjectsOrFinalWorks($studentId, $finalWorks, $schoolPeriodStudentId, $isProject,
                                                   $availableFinalWorks,$organizationId)
    {
        if (count($availableFinalWorks)>0){
            if (!is_array($availableFinalWorks)){
                $availableFinalWorks=$availableFinalWorks->toArray();
            }
            $availableFinalWorkIds = array_column($availableFinalWorks,'id');
            foreach ($finalWorks as $finalWork){
                $retB=false;
                if ($finalWork['status']!='APPROVED'){
                    $finalWork['approval_date']=null;
                }
                $subjectFinalWork = FinalWork::getFinalWorksByStudentSubject($studentId,$finalWork['subject_id'],
                    $isProject);
                if (is_numeric($subjectFinalWork)&&$subjectFinalWork===0){
                    return 0;
                }
                if (count($subjectFinalWork)>0){
                    $availableFinalWorkIds[]=$finalWork['subject_id'];
                }
                if (in_array($finalWork['subject_id'],$availableFinalWorkIds)){
                    $result=1;//initial value
                    if (count($subjectFinalWork)==0){ //new project or final work
                        $result = self::createFinalWork($studentId,$finalWork,$schoolPeriodStudentId,$isProject);
                    }else{//exist project or final work
                        if (count($subjectFinalWork)==1){// exist one project or final work
                            if (count($subjectFinalWork[0]['schoolPeriods'])==1){ //update first attempt or create second project or final work
                                if (self::existStatusREPROBATE($subjectFinalWork[0]['schoolPeriods'])){//second project o final work
                                    $result = self::createFinalWork($studentId,$finalWork,$schoolPeriodStudentId,
                                        $isProject);
                                    if ($finalWork['status']=="REPROBATE"){
                                        $retB=true;
                                    }
                                }else{//update first attempt
                                    $result = self::updateFinalWork($studentId,$finalWork,$schoolPeriodStudentId,
                                        $isProject,$subjectFinalWork[0]);
                                }
                            }
                        }
                        if (count($subjectFinalWork)==2) {// update second attempt
                            foreach ($subjectFinalWork as $subject){
                                if (!self::existStatusREPROBATE($subject['schoolPeriods'])){
                                    $result = self::updateFinalWork($studentId,$finalWork,$schoolPeriodStudentId,
                                        $isProject,$subject);
                                    if ($finalWork['status']=="REPROBATE"){
                                        $retB=true;
                                    }
                                }
                            }
                        }
                    }
                    if ($result===0){
                        return 0;
                    }
                    if ($retB){
                        $result = self::retBStudent($studentId,$schoolPeriodStudentId,$organizationId);
                        if ($result===0){
                            return 0;
                        }
                    }
                    if (!$isProject && isset($finalWork['status']) && $finalWork['status']==='APPROVED'){
                        $student = Student::getStudentById($studentId,$organizationId);
                        if (is_numeric($student)&&$student===0){
                            return 0;
                        }
                        $student[0]['current_status']='ENDED';
                        $student[0]['end_program']=true;
                        $result=Student::updateStudent($studentId,$student[0]->toArray());
                        if (is_numeric($result)&&$result==0){
                            return 0;
                        }
                    }
                }
            }
            $result = self::deleteFinalWorkSchoolPeriodNotUpdate($finalWorks,$schoolPeriodStudentId);
            if (is_numeric($result)&&$result===0){
                return 0;
            }
        }
    }

    /**
     * Elimina los finalWorks que no fueron agregados al editar la inscripción del estudiante.
     * @param array $finalWorks Array de objeto con contenido parcial del proyecto o trabajo final
     * @param int $schoolPeriodStudentId Id del objeto SchoolPeriodStudent
     * @return integer Elimina los finalWorks que no estan en la lista, de fallar devolvera 0.
     */
    public static function deleteFinalWorkSchoolPeriodNotUpdate($finalWorks,$schoolPeriodStudentId){
        $finalWorkSchoolPeriodsUpdated = [];
        $finalWorkSchoolPeriods = FinalWorkSchoolPeriod::getFinalWorkSchoolPeriodBySchoolPeriodStudentId(
            $schoolPeriodStudentId);
        if (is_numeric($finalWorkSchoolPeriods)&&$finalWorkSchoolPeriods===0){
            return 0;
        }
        foreach ($finalWorks as $finalWork){
            foreach ($finalWorkSchoolPeriods as $finalWorkSchoolPeriod){
                if ($finalWorkSchoolPeriod['school_period_student_id']==$schoolPeriodStudentId &&
                    $finalWorkSchoolPeriod['finalWork']['subject_id']==$finalWork['subject_id']){
                    $finalWorkSchoolPeriodsUpdated[]= $finalWorkSchoolPeriod['id'];
                }
            }
        }
        foreach ($finalWorkSchoolPeriods as $finalWorkSchoolPeriodId){
            if (!in_array($finalWorkSchoolPeriodId['id'],$finalWorkSchoolPeriodsUpdated)){
                $finalWork = FinalWork::getFinalWork($finalWorkSchoolPeriodId['final_work_id']);
                if (is_numeric($finalWork)&&$finalWork===0){
                    return 0;
                }
                if (count($finalWork[0]['schoolPeriods'])==1){
                    $result = FinalWork::deleteFinalWork($finalWork[0]['id']);
                }else{
                    $result = FinalWorkSchoolPeriod::deleteFinalWorkSchoolPeriod($finalWorkSchoolPeriodId['id']);
                }
                if (is_numeric($result)&&$result===0){
                    return 0;
                }
            }
        }
    }

    /**
     * Crea un examen doctoral a un estudiante con un estatus de aprobado o reprobado asociado a un periodo escolar con
     * el método DoctoralExam::getDoctoralExamByStudent($studentId) en caso de que el estudiante repruebe su segundo
     * intento de realizar el examen doctoral este estudiante pasa a un estatus de retiro tipo b y culmina el programa
     * escolar.
     * @param integer $studentId Id del estudiante
     * @param object $doctoralExam Contiene el estatus del examen doctoral
     * @param int $schoolPeriodStudentId Id del objeto SchoolPeriodStudent
     * @param string $organizationId Id de la organiación
     * @return integer Crea un examen doctoral a un estudiante, de fallar devolvera 0.
     */
    public static function setDoctoralExam($studentId, $doctoralExam, $schoolPeriodStudentId,$organizationId){
        $retB=false;
        $doctoralExams=DoctoralExam::getDoctoralExamByStudent($studentId);
        if (is_numeric($doctoralExams)&&$doctoralExams===0){
            return 0;
        }
        $result=1;
        if (count($doctoralExams)==0){
            $result = DoctoralExam::addDoctoralExam($schoolPeriodStudentId,$doctoralExam['status']);
        }
        if (count($doctoralExams)==1){
            if ($doctoralExams[0]['school_period_student_id']==$schoolPeriodStudentId){
                $delete=DoctoralExam::deleteDoctoralExam($schoolPeriodStudentId);
                if (is_numeric($delete)&&$delete===0){
                    return 0;
                }
                $result = DoctoralExam::addDoctoralExam($schoolPeriodStudentId,$doctoralExam['status']);
            }else{
                $result = DoctoralExam::addDoctoralExam($schoolPeriodStudentId,$doctoralExam['status']);
                if ($doctoralExam['status']=='REPROBATE'){
                    $retB=true;
                }
            }
        }
        if (count($doctoralExams)==2){
            foreach ($doctoralExams as $exam){
                if ($exam['school_period_student_id']==$schoolPeriodStudentId){
                    $delete=DoctoralExam::deleteDoctoralExam($schoolPeriodStudentId);
                    if (is_numeric($delete)&&$delete===0){
                        return 0;
                    }
                    $result = DoctoralExam::addDoctoralExam($schoolPeriodStudentId,$doctoralExam['status']);
                    if ($doctoralExam['status']=='REPROBATE'){
                        $retB=true;
                    }
                }
            }
        }
        if (is_numeric($result)&&$result==0){
            return 0;
        }
        if ($retB){
            $result = self::retBStudent($studentId,$schoolPeriodStudentId,$organizationId);
            if (is_numeric($result)&&$result==0){
                return 0;
            }
        }
    }

    /**
     * Cambia el estatus del periodo escolar y del estudiante a retirado tipo b y concluye el periodo del estudiante en
     * el programa escolar.
     * @param integer $studentId Id del estudiante
     * @param int $schoolPeriodStudentId Id del objeto SchoolPeriodStudent
     * @param string $organizationId Id de la organiación
     * @return integer Actualiza el estatus del estudiante a RET-B, de fallar devolvera 0.
     */
    public static function retBStudent($studentId,$schoolPeriodStudentId,$organizationId){
        $schoolPeriodStudent=SchoolPeriodStudent::getSchoolPeriodStudentById($schoolPeriodStudentId,$organizationId);
        if (is_numeric($schoolPeriodStudent)&&$schoolPeriodStudent===0){
            return 0;
        }
        $schoolPeriodStudent[0]['status']='RET-B';
        unset($schoolPeriodStudent[0]['student']);
        unset($schoolPeriodStudent[0]['enrolledSubjects']);
        unset($schoolPeriodStudent[0]['schoolPeriod']);
        unset($schoolPeriodStudent[0]['finalWorkData']);
        $result=SchoolPeriodStudent::updateSchoolPeriodStudentLikeArray($schoolPeriodStudentId,
            $schoolPeriodStudent[0]->toArray());
        if (is_numeric($result)&&$result==0){
            return 0;
        }
        $student = Student::getStudentById($studentId,$organizationId);
        if (is_numeric($student)&&$student===0){
            return 0;
        }
        $student[0]['status']='RET-B';
        $student[0]['end_program']=true;
        $result=Student::updateStudent($studentId,$student[0]->toArray());
        if (is_numeric($result)&&$result==0){
            return 0;
        }
    }

    /**
     * Agrega las inscripciones del estudiante con el método SchoolPeriodStudent::addSchoolPeriodStudent($request) y
     * self::addSubjects($request['subjects'],$schoolPeriodStudentId,false), para inscribir proyecto o trabajo especial
     * de grado usa el método self::setProjectOrFinalWork($student[0],$request['projects'],$schoolPeriodStudentId,
     * $organizationId,true,$organizationId),y exámenes doctorales con el método
     * self::setDoctoralExam($student[0]['id'],$request['doctoral_exam'],$schoolPeriodStudentId,$organizationId).
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @return array|Response Devuelve la inscripcion realizada, de fallar devolvera un objeto con un mensaje indicando
     * el error asociado
     */
    public static function addInscription(Request $request,$organizationId)
    {
        self::validate($request);
        if (isset($request['subjects'])&&count($request['subjects'])>0){
            self::validateSubjects($request);
        }
        if (isset($request['projects'])){
            self::validateProjects($request);
        }
        if (isset($request['final_works'])){
            self::validateInscriptionFinalWork($request);
        }
        if (isset($request['doctoral_exam'])){
            self::validateDoctoralExam($request);
        }
        if (isset($request['projects'])||isset($request['final_works'])||isset($request['subjects'])){
            $existSchoolPeriod=SchoolPeriodStudent::existSchoolPeriodStudent($request['student_id'],
                $request['school_period_id']);
            if (is_numeric($existSchoolPeriod)&&$existSchoolPeriod===0){
                return self::taskError(false,false);
            }
            if (!$existSchoolPeriod) {
                $student=Student::getStudentById($request['student_id'],$organizationId);
                if (is_numeric($student)&&$student===0){
                    return self::taskError(false,false);
                }
                $schoolPeriod = SchoolPeriod::getSchoolPeriodById($request['school_period_id'],$organizationId);
                if (is_numeric($schoolPeriod)&&$schoolPeriod===0){
                    return self::taskError(false,false);
                }
                if (count($student)>0){
                    $request['status']=$student[0]['current_status'];
                    $request['test_period']=$student[0]['test_period'];
                    if ($request['status']!='RET-A'&&$request['status']!='RET-B'&&$request['status']!='DES-A'&&
                        $request['status']!='DES-B'&&$request['status']!='ENDED'){
                        $availableSubjects = self::getAvailableSubjects($request['student_id'],
                            $request['school_period_id'], $organizationId, true);
                        $usersRol = array_column(auth()->payload()['user']->roles,'user_type');
                        if (is_numeric($availableSubjects)){
                            if ($availableSubjects===0){
                                return self::taskError(false,false);
                            }
                            if ($availableSubjects===1){
                                return response()->json(['message' => self::notAllowedRegister], 206);
                            }
                            if ($availableSubjects===2){
                                return response()->json(['message' => self::endProgram], 206);
                            }
                            if ($availableSubjects===3){
                                return response()->json(['message'=>self::thereAreNotSubjectsAvailableToRegister],206);
                            }
                            if ($availableSubjects===4 && !in_array('A',$usersRol)){
                                return response()->json(['message'=>self::thereAreSchoolPeriodWithoutPaying],206);
                            }
                            if ($availableSubjects===5){
                                return response()->json(['message'=>self::notFoundStudentGivenId],206);
                            }
                        }
                        if(in_array('A',$usersRol)&& strtotime($schoolPeriod->toArray()[0]['start_date'])<=strtotime(now()->toDateTimeString())){
                            $request['inscription_date']=$schoolPeriod->toArray()[0]['inscription_start_date'];
                        }
                        $schoolPeriodStudentId=SchoolPeriodStudent::addSchoolPeriodStudent($request);
                        if (is_numeric($schoolPeriodStudentId)&&$schoolPeriodStudentId===0){
                            return self::taskError(false,false);
                        }
                        if (isset($request['subjects'])&&count($request['subjects'])>0){
                            $validateSubjectToInscription=self::validateSubjectsToInscription($request,$organizationId,
                                $availableSubjects);
                            if (is_numeric($validateSubjectToInscription)&&$validateSubjectToInscription===0){
                                return self::taskError(false,false);
                            }
                            if($validateSubjectToInscription){
                                $result = self::addSubjects($request['subjects'],$schoolPeriodStudentId,false);
                                if (is_numeric($result)&&$result===0){
                                    return self::taskError(false,true);
                                }
                            }else{
                                $result = SchoolPeriodStudent::deleteSchoolPeriodStudent($schoolPeriodStudentId);
                                if (is_numeric($result)&&$result===0){
                                    return self::taskError(false,true);
                                }
                                return response()->json(['message'=>self::invalidSubjectToInscription],206);
                            }
                        }
                        $result=1; //flag para el resultado generico
                        if ((isset($availableSubjects['available_doctoral_exam'])&&
                                $availableSubjects['available_doctoral_exam']) && isset($request['doctoral_exam'])){
                            $result =self::setDoctoralExam($student[0]['id'],$request['doctoral_exam'],
                                $schoolPeriodStudentId,$organizationId);
                        }
                        if ((isset($availableSubjects['available_project'])&&$availableSubjects['available_project'])
                            && isset($request['projects'])){
                            $result =self::setProjectsOrFinalWorks($student[0]['id'],$request['projects'],
                                $schoolPeriodStudentId, true, $availableSubjects['project_subjects'],
                                $organizationId);
                        }
                        if ((isset($availableSubjects['available_final_work'])&&
                                $availableSubjects['available_final_work']) && isset($request['final_works'])){
                            $result = self::setProjectsOrFinalWorks($student[0]['id'],$request['final_works'],
                                $schoolPeriodStudentId,false, $availableSubjects['final_work_subjects'],
                                $organizationId);
                        }
                        if (is_numeric($result)&&$result===0){
                            return self::taskError(false,true);
                        }
                        $log = Log::addLog(auth('api')->user()['id'],
                            self::logAddInscription.$request['student_id']);
                        if (is_numeric($log)&&$log==0){
                            return self::taskError(false,true);
                        }
                        $student=Student::getStudentById($request['student_id'],$organizationId);
                        if (is_numeric($student) && $student==0){
                            return self::taskError(false,true);
                        }
                        if(count($student)>0){
                            $student[0]['allow_post_inscription']=false;
                            $result = Student::updateStudent($student[0]['id'],$student[0]->toArray());
                            if (is_numeric($result)&&$result==0){
                                return self::taskError(false,true);
                            }
                        }
                        return self::getInscriptionById($schoolPeriodStudentId,$organizationId);
                    }
                    return response()->json(['message' => self::notAllowedRegister], 206);
                }
                return response()->json(['message'=>self::notFoundStudentGivenId],206);
            }
            return response()->json(['message'=>self::inscriptionReady],206);
        }
        return response()->json(['message' => self::notRegister], 206);
    }


    /**
     * Elimina una inscripción de un estudiante en un periodo escolar con el método
     * SchoolPeriodStudent::deleteSchoolPeriodStudent($id).
     * @param string $id Id de la inscripción
     * @param string $organizationId Id de la organiación
     * @return Response, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcto
     * devolvera un objeto con mensaje OK.
     */
    public static function deleteInscription($id,$organizationId)
    {
        $schoolPeriodStudentById=SchoolPeriodStudent::getSchoolPeriodStudentById($id,$organizationId);
        if (is_numeric($schoolPeriodStudentById) && $schoolPeriodStudentById===0){
            return self::taskError(false,false);
        }
        if(count($schoolPeriodStudentById)>0){
            $result=SchoolPeriodStudent::deleteSchoolPeriodStudent($id);
            if (is_numeric($result)&&$result===0){
                return self::taskError(false,false);
            }
            $log = Log::addLog(auth('api')->user()['id'],
                self::logDeleteFinalWork.$schoolPeriodStudentById[0]['student_id']);
            if (is_numeric($log)&&$log==0){
                return self::taskError(false,true);
            }
            return response()->json(['message'=>self::OK]);
        }
        return response()->json(['message'=>self::notFoundInscription],206);
    }

    /**
     * Valida que las asignaturas que se desean inscribir sean las que el estudiante tiene disponible, si todas son
     * válidas devolverá true, de lo contrario será false.
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @param array $availableSubjects Objeto con las asignaturas disponibles para inscribir e información de los
     * proyectos o trabajos finales
     * @return integer|boolean Devuelve un booleano validando si las asignaturas solicitadas para inscribir estan
     * disponibles para el estudiante en caso de existir un error devolvera 0.
     */
    public static function validateRelationUpdate(Request $request,$organizationId,$availableSubjects)
    {
        $existSchoolPeriodById=SchoolPeriod::existSchoolPeriodById($request['school_period_id'],$organizationId);
        if (is_numeric($existSchoolPeriodById)&&$existSchoolPeriodById===0){
            return 0;
        }
        if (!$existSchoolPeriodById){
            return false;
        }
        $subjectsEnrolledInSchoolPeriod = SchoolPeriodStudent::findSchoolPeriodStudent($request['student_id'],
            $request['school_period_id']);
        if (is_numeric($subjectsEnrolledInSchoolPeriod)&&$subjectsEnrolledInSchoolPeriod===0){
            return 0;
        }
        $subjectsEnrolledInSchoolPeriod=$subjectsEnrolledInSchoolPeriod[0]['enrolledSubjects'];
        if (is_array($availableSubjects)&&(count($availableSubjects)<1 && count($subjectsEnrolledInSchoolPeriod)<1 )){
            return false;
        }
        if (is_array($availableSubjects)){
            $availableSubjectsId=array_column($availableSubjects['available_subjects'],'id');
        }else{
            $availableSubjectsId=[];
        }
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

    /**
     * En caso de que todas las asignaturas del estudiante sean retiradas del semestre el estatus del semestre inscrito
     * y del estudiante cambian a DES-A, con los siguientes métodos
     * SchoolPeriodStudent::updateSchoolPeriodStudentLikeArray($schoolPeriodStudentId,$schoolPeriodStudent->toArray()) y
     * Student::updateStudent($schoolPeriodStudent['student_id'],$student[0]->toArray()).
     * @param int $schoolPeriodStudentId Id del objeto SchoolPeriodStudent
     * @param string $organizationId Id de la organiación
     * @return integer Actualiza el estatus del estudiante a DES-A si retirar todas las asignaturas, de fallar devolvera
     * 0.
     */
    public static function updateStatusDESA($schoolPeriodStudentId, $organizationId) //Actualiza el status del estudiante sobre el periodo escolar
    {
        $schoolPeriodStudent = SchoolPeriodStudent::getSchoolPeriodStudentById($schoolPeriodStudentId,$organizationId);
        if (is_numeric($schoolPeriodStudent)&&$schoolPeriodStudent===0){
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
            $schoolPeriodStudent['status']='DES-A'; //Si un estudiante retira todas las asignaturas debe caer en DES-A
            $result =SchoolPeriodStudent::updateSchoolPeriodStudentLikeArray($schoolPeriodStudentId,
                $schoolPeriodStudent->toArray());
            if (is_numeric($result)&&$result===0){
                return 0;
            }
            $student = Student::getStudentById($schoolPeriodStudent['student_id'],$organizationId);
            if (is_numeric($student)&&$student===0){
                return 0;
            }
            $student[0]['current_status']='DES-A';
            $result = Student::updateStudent($schoolPeriodStudent['student_id'],$student[0]->toArray());
            if (is_numeric($result)&&$result===0){
                return 0;
            }
        }
    }

    /**
     * Actualiza las asignaturas inscritas en el periodo escolar dado con el método
     * StudentSubject::updateStudentSubject($subjectInBd['id'],$studentSubject) de no existir la asignatura para
     * actualizarla se creará con el método self::addSubjects([$subject],$schoolPeriodStudentId,$isWithdrawn).
     * @param array $subjects Contenido parcial del objeto StudentSubject proviene de la petición
     * @param integer $schoolPeriodStudentId  Id el objeto SchoolPeriodStudent
     * @param string $organizationId Id de la organiación
     * @param boolean $isWithdrawn: Bandera para determinar si se retiró de la asignatura
     * @return integer Actualiza las asignaturas inscritas de un estudiante en periodo escolar y
     * actualiza la cantidad de estudiantes inscritos en dicha asignatura de fallar devolvera 0.
     */
    public static function updateSubjects($subjects,$schoolPeriodStudentId,$organizationId,$isWithdrawn)
    {
        $subjectsInBd=StudentSubject::studentSubjectBySchoolPeriodStudent($schoolPeriodStudentId);
        if (is_numeric($subjectsInBd)&&$subjectsInBd===0){
            return 0;
        }
        $subjectsUpdated=[];
        foreach ($subjects as $subject){
            $existSubject = false;
            foreach ($subjectsInBd as $subjectInBd){
                if ($subject['school_period_subject_teacher_id']==$subjectInBd['school_period_subject_teacher_id']){
                    $studentSubject = self::buildStudentSubject($subject,$schoolPeriodStudentId,$isWithdrawn);
                    $result = StudentSubject::updateStudentSubject($subjectInBd['id'],$studentSubject);
                    if (is_numeric($result)&&$result===0){
                        return 0;
                    }
                    $subjectsUpdated[]=$subjectInBd['id'];
                    $existSubject=true;
                    break;
                }
            }
            if ($existSubject==false){
                $result=self::addSubjects([$subject],$schoolPeriodStudentId,$isWithdrawn);
                if (is_numeric($result)&&$result===0){
                    return 0;
                }
                $schoolPeriodStudentIdAdd=StudentSubject::findStudentSubjectId($schoolPeriodStudentId,
                    $subject['school_period_subject_teacher_id']);
                if (is_numeric($schoolPeriodStudentIdAdd)&&$schoolPeriodStudentIdAdd){
                    return 0;
                }
                if (count($schoolPeriodStudentIdAdd)>0){
                    $subjectsUpdated[]=$schoolPeriodStudentIdAdd[0]['id'];
                }
            }
        }
        foreach ($subjectsInBd as $subjectId){
            if (!in_array($subjectId['id'],$subjectsUpdated)){
                $deleteStudentSubject=StudentSubject::deleteStudentSubject($subjectId['id']);
                if (is_numeric($deleteStudentSubject)&&$deleteStudentSubject===0){
                    return 0;
                }
            }
        }
        $result = self::updateStatusDESA($schoolPeriodStudentId,$organizationId);
        if (is_numeric($result)&&$result===0){
            return 0;
        }
    }

    /**
     * Actualiza la inscripción de un estudiante en un periodo escolar con el método
     * SchoolPeriodStudent::updateSchoolPeriodStudent($id,$request) y
     * self::updateSubjects($request['subjects'],$id,$organizationId,false), para inscribir o actualizar proyectos o
     * trabajo especial de grado usa el método
     * self::setProjectOrFinalWork($student[0],$request['projects'],$schoolPeriodStudentId, $organizationId,true) ,y
     * exámenes doctorales con el método
     * self::setDoctoralExam($student[0]['id'],$request['doctoral_exam'],$schoolPeriodStudentId,$organizationId).
     * @param Request $request Objeto con los datos de la petición
     * @param string $id Id del objeto schoolPeriodStudent que representa la inscripción
     * @param string $organizationId Id de la organiación
     * @return array|Response Devuelve la inscripcion actualizada, de fallar devolvera un objeto con un mensaje
     * indicando el error asociado
     */
    public static function updateInscription(Request $request, $id,$organizationId)
    {
        self::validate($request);
        if (isset($request['subjects'])&&count($request['subjects'])>0){
            self::validateSubjects($request);
        }
        if (isset($request['projects'])){
            self::validateProjects($request);
        }
        if (isset($request['final_works'])){
            self::validateInscriptionFinalWork($request);
        }
        if (isset($request['doctoral_exam'])){
            self::validateDoctoralExam($request);
        }
        if (isset($request['projects'])||isset($request['final_works'])||isset($request['subjects'])){
            $schoolPeriodStudentInBd= SchoolPeriodStudent::findSchoolPeriodStudent($request['student_id'],
                $request['school_period_id']);
            if (is_numeric($schoolPeriodStudentInBd)&&$schoolPeriodStudentInBd===0){
                return self::taskError(false,false);
            }
            if(count($schoolPeriodStudentInBd)>0 && $schoolPeriodStudentInBd[0]['id']==$id){
                $student=Student::getStudentById($request['student_id'],$organizationId);
                if (is_numeric($student)&&$student===0){
                    return self::taskError(false,false);
                }
                if (count($student)>0){
                    $request['status']=$student[0]['current_status'];
                    $request['test_period']=$student[0]['test_period'];
                    if($request['status']!='RET-A'&&$request['status']!='RET-B'&&$request['status']!='DES-A'&&
                        $request['status']!='DES-B'&&$request['status']!='ENDED'){
                        $availableSubjects = self::getAvailableSubjects($request['student_id'],
                            $request['school_period_id'], $organizationId, true);
                        if (is_numeric($availableSubjects)){
                            if ($availableSubjects===0){
                                return self::taskError(false,false);
                            }
                            if ($availableSubjects===1){
                                return response()->json(['message' => self::notAllowedRegister], 206);
                            }
                            if ($availableSubjects===2){
                                return response()->json(['message' => self::endProgram], 206);
                            }
                            $usersRol = array_column(auth()->payload()['user']->roles,'user_type');
                            if ($availableSubjects===4 && !in_array('A',$usersRol)){
                                return response()->json(['message'=>self::thereAreSchoolPeriodWithoutPaying],206);
                            }
                            if ($availableSubjects===5){
                                return response()->json(['message'=>self::notFoundStudentGivenId],206);
                            }
                        }
                        $result = SchoolPeriodStudent::updateSchoolPeriodStudent($id,$request);
                        if (is_numeric($result)&&$result===0){
                            return self::taskError(false,false);
                        }
                        if (isset($request['subjects'])&&count($request['subjects'])>0){
                            $validateRelationUpdate=self::validateRelationUpdate($request,$organizationId,
                                $availableSubjects);
                            if (is_numeric($validateRelationUpdate)&&$validateRelationUpdate===0){
                                return self::taskError(false,false);
                            }
                            if($validateRelationUpdate){
                                $result = self::updateSubjects($request['subjects'],$id,$organizationId,false);
                                if (is_numeric($result)&&$result===0){
                                    return self::taskError(false,true);
                                }
                            }else{
                                return response()->json(['message'=>self::invalidSubjectToInscription],206);
                            }
                        }else{
                            $result=StudentSubject::deleteStudentSubjectBySchoolPeriodStudentId($id);
                            if (is_numeric($result)&&$result===0){
                                return self::taskError(false,true);
                            }
                        }
                        if (isset($request['projects'])){
                            $projectSubjects = [];
                            if (!is_numeric($availableSubjects) && isset($availableSubjects['project_subjects'])){
                                if (!is_array($availableSubjects['project_subjects'])){
                                    $projectSubjects = $availableSubjects['project_subjects']->toArray();
                                }else{
                                    $projectSubjects = $availableSubjects['project_subjects'];
                                }
                            }
                            $enrolledProjects = FinalWork::getFinalWorksByStudent($request['student_id'],true);
                            if (is_numeric($enrolledProjects)&&$enrolledProjects===0){
                                return self::taskError(false,true);
                            }
                            foreach ($enrolledProjects->toArray() as $project){
                                if ($project['approval_date']!=null){
                                    $aux = new \stdClass();
                                    $aux->id = $project['subject_id'];
                                    $projectSubjects[]=[$aux];
                                }
                            }
                            if((isset($availableSubjects['available_project'])&&$availableSubjects['available_project'])
                                || count($projectSubjects)>0 ){
                                $result =self::setProjectsOrFinalWorks($student[0]['id'],$request['projects'],
                                    $id, true, $projectSubjects,$organizationId);
                            }
                        }else{
                            $result = self::deleteFinalWorkSchoolPeriodNotUpdate([],$id);
                            if (is_numeric($result)&&$result===0){
                                return self::taskError(false,true);
                            }
                        }
                        if (isset($request['final_works'])){
                            $finalWorkSubjects = [];
                            if (!is_numeric($availableSubjects) && isset($availableSubjects['final_work_subjects'])){
                                if (!is_array($availableSubjects['final_work_subjects'])){
                                    $finalWorkSubjects = $availableSubjects['final_work_subjects']->toArray();
                                }else{
                                    $finalWorkSubjects = $availableSubjects['final_work_subjects'];
                                }
                            }
                            $enrolledFinalWorks = FinalWork::getFinalWorksByStudent($request['student_id'],false);
                            if (is_numeric($finalWorkSubjects)&&$finalWorkSubjects===0){
                                return self::taskError(false,true);
                            }
                            foreach ($enrolledFinalWorks->toArray() as $finalWork){
                                if ($finalWork['approval_date']!=null){
                                    $aux = new \stdClass();
                                    $aux->id = $finalWork['subject_id'];
                                    $finalWorkSubjects[]=[$aux];
                                }
                            }
                            if ((isset($availableSubjects['available_final_work'])&&
                                $availableSubjects['available_final_work']) || count($finalWorkSubjects)>0){
                                $result = self::setProjectsOrFinalWorks($student[0]['id'],$request['final_works'],
                                    $id,false, $finalWorkSubjects,$organizationId);
                            }
                        }
                        if ((isset($availableSubjects['available_project']) && ($availableSubjects['available_project'])
                                && !isset($request['projects']) && (isset($availableSubjects['available_final_work']) &&
                                    $availableSubjects['available_final_work']) && !isset($request['final_works']))){
                            $result=FinalWorkSchoolPeriod::existFinalWorkSchoolPeriodBySchoolPeriodStudent($id);
                            if (is_numeric($result)&&$result===0){
                                return self::taskError(false,true);
                            }
                            $result=FinalWorkSchoolPeriod::deleteFinalWorkSchoolPeriodBySchoolPeriodStudentId($id);
                        }
                        if (is_numeric($result)&&$result===0){
                            return self::taskError(false,true);
                        }
                        if ((isset($availableSubjects['available_doctoral_exam'])&&
                                $availableSubjects['available_doctoral_exam']) && isset($request['doctoral_exam'])){
                            $result =self::setDoctoralExam($student[0]['id'],$request['doctoral_exam'], $id,
                                $organizationId);
                        }else{
                            $result= DoctoralExam::deleteDoctoralExam($id);
                        }
                        if (is_numeric($result)&&$result===0){
                            return self::taskError(false,true);
                        }
                        $log = Log::addLog(auth('api')->user()['id'],
                            self::logUpdateInscription.$request['student_id']);
                        if (is_numeric($log)&&$log==0){
                            return self::taskError(false,true);
                        }
                        return self::getInscriptionById($id,$organizationId);
                    }
                    return response()->json(['message' => self::notAllowedRegister], 206);
                }
                return response()->json(['message'=>self::notFoundStudentGivenId],206);
            }
            return response()->json(['message'=>self::notFoundInscription],206);
        }
        return response()->json(['message' => self::notRegister], 206);
    }

    /**
     * Asigna el periodo escolar actual para consultar las asignaturas disponibles de un estudiante usando el método
     * self::getAvailableSubjects($studentId,$currentSchoolPeriod[0]['id'],$organizationId,false).
     * @param integer $studentId Id del estudiante
     * @param string $organizationId Id de la organiación
     * @return Response|array Devolvera las asignatuiras disponibles para inscribir, de no existir asignaturas
     * disponibles o fallar devolvera un json con un mensaje asociado.
     */
    public static function studentAvailableSubjects($studentId,$organizationId)
    {
        $isValid=StudentService::validateStudent($organizationId,$studentId);
        if ($isValid==='valid'){
            $student = Student::getStudentById($studentId,$organizationId);
            if (is_numeric($student)&&$student===0){
                return self::taskError(false,false);
            }
            $currentSchoolPeriod= SchoolPeriod::getCurrentSchoolPeriod($organizationId);
            if(is_numeric($currentSchoolPeriod)&&$currentSchoolPeriod===0){
                return self::taskError(false,false);
            }
            if (count($currentSchoolPeriod)>0 && count($student)>0){
                if ($currentSchoolPeriod[0]['inscription_visible'] || $student[0]['allow_post_inscription']){
                    return self::getAvailableSubjects($studentId,$currentSchoolPeriod[0]['id'],$organizationId,false);
                }
                return response()->json(['message'=>self::notAvailableInscriptions],206);
            }
            return response()->json(['message'=>self::noCurrentSchoolPeriod],206);
        }
        return $isValid;
    }

    /**
     * Crea la inscripción de un estudiante, seteando el periodo escolar actual como el id a utilizar con el método
     * self::addInscription($request,$organizationId).
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @return array|Response Devuelve la inscripcion realizada, de fallar devolvera un objeto con un mensaje indicando
     * el error asociado
     */
    public static function studentAddInscription(Request $request,$organizationId)
    {
        $isValid=StudentService::validateStudent($organizationId,$request['student_id']);
        if ($isValid=='valid'){
            $currentSchoolPeriod= SchoolPeriod::getCurrentSchoolPeriod($organizationId);
            if (is_numeric($currentSchoolPeriod)&&$currentSchoolPeriod===0){
                 return self::taskError(false,false);
            }
            $student = Student::getStudentById($request['student_id'],$organizationId);
            if (is_numeric($student)&&$student===0){
                return self::taskError(false,false);
            }
            if (count($currentSchoolPeriod)>0 && count($student)>0){
                if ($currentSchoolPeriod[0]['inscription_visible'] || $student[0]['allow_post_inscription']){
                    $request['school_period_id']=$currentSchoolPeriod[0]['id'];
                    unset($request['pay_ref']);
                    unset($request['amount_paid']);
                    if (isset($request['subjects'])){
                        $subjects = $request['subjects'];
                        for ($i=0;count($request['subjects'])>$i;$i++){
                            $subject = $subjects[$i];
                            unset($subject['qualification']);
                            unset($subject['status']);
                            $subjects[$i]=$subject;
                        }
                        $request['subjects']=$subjects;
                    }
                    if (isset($request['projects'])){
                        $projects =$request['projects'];
                        for ($i=0;$i<count($projects);$i++){
                            $project = $projects[$i];
                            $project['status']='PROGRESS';
                            unset($project['description_status']);
                            unset($project['approval_date']);
                            $projects[$i]=$project;
                        }
                        $request['projects']=$projects;
                    }
                    if (isset($request['final_works'])){
                        $finalWorks= $request['final_works'];
                        for ($i=0;$i<count($finalWorks);$i++){
                            $finalWork = $finalWorks[$i];
                            $finalWork['status']='PROGRESS';
                            unset($finalWork['description_status']);
                            unset($finalWork['approval_date']);
                            $finalWorks[$i] = $finalWork;
                        }
                        $request['final_works']=$finalWorks;
                    }
                    unset($request['doctoral_exam']);
                    return self::addInscription($request,$organizationId);
                }
                return response()->json(['message'=>self::notAvailableInscriptions],206);
            }
            return response()->json(['message'=>self::noCurrentSchoolPeriod],206);
        }
        return $isValid;
    }

    /**
     * Obtiene las asignaturas inscritas de un estudiante en el periodo escolar actual con el método
     * SchoolPeriodStudent::findSchoolPeriodStudent($studentId,$currentSchoolPeriod[0]['id']).
     * @param integer $studentId Id del estudiante
     * @param string $organizationId Id de la organiación
     * @return Response|array Devolvera las asignatuiras inscritas por el estudiante, de no existir asignaturas
     * disponibles o fallar devolvera un json con un mensaje asociado.
     */
    public static function getCurrentEnrolledSubjects($studentId,$organizationId){
        $isValid=StudentService::validateStudent($organizationId,$studentId);
        if ($isValid=='valid'){
            $currentSchoolPeriod= SchoolPeriod::getCurrentSchoolPeriod($organizationId);
            if (is_numeric($currentSchoolPeriod)&&$currentSchoolPeriod===0){
                return response()->json(['message'=>self::taskError],500);
            }
            if (count($currentSchoolPeriod)>0){
                $inscription = SchoolPeriodStudent::findSchoolPeriodStudent($studentId,$currentSchoolPeriod[0]['id']);
                if (is_numeric($inscription)&&$inscription===0){
                    return self::taskError(false,false);
                }
                if (count($inscription)>0){
                    $finalWork = FinalWork::getFinalWorksByStudentAndSchoolPeriod($studentId,false,
                        $inscription[0]['id']);
                    $project = FinalWork::getFinalWorksByStudentAndSchoolPeriod($studentId,true,
                        $inscription[0]['id']);
                    if (is_numeric($finalWork)&&$finalWork===0 || is_numeric($project)&&$project===0 ){
                        return self::taskError(false,false);
                    }
                    if (count($finalWork)>0){
                        $inscription[0]['final_work']=$finalWork;
                    }
                    if (count($project)>0){
                        $inscription[0]['project']=$project;
                    }
                    return $inscription[0];
                }
                return response()->json(['message'=>self::emptyInscriptions],206);
            }
            return response()->json(['message'=>self::noCurrentSchoolPeriod],206);
        }
        return $isValid;
    }

    /**
     *Valida que se cumpla las restricciones:
     * *student_id:requerido y numérico
     * *withdraw_subjects.*.student_subject_id: requerido y numérico
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validateWithdrawSubjectsBody(Request $request)
    {
        $request->validate([
            'student_id'=>'required|numeric',
            'withdraw_subjects.*.student_subject_id'=>'required|numeric'
        ]);
    }

    /**
     * Valida que las asignaturas que quieren retirar estén inscritas si todas están inscritas, devolverá true, de lo
     * contrario será false.
     * @param array $withdrawSubjects Arreglo con ids de asignaturas a retirar
     * @param array $enrolledSubjects Asignaturas inscritas en el semestre
     * @return boolean devuelve true si las asignaturas que solicitas retirar son las que el estudiante inscribio de lo
     * contrario sera false
     */
    public static function validateWithdrawSubjects($withdrawSubjects,$enrolledSubjects)
    {
        $enrolledSubjectsId = array_column($enrolledSubjects->toArray(),'id');
        foreach ($withdrawSubjects as $withdrawSubject){
            if (!in_array($withdrawSubject['student_subject_id'],$enrolledSubjectsId)){
                return false;
            }
            return true;
        }
    }

    /**
     * Cambia el estatus a retirado de las asignaturas que se solicitan retirar del semestre con el método
     * StudentSubject::updateStudentSubject($withdrawSubject['student_subject_id'],$studentSubject->toArray()).
     * @param integer $schoolPeriodStudentId  Id el objeto SchoolPeriodStudent
     * @param string $organizationId Id de la organiación
     * @param array $withdrawSubjects: Arreglo con ids de asignaturas a retirar
     * @return integer cambia a retirado dichas asignaturas retiradas de fallar devolvera 0.
     */
    public static function changeStatusSubjectsToRET($schoolPeriodStudentId,$organizationId,$withdrawSubjects)
    {
        foreach ($withdrawSubjects as $withdrawSubject){
            $studentSubject = StudentSubject::getStudentSubjectById($withdrawSubject['student_subject_id']);
            if (is_numeric($studentSubject)&&$studentSubject===0){
                return 0;
            }
            $studentSubject = $studentSubject[0];
            $studentSubject['status']='RET';
            $studentSubject=StudentSubject::updateStudentSubject($withdrawSubject['student_subject_id'],
                $studentSubject->toArray());
            if (is_numeric($studentSubject)&&$studentSubject===0){
                return 0;
            }
        }
        $result=self::updateStatusDESA($schoolPeriodStudentId,$organizationId);
        if (is_numeric($result)&&$result==0){
            return 0;
        }
    }

    /**
     * Retira las asignaturas inscritas en el periodo escolar con el método
     * self::changeStatusSubjectsToRET($inscription[0]['id'],$organizationId,$request['withdrawSubjects']).
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @return Response Devuelve un mensaje Ok de realizar un retiro en la inscripcion, de fallar devolvera un objeto
     * con un mensaje indicando el error asociado
     */
    public static function withdrawSubjects(Request $request,$organizationId)
    {
        self::validateWithdrawSubjectsBody($request);
        $isValid=StudentService::validateStudent($organizationId,$request['student_id']);
        if ($isValid=='valid'){
            $currentSchoolPeriod= SchoolPeriod::getCurrentSchoolPeriod($organizationId);
            if (is_numeric($currentSchoolPeriod)&&$currentSchoolPeriod==0){
                return self::taskError(false,false);
            }
            if (count($currentSchoolPeriod)>0){
                if (strtotime($currentSchoolPeriod[0]['withdrawal_deadline'])>=strtotime(now()->toDateTimeString())){
                    $inscription = SchoolPeriodStudent::findSchoolPeriodStudent($request['student_id'],
                        $currentSchoolPeriod[0]['id']);
                    if (is_numeric($inscription)&&$inscription===0){
                        return self::taskError(false,false);;
                    }
                    if (count($inscription)>0){
                        if (self::validateWithdrawSubjects($request['withdraw_subjects'],
                            $inscription[0]['enrolledSubjects'])){
                            $result =self::changeStatusSubjectsToRET($inscription[0]['id'],$organizationId,
                                $request['withdraw_subjects']);
                            if (is_numeric($result)&&$result==0){
                                return self::taskError(false,false);
                            }
                            $log = Log::addLog(auth('api')->user()['id'],
                                self::logWithdrawSubject.$request['student_id']);
                            if (is_numeric($log)&&$log==0){
                                return self::taskError(false,true);
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

    /**
     * Lista los estudiantes que tiene un profesor en una asignatura dictada en un periodo escolar con el método
     * StudentSubject::studentSubjectBySchoolPeriodSubjectTeacherId( $schoolPeriodSubjectTeacherId).
     * @param integer $teacherId Id del profesor
     * @param integer $schoolPeriodSubjectTeacherId Id de la asignatura que dicta un profesor en determinado periodo
     * escolar
     * @param string $organizationId Id de la organiación
     * @return StudentSubject|Response Devuelve los estudiantes inscritos en una asignatura que imparte un profesor en
     * el periodo escolar actual, de fallar devolvera un objeto con un mensaje indicando el error asociado
     */
    public static function getEnrolledStudentsInSchoolPeriod($teacherId,$schoolPeriodSubjectTeacherId,$organizationId)
    {
        $isValid=TeacherService::validateTeacher($teacherId,$organizationId);
        if ($isValid=='valid'){
            $currentSchoolPeriod= SchoolPeriod::getCurrentSchoolPeriod($organizationId);
            if (is_numeric($currentSchoolPeriod)&&$currentSchoolPeriod==0){
                return self::taskError(false,false);
            }
            if (count($currentSchoolPeriod)>0){
                $schoolPeriodSubjectTeacher= SchoolPeriodSubjectTeacher::getSchoolPeriodSubjectTeacherById(
                    $schoolPeriodSubjectTeacherId);
                if (is_numeric($schoolPeriodSubjectTeacher)&&$schoolPeriodSubjectTeacher===0){
                    return self::taskError(false,false);
                }
                if ($schoolPeriodSubjectTeacher[0]['teacher_id']==$teacherId &&
                    $schoolPeriodSubjectTeacher[0]['school_period_id']==$currentSchoolPeriod[0]['id'] ){
                    $enrolledStudents=StudentSubject::studentSubjectBySchoolPeriodSubjectTeacherId(
                        $schoolPeriodSubjectTeacherId);
                    if (is_numeric($enrolledStudents)&&$enrolledStudents===0){
                        return self::taskError(false,false);
                    }
                    if (count($enrolledStudents)>0){
                        return $enrolledStudents;
                    }
                    return response()->json(['message'=>self::emptyInscriptions],206);
                }
                return response()->json(['message'=>self::invalidSubject],206);
            }
            return response()->json(['message'=>self::noCurrentSchoolPeriod],206);
        }
        return $isValid;
    }

    /**
     *Valida que se cumpla las restricciones:
     * *teacher_id requerido y numérico
     * *school_period_subject_teacher_id requerido y numérico
     * *student_notes.*.student_subject_id:  requerido y numérico
     * *student_notes.*.qualification:  requerido y numérico
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validateLoadNotes(Request $request)
    {
        $request->validate([
            'teacher_id'=>'required|numeric',
            'school_period_subject_teacher_id'=>'required|numeric',
            'student_notes.*.student_subject_id'=>'required|numeric',
            'student_notes.*.qualification'=>'required|numeric'
        ]);
    }

    /**
     * Valida que las notas se mantengan en un rango de 0 a 20, que la asignatura que se trata de colocar la nota no
     * esté retirada de cumplir estos criterios devolverá true de lo contrario será false.
     * @param Request $request Objeto con los datos de la petición
     * @param integer $schoolPeriodId Id del periodo escolar
     * @return boolean|integer Devuelve un booleano en caso de los datos ser correctos devolvera true, de lo contrario
     * sera false, de fallar devolvera 0.
     */
    public static function validateNotes(Request $request, $schoolPeriodId)
    {
        $teacherId=$request['teacher_id'];
        $schoolPeriodSubjectTeacherId=$request['school_period_subject_teacher_id'];
        $studentNotes=$request['student_notes'];
        $schoolPeriodSubjectTeacher = SchoolPeriodSubjectTeacher::getSchoolPeriodSubjectTeacherById(
            $schoolPeriodSubjectTeacherId);
        if(is_numeric($schoolPeriodSubjectTeacher)&&$schoolPeriodSubjectTeacher==0){
            return 0;
        }
        if (count($schoolPeriodSubjectTeacher)<1){
            return false;
        }
        if ($schoolPeriodSubjectTeacher[0]['teacher_id']!=$teacherId ||
            $schoolPeriodSubjectTeacher[0]['school_period_id']!=$schoolPeriodId){
            return false;
        }
        $enrolledStudents=StudentSubject::studentSubjectBySchoolPeriodSubjectTeacherId($schoolPeriodSubjectTeacherId);
        if (is_numeric($enrolledStudents)&&$enrolledStudents===0){
            return 0;
        }
        if (count($enrolledStudents)<1){
            return false;
        }
        foreach ($studentNotes as $studentNote){
            $found = false;
            foreach ($enrolledStudents as $enrolledStudent){
                if($enrolledStudent['id']==$studentNote['student_subject_id'] && $enrolledStudent['status']!='RET' &&
                    $studentNote['qualification']>=0 && $studentNote['qualification']<=20){
                    $found = true;
                    break;
                }
            }
            if ($found == false){
                return false;
            }
        }
        return true;
    }

    /**
     * Actualiza la nota de un estudiante en una asignatura inscrita en el semestre con el método
     * StudentSubject::updateStudentSubject($studentSubject[0]['id'],$studentSubjectPrepare).
     * @param array $studentNotes Inscripciones de los estudiantes en una asignatura
     * @return integer Actualiza las notas de los estudiantes en una asignatura, de fallar devolvera 0.
     */
    public static function changeNotes($studentNotes){
        foreach($studentNotes as $studentNote){
            $studentSubject= StudentSubject::getStudentSubjectById($studentNote['student_subject_id']);
            if (is_numeric($studentSubject)&&$studentSubject===0){
                return 0;
            }
            $studentSubject[0]['qualification']=$studentNote['qualification'];
            $studentSubjectPrepare = self::buildStudentSubject($studentSubject[0],
                $studentSubject[0]['school_period_student_id'],false);
            $result=StudentSubject::updateStudentSubject($studentSubject[0]['id'],$studentSubjectPrepare);
            if (is_numeric($result)&&$result===0){
                return 0;
            }
        }
    }

    /**
     * Carga la nota de los estudiantes con el método self::changeNotes($request['student_notes']).
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @return Response Realiza la carga de notas de fallar devolvera 0.
     */
    public static function loadNotes(Request $request,$organizationId)
    {
        self::validateLoadNotes($request);
        $isValid=TeacherService::validateTeacher($request['teacher_id'],$organizationId);
        if ($isValid=='valid'){
            $currentSchoolPeriod= SchoolPeriod::getCurrentSchoolPeriod($organizationId);
            if(is_numeric($currentSchoolPeriod)&&$currentSchoolPeriod===0){
                return self::taskError(false,false);
            }
            if (count($currentSchoolPeriod)>0){
                $validNotes = self::validateNotes($request,$currentSchoolPeriod[0]['id']);
                if (is_numeric($validNotes)&&$validNotes===0){
                    return self::taskError(false,false);
                }
                if ($validNotes && $currentSchoolPeriod[0]['load_notes']==true){
                    $schoolPeriodsStudentForUpdate=self::changeNotes($request['student_notes']);
                    if (is_numeric($schoolPeriodsStudentForUpdate)&&$schoolPeriodsStudentForUpdate===0){
                        return self::taskError(false,false);
                    }
                    $log = Log::addLog(auth('api')->user()['id'],
                        self::logLoadNotes);
                    if (is_numeric($log)&&$log==0){
                        return self::taskError(false,true);
                    }
                    return response()->json(['message'=>self::OK],200);
                }
                return response()->json(['message'=>self::invalidData],206);
            }
            return response()->json(['message'=>self::noCurrentSchoolPeriod],206);
        }
        return $isValid;
    }

    /**
     * Elimina un proyecto o trabajo final dado su id con el método FinalWork::deleteFinalWork($id).
     * @param string $id Id del FinalWork
     * @return Response|string, De realizarse de manera exitosa sera OK de ocurrir un error devolvera un mensaje
     * asociado.
     */
    public static function deleteFinalWork($id){
        $finalWork = FinalWork::getFinalWork($id);
        if (is_numeric($finalWork)&&$finalWork===0){
            return self::taskError(false,false);
        }
        if (count($finalWork)>0){
            $result = FinalWork::deleteFinalWork($id);
            if (is_numeric($result)&&$result===0){
                return self::taskError;
            }
            $log = Log::addLog(auth('api')->user()['id'], self::logDeleteFinalWork.$finalWork[0]['student_id']);
            if (is_numeric($log)&&$log==0){
                return self::taskError(false,true);
            }
            return response()->json(['message'=>self::OK]);
        }
        return response()->json(['message'=>self::notFoundFinalWork]);
    }
}
