<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

/**
 * @package : Controller
 * @author : Hector Alayon
 * @version : 1.0
 */
class UserController extends Controller
{
    /**
     * Usa el servicio UserService::changePassword($request,$organizationId) para cambiar la contraseña del usuario
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function changePassword(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return UserService::changePassword($request,$organizationId);
    }

    /**
     * Usa el servicio UserService::changeUserData($request,$organizationId) para cambiar los datos del usuario.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function changeUserData(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return UserService::changeUserData($request,$organizationId);
    }

    /**
     * Usa el servicio UserService::changePassword($request,$organizationId) para cambiar la contraseña del usuario
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function resetPassword(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return UserService::resetPassword($request,$organizationId);
    }
}
