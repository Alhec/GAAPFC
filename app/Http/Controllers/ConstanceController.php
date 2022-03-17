<?php

namespace App\Http\Controllers;

use App\Services\ConstanceService;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Integer;

/**
 * @package : Controller
 * @author : Hector Alayon
 * @version : 1.0
 */
class ConstanceController extends Controller
{
    /**
     * Genera una constancia de estudios o devuelve la data necesaria para generar una con el método
     * ConstanceService::constanceOfStudy($request,$studentId,$organizationId,$data).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|array|\PDF|object
     */
    public function constanceOfStudy(Request $request)
    {
        $studentId = $request->input('student_id');
        $organizationId = $request->header('Organization-Key');
        $data = $request->input('data');
        return ConstanceService::constanceOfStudy($studentId,$organizationId,$data);
    }

    /**
     * Genera una constancia de inscripción de un estudiante o devuelve la data necesaria para generarla usando el
     * método ConstanceService::inscriptionConstance($request,$studentId,$inscriptionId,$organizationId,$data).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|array|\PDF|object
     */
    public function inscriptionConstance(Request $request)
    {
        $studentId = $request->input('student_id');
        $inscriptionId = $request->input('inscription_id');
        $organizationId = $request->header('Organization-Key');
        $data = $request->input('data');
        return ConstanceService::inscriptionConstance($studentId,$inscriptionId,$organizationId,$data);
    }

    /**
     * Genera una constancia de trabajo o devuelve la data necesaria para generar una con el método
     * ConstanceService::constanceOfWorkTeacher($teacherId,$organizationId,$data).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|array|\PDF|object
     */
    public function constanceOfWorkTeacher(Request $request)
    {
        $teacherId = $request->input('teacher_id');
        $organizationId = $request->header('Organization-Key');
        $data = $request->input('data');
        return ConstanceService::constanceOfWorkTeacher($teacherId,$organizationId,$data);
    }

    /**
     * Genera una constancia de trabajo o devuelve la data necesaria para generar una con el método
     * ConstanceService::constanceOfWorkAdministrator($administratorId,$organizationId,$data)
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|array|\PDF|object
     */
    public function constanceOfWorkAdministrator(Request $request)
    {
        $administratorId = $request->input('administrator_id');
        $organizationId = $request->header('Organization-Key');
        $data = $request->input('data');
        return ConstanceService::constanceOfWorkAdministrator($administratorId,$organizationId,$data);
    }

    /**
     * Genera una constancia de carga académica o devuelve la data necesaria para generar una con el método
     * ConstanceService::academicLoad($studentId,$organizationId,$data).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|array|\PDF|object
     */
    public function academicLoad(Request $request)
    {
        $studentId = $request->input('student_id');
        $organizationId = $request->header('Organization-Key');
        $data = $request->input('data');
        return ConstanceService::academicLoad($studentId,$organizationId,$data);
    }

    /**
     * Genera una constancia de histórica o devuelve la data necesaria para generar una con el método
     * ConstanceService::studentHistorical($studentId,$organizationId,$data).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|array|\PDF|object
     */
    public function studentHistorical(Request $request)
    {
        $studentId = $request->input('student_id');
        $organizationId = $request->header('Organization-Key');
        $data = $request->input('data');
        return ConstanceService::studentHistorical($studentId,$organizationId,$data);
    }

}
