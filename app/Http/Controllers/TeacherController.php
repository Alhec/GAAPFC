<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\TeacherService;

/**
 * @package : Controller
 * @author : Hector Alayon
 * @version : 1.0
 */
class TeacherController extends Controller
{
    /**
     * Obtiene todos los usuarios de rol profesor de una organización usa el metodo
     * UserService::getUsers('T',$organizationId) o UserService::getUsers('T',$organizationId,$perPage) si usa
     * paginación.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $perPage = $request->input('per_page');
        return $perPage ? UserService::getUsers('T',$organizationId,$perPage) :
            UserService::getUsers('T',$organizationId);
    }

    /**
     * Agrega un usuario profesor a una organización, usa el método TeacherService::addTeacher($request,$organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return TeacherService::addTeacher($request,$organizationId);
    }

    /**
     * Devuelve los datos de un usuario profesor dado un id, usa el método
     * UserService::getUserById($id,'T',$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show($id,Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return UserService::getUserById($id,'T',$organizationId);
    }

    /**
     * Actualiza los datos de un usuario profesor usando el método
     * TeacherService::updateTeacher($request,$id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $organizationId = $request->header('Organization-Key');
        return TeacherService::updateTeacher($request,$id,$organizationId);
    }

    /**
     * Elimina un usuario profesor dado su id usando el método UserService::deleteUser($id,'T',$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return UserService::deleteUser($id,'T',$organizationId);
    }

    /**
     * Devuelve los usuarios con rol profesor que estén en estatus activo usando el método
     * UserService::activeUsers('T',$organizationId) o UserService::activeUsers('T',$organizationId,$perPage) si usa
     * paginación.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function active(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $perPage = $request->input('per_page');
        return $perPage ? UserService::activeUsers('T',$organizationId,$perPage) :
            UserService::activeUsers('T',$organizationId);
    }
}
