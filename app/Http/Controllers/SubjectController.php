<?php

namespace App\Http\Controllers;

use App\Services\SubjectService;
use Illuminate\Http\Request;

/**
 * @package : Controller
 * @author : Hector Alayon
 * @version : 1.0
 */
class SubjectController extends Controller
{
    /**
     * Obtiene todos las asignaturas asociadas a un programa escolar de una organización usa el método
     * SubjectService::getSubjects($organizationId) o SubjectService::getSubjects($organizationId,$perPage) si usa
     * paginación.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $perPage = $request->input('per_page');
        return $perPage ? SubjectService::getSubjects($organizationId,$perPage) :
            SubjectService::getSubjects($organizationId);
    }

    /**
     * Agrega una asignatura y la asocia a un programa escolar de una organización, usa el método
     * SubjectService::addSubject($request,$organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return SubjectService::addSubject($request,$organizationId);
    }

    /**
     * Devuelve los datos de una asignatura dado su id, usa el método
     * SubjectService::getSubjectsById($id,$organizationId)
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return SubjectService::getSubjectById($id,$organizationId);
    }

    /**
     * Actualiza los datos de una asignatura usando el método
     * SubjectService::updateSubject($request,$id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($id,Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return SubjectService::updateSubject($request,$id,$organizationId);
    }

    /**
     * Elimina una asignatura dada su id usando el método SubjectService::deleteSubject($id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,Request $request)
    {
       $organizationId = $request->header('Organization-Key');
       return SubjectService::deleteSubject($id,$organizationId);
    }

    /**
     * Devuelve las asignaturas asociadas al id del programa escolar usando el método
     * SubjectService::getSubjectsBySchoolProgramId($id,$organizationId) o
     * SubjectService::getSubjectsWithoutFinalWorks($organizationId,$perPage) si usa paginación.
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getBySchoolProgram($id,Request $request){
        $organizationId = $request->header('Organization-Key');
        $perPage = $request->input('per_page');
        return $perPage ? SubjectService::getSubjectsBySchoolProgramId($id,$organizationId,$perPage) :
            SubjectService::getSubjectsBySchoolProgramId($id,$organizationId);
    }

    /**
     * Obtiene todos las asignaturas asociadas a un programa escolar sin asignaturas finales ni proyectos de una
     * organización usa el método SubjectService::getSubjectsWithoutFinalWorks($organizationId) o
     * SubjectService::getSubjectsBySchoolProgramId($id,$organizationId,$perPage) si usa paginación.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSubjectsWithoutFinalWorks(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $perPage = $request->input('per_page');
        return $perPage ? SubjectService::getSubjectsWithoutFinalWorks($organizationId,$perPage) :
            SubjectService::getSubjectsWithoutFinalWorks($organizationId);
    }
}
