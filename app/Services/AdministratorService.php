<?php
/**
 * Created by PhpStorm.
 * User: halayon
 * Date: 03/10/19
 * Time: 04:30 PM
 */

namespace App\Services;

use App\Log;
use App\Roles;
use Illuminate\Http\Request;
use App\Administrator;
use App\User;
use Illuminate\Http\Response;

/**
 * @package : Services
 * @author : Hector Alayon
 * @version : 1.0
 */
class AdministratorService
{
    const taskError = 'No se puede proceder con la tarea';
    const busyCredential = 'Identificacion o Correo ya registrados';
    const isStudent = 'Un estudiante cursando un programa academico no puede ser administrador';
    const notFoundUser = 'Usuario no encontrado';
    const taskPartialError = 'No se pudo proceder con la tarea en su totalidad';
    const notSendEmail = 'No se pudo enviar el correo electronico';
    const unauthorized = 'Unauthorized';
    const notDeletePrincipal = 'Debe designar otro coordinador principal para poder eliminar este usuario, solo el coordinador principal puede realizar esta accion';
    const hasNotPrincipal = 'No hay coordinador principal';
    const logCreateAdmin = 'Creo la entidad administrator para ';
    const logUpdateAdmin = 'Actualizo la entidad administrator para ';

    /**
     *Valida que se cumpla las restricciones:
     * *rol: requerido, máximo 11 y finaliza en COORDINATOR o SECRETARY
     * *principal: booleano
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validate(Request $request)
    {
        $request->validate([
            'rol'=>'required|max:11|ends_with:COORDINATOR,SECRETARY',
            'principal'=>'boolean'
        ]);
    }

    /**
     * Agrega un usuario administrador ya sea con el sub-rol de secretario o coordinador y  se envía un correo al
     * usuario, en caso de que el campo principal esté seteado esta acción solo la puede realizar el actual coordinador
     * principal, y este dejara de ser coordinador principal con el método
     * Administrator::addAdministrator([
     * 'id'=>$user,
     * 'rol'=>$request['rol'],
     * 'principal'=>$request['principal']
     * ]).
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @param string $userId Id del usuario
     * @return Response|User, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcta
     * devolvera el objeto usuario.
     */
    public static function createAdmin(Request $request,$organizationId,$userId)
    {
        if (!isset($request['principal'])){
            $request['principal']=false;
        }
        if ($request['principal']&& $request['rol']=='COORDINATOR'){
            $result = Administrator::updateAdministrator(auth()->payload()['user']->id, [
                'id'=>auth()->payload()['user']->id,
                'rol'=>auth()->payload()['user']->administrator->rol,
                'principal'=>false
            ]);
            if(is_numeric($result) && $result==0){
                return response()->json(['message'=>self::taskError],500);
            }
        }
        if ($request['rol']=='COORDINATOR'){
            $result = Administrator::addAdministrator([
                'id'=>$userId,
                'rol'=>$request['rol'],
                'principal'=>$request['principal']
            ]);
        }else{
            $result = Administrator::addAdministrator([
                'id'=>$userId,
                'rol'=>$request['rol'],
                'principal'=>false
            ]);
        }
        if(is_numeric($result) && $result==0){
            return response()->json(['message' => self::taskPartialError], 500);
        }
        $rol = Roles::addRol(['user_id'=>$userId,'user_type'=>'A']);
        if (is_numeric($rol)&&$rol==0){
            return response()->json(['message' => self::taskPartialError], 500);
        }
        $log = Log::addLog(auth('api')->user()['id'],self::logCreateAdmin.$request['first_name'].
            ' '.$request['first_surname']);
        if (is_numeric($log)&&$log==0){
            return response()->json(['message' => self::taskPartialError], 500);
        }
        $result = EmailService::userCreate($userId,$organizationId,'A');
        if ($result==0){
            return response()->json(['message'=>self::notSendEmail],206);
        }
        return UserService::getUserById($userId,'A',$organizationId);
    }

