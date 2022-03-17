<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SchoolPeriodService;

/**
 * @package : Controller
 * @author : Hector Alayon
 * @version : 1.0
 */
class SchoolPeriodController extends Controller
{
    /**
     * Obtiene todos los periodos escolares de una organización usa el método
     * SchoolPeriodService::getSchoolPeriods($organizationId) o
     * SchoolPeriodService::getSchoolPeriods($organizationId,$perPage) si usa paginación.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $perPage = $request->input('per_page');
        return $perPage ? SchoolPeriodService::getSchoolPeriods($organizationId,$perPage) :
            SchoolPeriodService::getSchoolPeriods($organizationId);
    }

    /**
     * Agrega un periodo escolar, usa el método SchoolPeriodService::addSchoolPeriod($request,$organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return SchoolPeriodService::addSchoolPeriod($request,$organizationId);
    }

    /**
     * Devuelve los datos de un periodo escolar dado su id, usa el método
     * SchoolPeriodService::getSchoolPeriodById($id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show($id,Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return SchoolPeriodService::getSchoolPeriodById($id,$organizationId);
    }

    /**
     * Actualiza los datos de un periodo escolar usando el método
     * SchoolPeriodService::updateSchoolPeriod($request,$id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $organizationId = $request->header('Organization-Key');
        return SchoolPeriodService::updateSchoolPeriod($request,$id,$organizationId);
    }

    /**
     * Elimina un periodo escolar dado su id usando el método
     * SchoolPeriodService::deleteSchoolPeriod($id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $organizationId = $request->header('Organization-Key');
        return SchoolPeriodService::deleteSchoolPeriod($id,$organizationId);
    }

    /**
     * Devuelve el periodo escolar actual usando el método SchoolPeriodService::getCurrentSchoolPeriod($organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function current(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return SchoolPeriodService::getCurrentSchoolPeriod($organizationId);
    }

    /**
     * Obtiene las asignaturas que dicta un profesor usando el método
     * SchoolPeriodService::getSubjectsTaughtSchoolPeriod($teacherId,$organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function subjectTaughtSchoolPeriod(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $teacherId=$request->input('teacher_id');
        return SchoolPeriodService::getSubjectsTaughtSchoolPeriod($teacherId,$organizationId);
    }
}
