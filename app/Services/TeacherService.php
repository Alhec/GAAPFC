<?php
/**
 * Created by PhpStorm.
 * User: halayon
 * Date: 28/08/19
 * Time: 03:59 PM
 */

namespace App\Services;


use App\Log;
use App\Roles;
use Illuminate\Http\Request;
use App\User;
use App\Teacher;
use Illuminate\Http\Response;

/**
 * @package : Services
 * @author : Hector Alayon
 * @version : 1.0
 */
class TeacherService
{
    const taskError = 'No se puede proceder con la tarea';
    const busyCredential = 'Identificacion o Correo ya registrados';
    const isStudent = 'Un estudiante cursando un programa academico no puede ser profesor';
    const notFoundUser = 'Usuario no encontrado';
    const taskPartialError = 'No se pudo proceder con la tarea en su totalidad';
    const notSendEmail = 'No se pudo enviar el correo electronico';
    const noAction = "No esta permitido realizar esta accion";
    const unauthorized = "Unauthorized";
    const logCreateTeacher = 'Creo la entidad teacher para ';
    const logUpdateTeacher = 'Actualizo la entidad teacher para ';

    /**
     * Valida que se cumpla las restricciones:
     * *teacher_type: requerido, máximo 3 y finaliza en CON, JUB, REG, OTH
     * *dedication requerido, máximo 3 y finaliza en MT, TC, EXC y TCO
     * *category: requerido, máximo 3 y finaliza en INS, ASI, AGR, ASO, TIT, INV
     * *home_institute:  máximo 100
     * *country:  máximo 20
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validate(Request $request)
    {
        $request->validate([
            'teacher_type'=>'required|max:3|ends_with:CON,JUB,REG,OTH',
            'dedication'=>'required|max:3|ends_with:MT,TC,EXC,TCO',
            'category'=>'required|max:3|ends_with:INS,ASI,AGR,ASO,TIT,INV',
            'home_institute'=>'max:100',
            'country'=>'max:20'
        ]);
    }

    /**
     * Agrega un usuario profesor y se envía un correo al usuario, con el método
     * Teacher::addTeacher([
     * 'id'=>$userId,
     * 'teacher_type'=>$request['teacher_type'],
     * 'dedication'=>$request['dedication'],
     * 'category'=>$request['category'],
     * 'home_institute'=>$request['home_institute'],
     * 'country'=>$request['country']
     * ]).
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @param string $userId Id del usuario
     * @return Response|User, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcta
     * devolvera el objeto usuario.
     */
    public static function createTeacher(Request $request,$organizationId,$userId)
    {
        $result = Teacher::addTeacher([
            'id'=>$userId,
            'teacher_type'=>$request['teacher_type'],
            'dedication'=>$request['dedication'],
            'category'=>$request['category'],
            'home_institute'=>$request['home_institute'],
            'country'=>$request['country'],
        ]);
        if (is_numeric($result)&&$result==0){
            return response()->json(['message' => self::taskPartialError], 500);
        }
        $rol = Roles::addRol(['user_id'=>$userId,'user_type'=>'T']);
        if (is_numeric($rol)&&$rol==0){
            return response()->json(['message' => self::taskPartialError], 500);
        }
        $log = Log::addLog(auth('api')->user()['id'],self::logCreateTeacher.$request['first_name'].
            ' '.$request['first_surname']);
        if (is_numeric($log)&&$log==0){
            return response()->json(['message' => self::taskPartialError], 500);
        }
        $result = EmailService::userCreate($userId,$organizationId,'T');
        if ($result==0){
            return response()->json(['message'=>self::notSendEmail],206);
        }
        return UserService::getUserById($userId,'T',$organizationId);
    }

