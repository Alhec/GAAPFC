<?php
/**
 * Created by PhpStorm.
 * User: halayon
 * Date: 27/08/19
 * Time: 02:24 PM
 */

namespace App\Services;


use App\Administrator;
use App\Log;
use App\Roles;
use App\Student;
use App\Teacher;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;


class UserService
{
    const taskError = 'No se puede proceder con la tarea';
    const emptyUser = 'No existen usuarios con ese perfil';
    const notFoundUser = 'Usuario no encontrado';
    const ok = 'OK';
    const notFoundActiveUser = 'No existen usuarios activos con ese perfil';
    const invalidPassword = 'La clave no puede ser igual a su clave anterior';
    const invalidCurrentPassword = 'Su clave actual esta errada';
    const busyCredential = 'Identificacion o Correo ya registrados';

    const logChangePassword = 'Realizo un cambio de contraseña';
    const logChangeUserData = 'Realizo cambios en sus datos de usuario';
    const logDeleteUser = 'Elimino al usuario ';
    const logCreateUser = 'Creo al usuario ';
    const logUpdateUser = 'Actualizo al usuario ';
    const logRol = ' con rol ';

    public static function getUsers($userType,$organizationId,$perPage=0)
    {
        $perPage == 0 ? $users= User::getUsers($userType,$organizationId) :
            $users= User::getUsers($userType,$organizationId,$perPage);
        if (is_numeric($users) && $users == 0){
            return response()->json(['message'=>self::taskError],206);
        }
        if ($perPage == 0){
            if (count($users)>0){
                return $users;
            }
            return response()->json(['message'=>self::emptyUser],206);
        }else{
            return $users;
        }

    }

    public static function getUserById($userId, $userType,$organizationId)
    {
        $user = User::getUserById($userId,$userType,$organizationId);
        if (is_numeric($user) && $user == 0){
            return response()->json(['message'=>self::taskError],206);
        }
        if (count($user)>0){
            return $user[0];
        }
        return response()->json(['message'=>self::notFoundUser],206);
    }

    public static function validate(Request $request)
    {
        $request->validate([
            'identification'=>'required|max:20',
            'first_name'=>'required|max:20',
            'second_name'=>'max:20',
            'first_surname'=>'required|max:20',
            'second_surname'=>'max:20',
            'telephone'=>'max:15',
            'mobile'=>'required|max:15',
            'work_phone'=>'max:15',
            'email'=>'required|max:30|email',
            'level_instruction'=>'required|max:3|ends_with:TSU,TCM,Dr,Esp,Ing,MSc,Lic',
            'with_disabilities'=>'boolean',
            'sex'=>'required|max:1|ends_with:M,F',
            'nationality'=>'required|max:1|ends_with:V,E',
        ]);
    }

    public static function addUser(Request $request,$userType,$organizationId)
    {
        self::validate($request);
        $existUserByIdentification=User::existUserByIdentification($request['identification'],$organizationId);
        $existUserByEmail=User::existUserByEmail($request['email'],$organizationId);
        if ((is_numeric($existUserByIdentification) && $existUserByIdentification==0) ||
            (is_numeric($existUserByEmail) && $existUserByEmail==0)){
            return 0;
        }
        if (!($existUserByIdentification)AND!($existUserByEmail)){
            $request['password']=Hash::make($request['identification']);
            $request['active']=true;
            $request['organization_id']=$organizationId;
            $userId= User::addUser($request);
            if ($userId == 0){
                return 0;
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logCreateUser.$request['first_name'].
                ' '.$request['first_surname'].self::logRol.$userType);
            if (is_numeric($log)&&$log==0){
                return 0;
            }
            return $userId;
        }
        return "busy_credential";
    }

    public static function deleteUser($userId,$userType,$organizationId)
    {
        $user = User::getUserById($userId,$userType,$organizationId);
        if (is_numeric($user) && $user == 0){
            return response()->json(['message'=>self::taskError],206);
        }
        $user=$user->toArray();
        if (count($user)>0){
            $usersRol = array_column($user[0]['roles'],'user_type');
            if (count($usersRol)==1){
                $result=User::deleteUser($userId);
                if (is_numeric($result) && $result == 0){
                    return response()->json(['message'=>self::taskError],206);
                }
                $log = Log::addLog(auth('api')->user()['id'],self::logDeleteUser.$user[0]['first_name']
                    .$user[0]['first_surname'].self::logRol.$userType);
                if (is_numeric($log)&&$log==0){
                    return response()->json(['message'=>self::taskError],401);
                }
            }else{
                switch ($userType){
                    case 'A':
                        if ($user[0]['administrator']['rol'] == 'COORDINATOR' &&
                            $user[0]['administrator']['principal'] == false){
                            $result = Administrator::deleteAdministrator($userId);
                            if (is_numeric($result) && $result == 0){
                                return response()->json(['message'=>self::taskError],206);
                            }
                            $result = Roles::deleteRol($userId,$userType);
                            if (is_numeric($result) && $result == 0){
                                return response()->json(['message'=>self::taskError],206);
                            }
                            break;
                        }
                    case 'T':
                        $result = Teacher::deleteTeacher($userId);
                        if (is_numeric($result) && $result == 0){
                            return response()->json(['message'=>self::taskError],206);
                        }
                        $result = Roles::deleteRol($userId,$userType);
                        if (is_numeric($result) && $result == 0){
                            return response()->json(['message'=>self::taskError],206);
                        }
                        break;
                    case 'S':
                        $result = Student::deleteStudentsByUserId($userId);
                        if (is_numeric($result) && $result == 0){
                            return response()->json(['message'=>self::taskError],206);
                        }
                        $result = Roles::deleteRol($userId,$userType);
                        if (is_numeric($result) && $result == 0){
                            return response()->json(['message'=>self::taskError],206);
                        }
                        break;
                    default:
                        break;
                }
            }
            return response()->json(['message'=>self::ok]);
        }
        return response()->json(['message'=>self::notFoundUser],206);
    }

