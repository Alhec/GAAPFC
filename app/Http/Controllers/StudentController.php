<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\StudentService;

/**
 * @package : Controller
 * @author : Hector Alayon
 * @version : 1.0
 */
class StudentController extends Controller
{
    /**
     * Obtiene todos los estudiantes de una organización usa el método UserService::getUsers('S',$organizationId) o
     * UserService::getUsers('S',$organizationId,$perPage) si usa paginación.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $perPage = $request->input('per_page');
        return $perPage ? UserService::getUsers('S',$organizationId,$perPage) :
            UserService::getUsers('S',$organizationId);
    }

    /**
     * Agrega un usuario estudiante a una organización, usa el método
     * StudentService::addNewStudent($request,$organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return StudentService::addNewStudent($request,$organizationId);
    }

    /**
     * Devuelve los datos de un usuario estudiante dado un id, usa el método
     * UserService::getUserById($id,'S',$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show($id,Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return UserService::getUserById($id,'S',$organizationId);
    }

    /**
     * Actualiza los datos de un usuario estudiante usando el método
     * StudentService::updateStudent($request,$id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $organizationId = $request->header('Organization-Key');
        return StudentService::updateStudent($request,$id,$organizationId);
    }

    /**
     * Elimina un usuario estudiante dado su id usando el método UserService::deleteUser($id,'S',$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return UserService::deleteUser($id,'S',$organizationId);
    }

    /**
     * Devuelve los usuarios con rol estudiante que estén en estatus activo usando el método
     * UserService::activeUsers('S',$organizationId) o UserService::activeUsers('S',$organizationId,$perPage) si usa
     * paginación.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function active(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $perPage = $request->input('per_page');
        return $perPage ? UserService::activeUsers('S',$organizationId,$perPage) :
            UserService::activeUsers('S',$organizationId);
    }

    /**
     * Agrega una entidad estudiante a un usuario con ese rol usando el servicio
     * StudentService::addStudentContinue($request,$id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addStudentToUser($id,Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return StudentService::addStudentContinue($request,$id,$organizationId);
    }

    /**
     * Elimina una entidad estudiante de un usuario con el servicio
     * StudentService::deleteStudent($id,$studentId,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteStudent($id,Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $studentId = $request->input('student_id');
        return StudentService::deleteStudent($id,$studentId,$organizationId);
    }

    /**
     * Lista todos los estudiantes que presentan alguna incidencia, o estatus diferente al regular usando el servicio
     * StudentService::warningStudent($organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function warningStudent(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return StudentService::warningStudent($organizationId);
    }
}
