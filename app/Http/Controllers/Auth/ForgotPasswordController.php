<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Log;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;
use Mockery\Generator\StringManipulation\Pass\Pass;

/**
 * @package : Controller
 * @author : Hector Alayon
 * @version : 1.0
 */
class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    const taskError = 'No se puede proceder con la tarea';
    const invalidUser ='Usuario Invalido';
    const invalidPass = 'Clave Invalida';
    const invalidToken = 'Token Invalido';
    const sendEmail = 'Correo enviado';
    const notSendEmail = 'No se pudo enviar el correo';
    const logUserRequestRecoverPass = 'Solicitud de recuperacion de clave';
    const logSendEmailRecoverPass = 'Correo enviado para recuperacion de clave';
    const logNotSendEmailRecoverPass = 'Correo no enviado para recuperacion de clave';

    /**
     * Crea una nueva instancia del controlador
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }


    /**
     * Valida los siguientes atributos
     * *email: requerido y estructura de email
     * *user_type: requerido, máximo 1 y termina en S,T o A
     * además de ello envía el correo al usuario con el link de recuperación de contraseña.
     * @param Request $request
     * @return Response
     * @throws
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);
        $request['organization_id'] = $request->header('Organization-Key');
        $user = User::getUserByEmail($request['email'],$request['organization_id']);
        if (is_numeric($user) && $user==0){
            return response()->json(['message' => self::taskError], 500);
        }
        try{
            $response = $this->broker()->sendResetLink(
                $request->only('email','organization_id')
            );
            if (count($user)>0){
                $log = Log::addLog($user[0]['id'],self::logUserRequestRecoverPass);
                if (is_numeric($log) && $log==0){
                    return response()->json(['message'=>self::taskError],500);
                }
            }
            switch ($response) {
                case \Password::INVALID_USER:
                    return response()->json(['message'=>self::invalidUser], 422);
                    break;
                case \Password::INVALID_TOKEN:
                    return response()->json(['message'=> self::invalidToken], 422);
                    break;
                default:
                    $log = Log::addLog($user[0]['id'],self::logSendEmailRecoverPass);
                    if (is_numeric($log) && $log==0){
                        return response()->json(['message'=>self::taskError],500);
                    }
                    return response()->json(['message'=>self::sendEmail], 200);
            }
        }catch(\Exception $e){
            $log = Log::addLog($user[0]['id'],self::logNotSendEmailRecoverPass);
            if (is_numeric($log) && $log==0){
                return response()->json(['message'=>self::taskError],500);
            }
            return response()->json(['message'=>self::notSendEmail], 206);
        }
    }
}
