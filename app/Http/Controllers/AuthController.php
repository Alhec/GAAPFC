<?php

namespace App\Http\Controllers;

use App\Log;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Http\Response;

/**
 * @package : Controller
 * @author : Hector Alayon
 * @version : 1.0
 */
class AuthController extends Controller
{
    const logLogout = 'Realizo cierre de sesion';
    const taskError = 'No se puede proceder con la tarea';

    /**
     * Obtenga un JWT a través de credenciales dadas.
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }


    /**
     * Usa el servicio AuthService::login($request,$organizationId) para autenticar al usuario.
     * @param Request $request
     * @return Response
     */
    public function login(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        return AuthService::login($request,$organizationId);
    }

    /**
     * Devuelve los datos del usuario.
     * @return Response
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Devuelve la carga útil del JWT.
     * @return Response
     */
    public function payload()
    {
        return response()->json(auth()->payload());
    }

    /**
     * Cerrar sesión del usuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $log = Log::addLog(auth('api')->user()['id'],self::logLogout);
        if (is_numeric($log)&&$log==0){
            return response()->json(['message' => self::taskError], 500);
        }
        auth()->logout();
        return response()->json(['message' => 'Cerro sesión exitosamente']);
    }

    /**
     * Refresca el time life de la sesión.
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Obtiene la estructura del token.
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL(),
            'user' => auth()->user(),
        ]);
    }

    /**
     * Obtiene el token del usuario.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToken()
    {
        $token = auth('api')->getToken();
        return $token;
    }

}