    /**
     * Evalúa al usuario de tener rol estudiante verifica que este no esté cursando un programa escolar de estar
     * cursando un programa escolar devolverá un mensaje asociado, de no ser estudiante, se procede agregar el rol
     * administrador con el método self::createAdmin($request,$organizationId,$userByCredentials[0]['id'])
     * @param Request $request Objeto con los datos de la petición
     * @param User $user Objeto con los datos del usuario a anexar rol administrador
     * @param string $organizationId Id de la organiación
     * @return Response|User, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera
     * correcta devolvera el objeto user.
     */
    public static function evaluateAndCreateAdmin(Request $request,$user,$organizationId)
    {
        $isStudent = false;
        if (isset($user[0]['student'])){
            foreach ($user[0]['student'] as $student){
                if ($student['current_status']!='ENDED'||!$student['end_program']){
                    $isStudent = true;
                }
            }
        }
        if (!$isStudent){
            $request['active'] = $user[0]['active'];
            $result = UserService::updateUser($request,$user[0]['id'],'A',$organizationId);
            if(is_numeric($result)&&$result==0){
                return response()->json(['message'=>self::taskError],500);
            }
            return self::createAdmin($request,$organizationId,$user[0]['id']);
        }
        return response()->json(['message' => self::isStudent], 206);
    }

    /**
     * Agrega un usuario administrador ya sea con el sub-rol de secretario o coordinador y  se envía un correo al
     * usuario, en caso de que el campo principal esté seteado esta acción solo la puede realizar el actual coordinador
     * principal, y este dejara de ser coordinador principal con el método
     * UserService::addUser($request,'A',$organizationId) y el método
     * self::evaluateAndCreateAdmin($request,$userByCredentials,$organizationId) o el método
     * self::createAdmin($request,$organizationId,$user).
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @return Response|User, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera
     * correcta devolvera el objeto user.
     */
    public static function addAdministrator(Request $request,$organizationId)
    {
        self::validate($request);
        if ($request['principal']&& $request['rol']=='COORDINATOR'){
            if (((auth()->payload()['user']->administrator->principal) ==false &&
                    auth()->payload()['user']->administrator->rol=='COORDINATOR' )||
                auth()->payload()['user']->administrator->rol=='SECRETARY'){
                return response()->json(['message'=>self::unauthorized],401);
            }
        }
        $user = UserService::addUser($request,'A',$organizationId);
        if ($user==="busy_credential") {
            $userByCredentials = User::getUserByIdentification($request['identification'],$organizationId);
            $userByEmail = User::getUserByEmail($request['email'],$organizationId);
            if ((is_numeric($userByCredentials)&& $userByCredentials==0)||(is_numeric($userByEmail)&&$userByEmail==0)){
                return response()->json(['message'=>self::taskError],500);
            }
            if (count($userByCredentials)>0 && count($userByEmail)>0){
                if ($userByCredentials[0]['id']==$userByEmail[0]['id'] &&
                    $userByCredentials[0]['identification']==$request['identification'] &&
                    $userByCredentials[0]['email']==$request['email'] &&
                    !isset($userByCredentials[0]['administrator'])){
                    return self::evaluateAndCreateAdmin($request,$userByCredentials,$organizationId);
                }
            }else if (count($userByCredentials)>0 && count($userByEmail)==0){
                if ($userByCredentials[0]['identification']==$request['identification'] &&
                    $userByCredentials[0]['email']==$request['email'] &&
                    !isset($userByCredentials[0]['administrator'])){
                    return self::evaluateAndCreateAdmin($request,$userByCredentials,$organizationId);
                }
            }else if (count($userByEmail)>0 && count($userByCredentials)==0){
                if ($userByEmail[0]['identification']==$request['identification'] &&
                    $userByEmail[0]['email']==$request['email'] &&
                    !isset($userByEmail[0]['administrator'])){
                    return self::evaluateAndCreateAdmin($request,$userByEmail,$organizationId);
                }
            }
            return response()->json(['message' => self::busyCredential], 206);
        }else if(is_numeric($user) && $user==0){
            return response()->json(['message'=>self::taskError],500);
        }
        return self::createAdmin($request,$organizationId,$user);
    }

