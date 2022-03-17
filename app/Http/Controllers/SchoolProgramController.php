<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SchoolProgramService;

/**
 * @package : Controller
 * @author : Hector Alayon
 * @version : 1.0
 */
class SchoolProgramController extends Controller
{
    /**
     * Obtiene todos los programas escolares de una organización usa el método
     * SchoolProgramService::getSchoolProgram($organizationId) o
     * SchoolProgramService::getSchoolProgram($organizationId,$perPage) si usa paginación.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $perPage = $request->input('per_page');
        return  $perPage ? SchoolProgramService::getSchoolProgram($organizationId,$perPage) :
            SchoolProgramService::getSchoolProgram($organizationId);
    }

    /**
     * Agrega un programa escolar a una organización, usa el método
     * SchoolProgramService::addSchoolProgram($request,$organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return SchoolProgramService::addSchoolProgram($request,$organizationId);
    }

    /**
     * Devuelve los datos de un programa escolar dado un id, usa el método
     * SchoolProgramService::getSchoolProgramById( $id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return SchoolProgramService::getSchoolProgramById($id,$organizationId);
    }

    /**
     * Actualiza los datos de un programa escolar usando el método
     * SchoolProgramService::updateSchoolProgram($request,$id,$organizationId)
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($id,Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return SchoolProgramService::updateSchoolProgram($request,$id,$organizationId);
    }

    /**
     * Elimina un programa escolar dado su id usando el método
     * SchoolProgramService::deleteSchoolProgram($id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return SchoolProgramService::deleteSchoolProgram($id,$organizationId);
    }
}
