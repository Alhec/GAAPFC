<?php
/**
 * Created by PhpStorm.
 * User: halayon
 * Date: 28/11/19
 * Time: 03:46 PM
 */

namespace App\Services;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\User;
use App\Log;

/**
 * @package : Services
 * @author : Hector Alayon
 * @version : 1.0
 */
class AuthService
{
    const taskError = 'No se puede proceder con la tarea';
    const invalidUser='Usuario o clave errados';
    const logUserLogin= 'El usuario se ha autenticado';

    /**
     * Realiza la autenticacion del usuario.
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @return Response Valida si el usuario existe y pertenece a la organización donde intenta autenticarse, si el
     * usuario no existe devuelve un mensaje de error, de fallar consultas en base de datos no procederá con la tarea,
     * en caso de ser exitoso genera un token y crea una sesión para el usuario autenticado.
     */
    public static function login(Request $request,$organizationId){
        $credentials= json_decode($request->getContent(),true);
        $token = auth('api')->attempt(
            [
                'identification'=>$credentials['identification'],
                'password'=>$credentials['password'],
                'organization_id'=>$organizationId,
                'active'=>1
            ]);
        if (!$token) {
            return response()->json(['error' => self::invalidUser], 401);
        }
        $user=User::getUserByIdWithoutFilterRol(auth('api')->user()['id'],$organizationId);
        if (is_numeric($user)&&$user==0){
            return response()->json(['message' => self::taskError], 500);
        }
        $log = Log::addLog(auth('api')->user()['id'],self::logUserLogin);
        if (is_numeric($log)&&$log==0){
            return response()->json(['message' => self::taskError], 500);
        }
        $user=$user->toArray()[0];
        return response()->json([
            'token' => $token,
            'type' => 'bearer',
            'expires' => auth('api')->factory()->getTTL(),
            'user' => $user,
        ]);
    }
}