    /**
     * Edita un usuario administrador ya sea con el sub-rol de secretario o coordinador, en caso de que el campo
     * principal esté seteado esta acción solo la puede realizar el actual coordinador principal, y este dejara de ser
     * coordinador principal con el método UserService::updateUser($request,$id,'A',$organizationId) y el método
     * Administrator::updateAdministrator($id, [
     * 'id'=>$id,
     * 'rol'=>$request['rol'],
     * 'principal'=>$request['principal']).
     * @param Request $request Objeto con los datos de la petición
     * @param string $id Id del usuario
     * @param string $organizationId Id de la organiación
     * @return Response|User, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera
     * correcta devolvera el objeto user.
     */
    public static function updateAdministrator(Request $request, $id,$organizationId)
    {
        self::validate($request);
        if ($request['principal']&& $request['rol']=='COORDINATOR'){
            $request['active']=true;
            if (((auth()->payload()['user']->administrator->principal) ==false &&
                    auth()->payload()['user']->administrator->rol=='COORDINATOR' )||
                auth()->payload()['user']->administrator->rol=='SECRETARY'){
                return response()->json(['message'=>self::unauthorized],401);
            }
        }
        $result = UserService::updateUser($request,$id,'A',$organizationId);
        if ($result==="not_found"){
            return response()->json(['message'=>self::notFoundUser],206);
        }else if (is_numeric($result)&& $result==0){
            return response()->json(['message'=>self::taskError],500);
        }else if ($result==="busy_credential"){
            return response()->json(['message'=>self::busyCredential],206);
        }else {
            if (!isset($request['principal'])){
                $request['principal']=false;
            }
            if ($request['principal'] && $request['rol']=='COORDINATOR' ){
                if(auth()->payload()['user']->id != $id){
                    $result = Administrator::updateAdministrator(auth()->payload()['user']->id, [
                        'id'=>auth()->payload()['user']->id,
                        'rol'=>auth()->payload()['user']->administrator->rol,
                        'principal'=>false
                    ]);
                    if (is_numeric($result)&& $result==0){
                        return response()->json(['message'=>self::taskError],500);
                    }
                }
            }
            if ($request['rol']=='COORDINATOR'){
                $result=Administrator::updateAdministrator($id, [
                    'id'=>$id,
                    'rol'=>$request['rol'],
                    'principal'=>$request['principal']
                ]);
            }else{
                $result=Administrator::updateAdministrator($id, [
                    'id'=>$id,
                    'rol'=>$request['rol'],
                    'principal'=>false
                ]);
            }
            if (is_numeric($result)&& $result==0){
                return response()->json(['message' => self::taskPartialError], 500);
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logUpdateAdmin.$request['first_name'].
                ' '.$request['first_surname']);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message' => self::taskPartialError], 500);
            }
            return UserService::getUserById($id, 'A',$organizationId);
        }
    }

    /**
     * Elimina un usuario administrador con excepción del coordinador principal con el método
     * UserService::deleteUser($id,'A',$organizationId).
     * @param string $id Id del usuario
     * @param string $organizationId Id de la organiación
     * @return Response, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcta devolvera
     * Ok.
     */
    public static function deleteAdministrator($id,$organizationId)
    {
        $administrator = User::getUserById($id,'A',$organizationId);
        if (is_numeric($administrator)&&$administrator==0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (count($administrator)<=0){
            return response()->json(['message'=>self::notFoundUser],206);
        }
        if ($administrator[0]['administrator']['principal'] && $administrator[0]['administrator']['rol']=='COORDINATOR'){
            return response()->json(['message'=>self::notDeletePrincipal],206);
        }
        return UserService::deleteUser($id,'A',$organizationId);
    }

    /**
     * Obtiene el coordinador principal de la organización usando el método
     * Administrator::getPrincipalCoordinator($organizationId)
     * @param string $organizationId Id de la organiación
     * @param string $internalCall bandera para identificar si la llamada va a un controlador(0)  o un servicio(1)
     * @return Response|string|integer, de ocurrir un error devolvera 0, si no existe un administrador principal arroja
     * un mensaje asociado, y si se realiza de manera correcta devolvera el objeto user.
     */
    public static function getPrincipalCoordinator($organizationId,$internalCall)
    {
        $administrator = Administrator::getPrincipalCoordinator($organizationId);
        if (is_numeric($administrator)&&$administrator==0){
            if ($internalCall){
                return 0;
            }
            return response()->json(['message'=>self::taskError],500);
        }
        if (count($administrator)>0){
            return $administrator[0];
        }
        if ($internalCall){
            return 'noExist';
        }
        return response()->json(['message'=>self::hasNotPrincipal],206);
    }
}
