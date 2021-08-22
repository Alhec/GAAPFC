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
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use phpDocumentor\Reflection\Types\Integer;

/**
 * @package : Services
 * @author : Hector Alayon
 * @version : 1.0
 */
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

    /**
     *Obtiene los usuarios de una organización dado su tipo de usuario
     * @param string $userType Tipos de usuario A,T,S
     * @param string $organizationId Id de la organiación
     * @param integer $perPage Parámetro opcional, cantidad de elementos por página, default:0
     * @return User|Response Obtiene todos los usuarios dado un tipo de usuario y una organización usando el metodo
     * User::getUsers($userType,$organizationId).
     */
    public static function getUsers($userType,$organizationId,$perPage=0)
    {
        $perPage == 0 ? $users= User::getUsers($userType,$organizationId) :
            $users= User::getUsers($userType,$organizationId,$perPage);
        if (is_numeric($users) && $users == 0){
            return response()->json(['message'=>self::taskError],500);
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

    /**
     *Obtiene el usuario dado su id, tipo de usuario y organizacion.
     * @param string $userId Id del usuario
     * @param string $userType Tipos de usuario A,T,S
     * @param string $organizationId Id de la organiación
     * @return User|Response Obtiene el usuario dado su id, tipo de usuario y una organización usando el metodo
     * User::getUserById($userId,$userType,$organizationId).
     */
    public static function getUserById($userId, $userType,$organizationId)
    {
        $user = User::getUserById($userId,$userType,$organizationId);
        if (is_numeric($user) && $user == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (count($user)>0){
            return $user[0];
        }
        return response()->json(['message'=>self::notFoundUser],206);
    }

    /**
     *Valida que se cumpla las restricciones:
     * *identification: requerido, máximo 20
     * *first_name: requerido, máximo 20
     * *second_name: máximo 20
     * *first_surname: requerido, máximo 20
     * *second_surname: máximo 20
     * *telephone: máximo 15
     * *mobile: requerido, máximo 15
     * *work_phone: máximo 15
     * *email: válida que sea un correo correcto, es requerido y máximo 3
     * *level_instruction: máximo 3, es requerido y termina en TSU, TCM, Dr, Esp, Ing, MSc o Lic
     * *with_disabilities: boolean
     * *sex: requerido, máximo 1 y termina en M o F
     * *nationality: requerido, máximo 1  y termina en V o E
     * *level_instruction_name: máximo 300
     * @param Request $request Objeto con los datos de la petición
     */
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
            'level_instruction_name'=>'max:300',
        ]);
    }

    /**
     *Verifica que el correo electrónico y la identificación no estén en el sistema, de ser así crea un usuario dado el
     * tipo de usuario y una organización usando el método User::addUser($request).
     * @param Request $request Objeto con los datos de la petición
     * @param string $userType Tipos de usuario A,T,S
     * @param string $organizationId Id de la organiación
     * @return integer|string Agrega un usuario con la data del objeto $user y devuelve el id, si falla devolverá 0, si
     * estan ocupadas las credenciales devolvera un string
     */
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

    /**
     * Si el usuario tiene un solo rol en el sistema elimina un usuario dado su id, tipo de usuario y  organización
     * usando el método User::deleteUser($userId), en caso de tener más de un rol se elimina el rol correspondiente
     * asociado al usuario con la función de acuerdo con el rol.
     * @param string $userId Id del usuario
     * @param string $userType Tipos de usuario A,T,S
     * @param string $organizationId Id de la organiación
     * @return Response, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcta devolvera
     * ok.
     */
    public static function deleteUser($userId,$userType,$organizationId)
    {
        $user = User::getUserById($userId,$userType,$organizationId);
        if (is_numeric($user) && $user == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        $user=$user->toArray();
        if (count($user)>0){
            if ($userType == 'S'){
                foreach ($user[0]['student'] as $student){
                    $result = StudentService::removeAllInscriptionsByStudent($student['id'],$organizationId);
                    if (is_numeric($result) && $result == 0){
                        return response()->json(['message'=>self::taskError],500);
                    }
                }
            }
            $usersRol = array_column($user[0]['roles'],'user_type');
            if (count($usersRol)==1){
                $result=User::deleteUser($userId);
                if (is_numeric($result) && $result == 0){
                    return response()->json(['message'=>self::taskError],500);
                }
                $log = Log::addLog(auth('api')->user()['id'],self::logDeleteUser.$user[0]['first_name'].' '.
                    $user[0]['first_surname'].self::logRol.$userType);
                if (is_numeric($log)&&$log==0){
                    return response()->json(['message' => self::taskError], 500);
                }
            }else{
                switch ($userType){
                    case 'A':
                        if ($user[0]['administrator']['rol'] == 'COORDINATOR' &&
                            $user[0]['administrator']['principal'] == false){
                            $result = Administrator::deleteAdministrator($userId);
                            if (is_numeric($result) && $result == 0){
                                return response()->json(['message'=>self::taskError],500);
                            }
                            $result = Roles::deleteRol($userId,$userType);
                            if (is_numeric($result) && $result == 0){
                                return response()->json(['message'=>self::taskError],500);
                            }
                        }
                        break;
                    case 'T':
                        $result = Teacher::deleteTeacher($userId);
                        if (is_numeric($result) && $result == 0){
                            return response()->json(['message'=>self::taskError],500);
                        }
                        $result = Roles::deleteRol($userId,$userType);
                        if (is_numeric($result) && $result == 0){
                            return response()->json(['message'=>self::taskError],500);
                        }
                        break;
                    case 'S':
                        $result = Student::deleteStudentsByUserId($userId);
                        if (is_numeric($result) && $result == 0){
                            return response()->json(['message'=>self::taskError],500);
                        }
                        $result = Roles::deleteRol($userId,$userType);
                        if (is_numeric($result) && $result == 0){
                            return response()->json(['message'=>self::taskError],500);
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

    /**
     * Válida si la identificación y correo de un usuario están disponibles usando el id y  organización devuelve un
     * booleano.
     * @param Request $request Objeto con los datos de la petición
     * @param string $userId Id del usuario
     * @param string $organizationId Id de la organiación
     * @return integer|bool De estar valido devolvera true, de lo contrario sera false, de haber un error devolvera 0.
     */
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

    /**
     *Valida que se cumpla las restricciones:
     * *active: requerido, booleano
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validateUpdate(Request $request)
    {
        $request->validate([
            'active'=>'required|boolean',
        ]);
    }

    /**
     * Actualiza los datos de un usuario usando el método User::updateUser($userId,$request).
     * @param Request $request Objeto con los datos de la petición
     * @param string $userId Id del usuario
     * @param string $userType Tipos de usuario A,T,S
     * @param string $organizationId Id de la organiación
     * @return integer|string Edita un usuario con la data del objeto $user y devuelve el id, si falla devolverá 0, si
     * estan ocupadas las credenciales devolvera un string
     */
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

    /**
     *Obtiene los usuarios activos de una organización dado su tipo de usuario
     * @param string $userType Tipos de usuario A,T,S
     * @param string $organizationId Id de la organiación
     * @param integer $perPage Parámetro opcional, cantidad de elementos por página, default:0
     * @return User|Response Obtiene todos los usuarios activos dado un tipo de usuario y una organización usando el
    método User::getUsersActive($userType,$organizationId).
     */
    public static function activeUsers($userType,$organizationId,$perPage=0)
    {
        $perPage == 0 ? $users = User::getUsersActive($userType,$organizationId) :
            $users = User::getUsersActive($userType,$organizationId,$perPage);
        if (is_numeric($users) && $users == 0){
            return response()->json(['message'=>self::taskError],500);
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

    /**
     * Actualiza los datos del usuario que lo solicite con el método
     * User::updateUser(auth()->payload()['user']->id,$request)
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @return Response de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcta devolvera
     * ok.
     */
    public static function changeUserData(Request $request,$organizationId)
    {
        self::validate($request);
        $user=User::getUserByIdWithoutFilterRol(auth()->payload()['user']->id,$organizationId);
        if (is_numeric($user) && $user ==0){
            return response()->json(['message'=>self::taskError],500);
        }
        $user=$user[0];
        $availableUser = self::availableUser($request,$user['id'],$organizationId);
        if (is_numeric($availableUser) && $availableUser == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (!$availableUser){
            return response()->json(['message'=>self::busyCredential],206);
        }
        $request['organization_id']=$organizationId;
        $request['password']=$user['password'];
        $request['activate']=$user['activate'];
        $result = User::updateUser(auth()->payload()['user']->id,$request);
        if (is_numeric($result) && $result ==0){
            return response()->json(['message'=>self::taskError],500);
        }
        $log = Log::addLog(auth()->payload()['user']->id,self::logChangePassword);
        if (is_numeric($log) && $log==0){
            return response()->json(['message'=>self::taskError],500);
        }
        return response()->json(['message'=>self::ok],200);
    }

    /**
     *Valida que se cumpla las restricciones:
     * *old_password: requerido
     * *password: requerido, verificado
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validateChangePassword(Request $request)
    {
        $request->validate([
            'old_password'=>'required',
            'password'=>'required|confirmed',
        ]);
    }

    /**
     * Actualiza la clave del usuario que lo solicite y verifique que la clave no sea igual a la clave anterior con el
     * método User::updateUserLikeArray(auth()->payload()['user']->id,$user)
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @return Response de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcta devolvera
     * ok.
     */
    public static function changePassword(Request $request,$organizationId)
    {
        self::validateChangePassword($request);
        $user=User::getUserByIdWithoutFilterRol(auth()->payload()['user']->id,$organizationId);
        if (is_numeric($user)&&$user==0){
            return response()->json(['message'=>self::taskError],500);
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
            return response()->json(['message'=>self::taskError],500);
        }
        $log = Log::addLog(auth()->payload()['user']->id,self::logChangePassword);
        if (is_numeric($log) && $log==0){
            return response()->json(['message'=>self::taskError],500);
        }
        return response()->json(['message'=>self::ok],200);
    }
}
