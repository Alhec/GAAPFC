<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Log;
use App\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @package : Controller
 * @author : Hector Alayon
 * @version : 1.0
 */
class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    const taskError = 'No se puede proceder con la tarea';
    const emailNotFound = 'Correo no encontrado';
    const resetPassword = 'Clave reseteada';
    const notResetPassword = 'Token invalido o vencido';
    const logResetPasword = 'Reseteo su clave';
    const logNotResetPasword = 'Provee token invalido';

    /**
     * Dónde redirigir a los usuarios después de restablecer su contraseña.
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Crea una nueva instancia del controlador
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Reglas a validar
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|max:30|email',
            'password' => 'required|confirmed',
        ];
    }

    /**
     * Obtiene valores especificos de la peticion
     * @param Request $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
    }

    /**
     * Valida los siguientes atributos
     * *email: requerido y estructura de email
     * *user_type: requerido, máximo 1 y termina en S,T o A
     * *token: requerido
     * *password: requerido y confirmación
     * además de ello resetea la clave del usuario.
     * @param Request $request
     * @return Response
     */
    public function reset(Request $request)
    {
        $request->validate( [
            'token' => 'required',
            'email' => 'required|max:30|email',
            'password' => 'required|confirmed',
        ]);
        $request['organization_id'] = $request->header('Organization-Key');
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );
        $user = User::getUserByEmail($request['email'],$request['organization_id']);
        if (is_numeric($user) && $user==0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (count($user)>0){
            if($response == \Password::PASSWORD_RESET){
                $log = Log::addLog($user[0]['id'],self::logResetPasword);
                if (is_numeric($log) && $log==0){
                    return response()->json(['message'=>self::taskError],500);
                }
                return response()->json(['message'=>self::resetPassword], 200);
            };
            $log = Log::addLog($user[0]['id'],self::logNotResetPasword);
            if (is_numeric($log) && $log==0){
                return response()->json(['message'=>self::taskError],500);
            }
            return response()->json(['message'=>self::notResetPassword], 422);
        }
        return response()->json(['message'=>self::emailNotFound], 206);
    }

    /**
     * Resetea la clave del usuario.
     * @param string $user
     * @param string $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
        ])->save();
    }
}
