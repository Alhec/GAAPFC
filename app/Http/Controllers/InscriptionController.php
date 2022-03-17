<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InscriptionService;

/**
 * @package : Controller
 * @author : Hector Alayon
 * @version : 1.0
 */
class InscriptionController extends Controller
{
    /**
     * Obtiene todas las inscripciones de una organización usa el método
     * InscriptionService::getInscriptions($organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return InscriptionService::getInscriptions($organizationId);
    }

    /**
     * Crea una inscripción, usa el método InscriptionService::addInscription($request,$organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return InscriptionService::addInscription($request,$organizationId);
    }

    /**
     * Devuelve los datos de una inscripción dado su id, usa el método
     * InscriptionService::getInscriptionById($id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $organizationId = $request->header('Organization-Key');
        return InscriptionService::getInscriptionById($id,$organizationId);
    }

    /**
     * Actualiza los datos de una inscripción usando el método
     * InscriptionService::updateInscription($request,$id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $organizationId = $request->header('Organization-Key');
        return InscriptionService::updateInscription($request,$id,$organizationId);
    }

    /**
     * Elimina una inscripción dado su id usando el método InscriptionService::deleteInscription($id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return InscriptionService::deleteInscription($id,$organizationId);
    }

    /**
     * Obtiene las asignaturas que tiene disponible un usuario y tambien si esta habilitado inscribir proyecto o trabajo
     * final usando el método InscriptionService::getAvailableSubjects($studentId,$schoolPeriodId,$organizationId,false).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function availableSubjects(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $studentId = $request->input('student_id');
        $schoolPeriodId = $request->input('school_period_id');
        return InscriptionService::getAvailableSubjects($studentId,$schoolPeriodId,$organizationId,false);
    }

    /**
     * Obtiene todas las inscripciones dado un periodo escolar usando el método
     * InscriptionService::getInscriptionsBySchoolPeriod($schoolPeriodId,$organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @param  integer  $schoolPeriodId
     * @return \Illuminate\Http\Response
     */
    public function inscriptionBySchoolPeriod(Request $request,$schoolPeriodId)
    {
        $organizationId = $request->header('Organization-Key');
        return InscriptionService::getInscriptionsBySchoolPeriod($schoolPeriodId,$organizationId);
    }

    /**
     * Obtiene las asignaturas disponibles de un estudiante aplicando las restricciones para los estudiantes, y se
     * realiza en el periodo escolar actual usando el método
     * InscriptionService::studentAvailableSubjects($studentId,$organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function studentAvailableSubjects(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $studentId = $request->input('student_id');
        return InscriptionService::studentAvailableSubjects($studentId,$organizationId);
    }

    /**
     * Inscribe las asignaturas desde las perspectivas del estudiante con las restricciones correspondientes en el
     * periodo escolar actual usando el método InscriptionService::studentAddInscription($request,$organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public  function addStudentInscription(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return InscriptionService::studentAddInscription($request,$organizationId);
    }

    /**
     * Obtiene las asignaturas inscritas en el periodo escolar actual de un estudiante usando el método
     * InscriptionService::getCurrentEnrolledSubjects($studentId,$organizationId)
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function currentEnrolledSubjects(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $studentId = $request->input('student_id');
        return InscriptionService::getCurrentEnrolledSubjects($studentId,$organizationId);
    }

    /**
     * Realiza un retiro de asignatura del periodo escolar actual usando el método
     * InscriptionService::withdrawSubjects($request,$organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function withdrawSubjects(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return InscriptionService::withdrawSubjects($request,$organizationId);
    }

    /**
     * Obtiene los estudiantes inscritos en una asignatura específica en el periodo escolar actual usando el método
     * InscriptionService::getEnrolledStudentsInSchoolPeriod($teacherId,$schoolPeriodSubjectTeacherId,$organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function enrolledStudentsInSchoolPeriod(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $teacherId = $request->input('teacher_id');
        $schoolPeriodSubjectTeacherId = $request->input('school_period_subject_teacher_id');
        return InscriptionService::getEnrolledStudentsInSchoolPeriod($teacherId,$schoolPeriodSubjectTeacherId,
            $organizationId);
    }

    /**
     * Carga las notas de una asignatura asociada al periodo escolar actual con el método
     * InscriptionService::loadNotes($request,$organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function loadNotes(Request $request){
        $organizationId = $request->header('Organization-Key');
        return InscriptionService::loadNotes($request,$organizationId);
    }

    /**
     * Elimina un proyecto o trabajo final usando el método InscriptionService::deleteFinalWork($id).
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    public static function deleteFinalWork($id){
        return InscriptionService::deleteFinalWork($id);
    }

}
