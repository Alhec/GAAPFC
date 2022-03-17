<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\AdministratorService;

/**
 * @package : Controller
 * @author : Hector Alayon
 * @version : 1.0
 */
class AdministratorController extends Controller
{
    /**
     * Obtiene todos los usuarios de rol administrador de una organización usa el método
     * UserService::getUsers('A',$organizationId) o UserService::getUsers('A',$organizationId,$perPage) si usa
     * paginación.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $perPage = $request->input('per_page');
        return $perPage ? UserService::getUsers('A',$organizationId,$perPage) :
            UserService::getUsers('A',$organizationId);
    }

    /**
     * Agrega un usuario administrador a una organización, usa el método
     * AdministratorService::addAdministrator($request,$organizationId).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return AdministratorService::addAdministrator($request,$organizationId);
    }

    /**
     * Devuelve los datos de un usuario administrador dado un id, usa el método
     * UserService::getUserById($id,'A',$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show($id,Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return UserService::getUserById($id,'A',$organizationId);
    }

    /**
     * Actualiza los datos de un usuario administrador usando el método
     * AdministratorService::updateAdministrator($request,$id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($id,Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return AdministratorService::updateAdministrator($request,$id,$organizationId);
    }

    /**
     * Elimina un usuario administrador dado su id usando el método
     * AdministratorService::deleteAdministrator($id,$organizationId).
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return AdministratorService::deleteAdministrator($id,$organizationId);
    }

    /**
     * Devuelve los usuarios con rol administrador que estén en estatus activo usando el método
     * UserService::activeUsers('A',$organizationId) o UserService::activeUsers('A',$organizationId,$perPage) si usa
     * paginación.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function active(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $perPage = $request->input('per_page');
        return $perPage ? UserService::activeUsers('A',$organizationId,$perPage) :
            UserService::activeUsers('A',$organizationId);
    }

    /**
     * Obtiene el usuario con rol coordinador principal usando el método
     * AdministratorService::getPrincipalCoordinator($organizationId,false).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function principal(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return AdministratorService::getPrincipalCoordinator($organizationId,false);
    }
}