    /**
     * Evalúa al usuario de tener rol estudiante verifica que este no esté cursando un programa escolar de estar
     * cursando un programa escolar devolverá un mensaje asociado, de no ser estudiante, se procede agregar el rol
     * profesor con el método self::createTeacher($request,$organizationId,$user[0]['id'])
     * @param Request $request Objeto con los datos de la petición
     * @param User $user Objeto con los datos del usuario a anexar rol profesor
     * @param string $organizationId Id de la organiación
     * @return Response|User, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera
     * correcta devolvera el objeto user.
     */
    public static function evaluateAndCreateTeacher(Request $request,$user,$organizationId)
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
            $result = UserService::updateUser($request,$user[0]['id'],'T',$organizationId);
            if(is_numeric($result)&&$result==0){
                return response()->json(['message'=>self::taskError],500);
            }
            return self::createTeacher($request,$organizationId,$user[0]['id']);
        }
        return response()->json(['message' => self::isStudent], 206);
    }

    /**
     * Agrega un usuario profesor y se envía un correo al usuario, con el método
     * UserService::addUser($request,'T',$organizationId) y el método
     * self::createTeacher($request,$organizationId,$userByCredentials[0]['id']) o con el metodo
     * self::evaluateAndCreateTeacher($request,$userByCredentials,$organizationId).
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @return Response|User, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera
     * correcta devolvera el objeto user.
     */
    public static function addTeacher(Request $request,$organizationId)
    {
        self::validate($request);
        $user =UserService::addUser($request,'T',$organizationId);
        if ($user==="busy_credential"){
            $userByCredentials = User::getUserByIdentification($request['identification'],$organizationId);
            $userByEmail = User::getUserByEmail($request['email'],$organizationId);
            if ((is_numeric($userByCredentials)&& $userByCredentials==0)||(is_numeric($userByEmail)&&$userByEmail==0)){
                return response()->json(['message'=>self::taskError],500);
            }
            if (count($userByCredentials)>0 && count($userByEmail)>0){
                if ($userByCredentials[0]['id']==$userByEmail[0]['id'] &&
                    $userByCredentials[0]['identification']==$request['identification'] &&
                    $userByCredentials[0]['email']==$request['email'] && !isset($userByCredentials[0]['teacher'])){
                    return self::evaluateAndCreateTeacher($request,$userByCredentials,$organizationId);
                }
            }else if(count($userByCredentials)>0 && count($userByEmail)==0){
                if ($userByCredentials[0]['identification']==$request['identification'] &&
                    $userByCredentials[0]['email']==$request['email'] && !isset($userByCredentials[0]['teacher'])){
                    return self::evaluateAndCreateTeacher($request,$userByCredentials,$organizationId);
                }
            }else if(count($userByEmail)>0 && count($userByCredentials)==0){
                if ($userByEmail[0]['identification']==$request['identification'] &&
                    $userByEmail[0]['email']==$request['email'] && !isset($userByCredentials[0]['teacher'])){
                    return self::evaluateAndCreateTeacher($request,$userByEmail,$organizationId);
                }
            }
            return response()->json(['message' => self::busyCredential], 206);
        }else if (is_numeric($user)&&$user==0){
            return response()->json(['message'=>self::taskError],500);
        }else{
            return self::createTeacher($request,$organizationId,$user);
        }
    }

    /**
     * Edita un usuario profesor, con el método UserService::updateUser($request,$id,'T',$organizationId) y el método
     * Teacher::updateTeacher($id, [
     * 'id' => $id,
     * 'teacher_type' => $request['teacher_type'],
     * 'dedication'=>$request['dedication'],
     * 'category'=>$request['category'],
     * 'home_institute'=>$request['home_institute'],
     * 'country'=>$request['country']
     * ]).
     * @param Request $request Objeto con los datos de la petición
     * @param string $id Id del usuario
     * @param string $organizationId Id de la organiación
     * @return Response|User, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera
     * correcta devolvera el objeto user.
     */
    public static function updateTeacher(Request $request, $id,$organizationId)
    {
        self::validate($request);
        $result =UserService::updateUser($request,$id,'T',$organizationId);
        if ($result==="not_found"){
            return response()->json(['message'=>self::notFoundUser],206);
        }else if (is_numeric($result)&&$result==0){
            return response()->json(['message'=>self::taskError],500);
        }else if ($result==="busy_credential"){
            return response()->json(['message'=>self::busyCredential],206);
        }else {
            $result = Teacher::updateTeacher($id, [
                'id' => $id,
                'teacher_type' => $request['teacher_type'],
                'dedication'=>$request['dedication'],
                'category'=>$request['category'],
                'home_institute'=>$request['home_institute'],
                'country'=>$request['country'],
            ]);
            if (is_numeric($result)&&$result==0){
                return response()->json(['message' => self::taskPartialError], 500);
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logUpdateTeacher.$request['first_name'].' '.
                $request['first_surname']);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message' => self::taskPartialError], 500);
            }
            return UserService::getUserById($id, 'T',$organizationId);
        }
    }

    /**
     * Válida si los datos enviados por parámetros son del profesor que realiza la petición o son de un usuario
     * administrador de lo contrario no estará autorizado.
     * @param string $teacherId Id del usuario
     * @param string $organizationId Id de la organiación
     * @return Response|string, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera
     * correcta devolvera el string valid.
     */
    public static function validateTeacher($teacherId,$organizationId)
    {
        $existTeacherId=User::existUserById($teacherId,'T',$organizationId);
        if (is_numeric($existTeacherId)&&$existTeacherId==0){
            return response()->json(['message'=>self::taskError],500);
        }
        if ($existTeacherId){
            $roles =array_column(auth()->payload()['user']->roles,'user_type');
            if(!in_array('A',$roles) && auth()->payload()['user']->id!=$teacherId){
                return response()->json(['message'=>self::unauthorized],401);
            }
            return 'valid';
        }
        return response()->json(['message'=>self::notFoundUser],206);
    }
}