    public static function availableUser(Request $request, $userId,$organizationId)
    {
        $existUserByIdentification=User::existUserByIdentification($request['identification'],$organizationId);
        if (is_numeric($existUserByIdentification) && $existUserByIdentification == 0){
            return 0;
        }
        if ($existUserByIdentification){
            $user =User::getUserByIdentification($request['identification'],$organizationId);
            if (is_numeric($user) && $user ==0){
                return 0;
            }
            if ($user[0]['id']!=$userId){
                return false;
            }
        }
        $existUserByEmail=User::existUserByEmail($request['email'],$organizationId);
        if (is_numeric($existUserByEmail) && $existUserByEmail == 0){
            return 0;
        }
        if ($existUserByEmail){
            $user =User::getUserByEmail($request['email'],$organizationId);
            if (is_numeric($user) && $user ==0){
                return 0;
            }
            if ($user[0]['id']!=$userId){
                return false;
            }
        }
        return true;
    }

    public static function validateUpdate(Request $request)
    {
        $request->validate([
            'active'=>'required|boolean',
        ]);
    }

    public static function updateUser(Request $request, $userId, $userType,$organizationId)
    {
        self::validate($request);
        self::validateUpdate($request);
        $existUserById = User::existUserByIdWithoutFilterRol($userId,$organizationId);
        if (is_numeric($existUserById) && $existUserById == 0 ){
            return 0;
        }
        if ($existUserById){
            $availableUser = self::availableUser($request,$userId,$organizationId);
            if (is_numeric($availableUser) && $availableUser == 0){
                return 0;
            }
            if (!$availableUser){
                return "busy_credential";
            }
            $user=User::getUserByIdWithoutFilterRol($userId,$organizationId);
            if (is_numeric($user)&&$user == 0){
                return 0;
            }
            if(isset($user['administrator']) && $user['administrator']['principal']){
                $request['active']=true;
            }
            $request['password']=$user[0]['password'];
            $result = User::updateUser($userId,$request);
            if (is_numeric($result) && $result == 0){
                return 0;
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logUpdateUser.$request['first_name'].
                ' '.$request['first_surname'].self::logRol.$userType);
            if (is_numeric($log)&&$log==0){
                return 0;
            }
            return $userId;
        }
        return "not_found";
    }

    public static function activeUsers($userType,$organizationId,$perPage=0)
    {
        $perPage == 0 ? $users = User::getUsersActive($userType,$organizationId) :
            $users = User::getUsersActive($userType,$organizationId,$perPage);
        if (is_numeric($users) && $users == 0){
            return response()->json(['message'=>self::taskError],206);
        }
        if ($perPage == 0){
            if (count($users)>0){
                return $users;
            }
            return response()->json(['message'=>self::notFoundActiveUser],206);
        }else{
            return $users;
        }
    }

    public static function changeUserData(Request $request,$organizationId)
    {
        self::validate($request);
        $user=User::getUserById(auth()->payload()['user']->id,auth()->payload()['user']->user_type,$organizationId);
        if (is_numeric($user) && $user ==0){
            return response()->json(['message'=>self::taskError],206);
        }
        $user=$user[0];
        $availableUser = self::availableUser($request,$user['id'],$organizationId);
        if (is_numeric($availableUser) && $availableUser == 0){
            return response()->json(['message'=>self::taskError],206);
        }
        if (!$availableUser){
            return response()->json(['message'=>self::busyCredential],206);
        }
        $request['organization_id']=$organizationId;
        $request['password']=$user['password'];
        $request['activate']=$user['activate'];
        $result = User::updateUser(auth()->payload()['user']->id,$request);
        if (is_numeric($result) && $result ==0){
            return response()->json(['message'=>self::taskError],206);
        }
        $log = Log::addLog(auth()->payload()['user']->id,self::logChangePassword);
        if (is_numeric($log) && $log==0){
            return response()->json(['message'=>self::taskError],206);
        }
        return response()->json(['message'=>self::ok],200);
    }

    public static function validateChangePassword(Request $request)
    {
        $request->validate([
            'old_password'=>'required',
            'password'=>'required|confirmed',
        ]);
    }

    public static function changePassword(Request $request,$organizationId)
    {
        self::validateChangePassword($request);
        $user=User::getUserById(auth()->payload()['user']->id,auth()->payload()['user']->user_type,$organizationId);
        if (is_numeric($user)&&$user==0){
            return response()->json(['message'=>self::taskError],206);
        }
        if (!Hash::check($request['old_password'],$user[0]['password'])){
            return response()->json(['message'=>self::invalidCurrentPassword],206);
        }
        if ($request['old_password']==$request['password']){
            return response()->json(['message'=>self::invalidPassword],206);
        }
        $user=$user->toArray();
        $user=$user[0];
        $user['password']=Hash::make($request['password']);
        unset($user['administrator']);
        unset($user['teacher']);
        unset($user['student']);
        $result = User::updateUserLikeArray(auth()->payload()['user']->id,$user);
        if (is_numeric($result) && $result ==0){
            return response()->json(['message'=>self::taskError],206);
        }
        $log = Log::addLog(auth()->payload()['user']->id,self::logChangePassword);
        if (is_numeric($log) && $log==0){
            return response()->json(['message'=>self::taskError],206);
        }
        return response()->json(['message'=>self::ok],200);
    }
}
