<?php

namespace App;

use Illuminate\Notifications\Notifiable;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Builder;
use App\Notifications\CustomResetPassword;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
*/
class User extends Authenticatable implements JWTSubject
{

    /**
     * Usa notificable.
     */
    use Notifiable;

    /**
     * Omite los campos de fecha de creado y modificado en las tablas
     *
     */
    public $timestamps = false;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'identification','first_name','second_name','first_surname','second_surname','telephone', 'mobile','work_phone',
        'email','password','level_instruction','active','with_disabilities','sex','nationality', 'organization_id',
        'level_instruction_name',
    ];

    /**
     * Los atributos que deben ocultarse para los Array.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','email_verified_at'
    ];

    /**
     * Los atributos que se deben convertir en tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Obtiene el identificador que se almacenará en la declaración de asunto del JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        // TODO: Implement getJWTIdentifier() method.
        return $this->getKey();
    }

    /**
     * Devuelve un array de valores clave, que contiene cualquier reclamación personalizada que se añada al JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'user' =>self::getUserByIdWithoutFilterRol($this['id'],$this['organization_id'])[0],
        ];
    }

    /**
     *Asociación de la relación teacher con user
    */
    public function teacher()
    {
        return $this->hasOne('App\Teacher','id','id');
    }

    /**
     *Asociación de la relación student con user
     */
    public function student()
    {
        return $this->hasMany('App\Student')
            ->with('degrees')
            ->with('guideTeacher')
            ->with('equivalence')
            ->with('schoolProgram');
    }

    /**
     *Asociación de la relación roles con user
     */
    public function roles()
    {
        return $this->hasMany('App\Roles','user_id','id');
    }

    /**
     *Asociación de la relación administrator con user
     */
    public function administrator()
    {
        return $this->hasOne('App\Administrator','id','id');
    }

    /**
     *Obtiene los usuarios de una organización
     * @param string $userType Tipos de usuario A,T,S
     * @param string $organizationId Id de la organiación
     * @param integer $perPage Parámetro opcional, cantidad de elementos por página, default:0
     * @return User|integer De acuerdo al tipo de usuario, devuelve todos los usuarios con su relación asociada,
     * si falla devolverá 0
     */
    public static function getUsers($userType,$organizationId,$perPage=0)
    {
        try{
            $users = self::where('organization_id',$organizationId)
                ->whereHas('roles',function (Builder $query) use ($userType){
                    $query
                        ->where('user_type','=',$userType);
                });
            if ($perPage == 0){
                return $users
                    ->with('administrator')
                    ->with('teacher')
                    ->with('student')
                    ->get();
            }else{
                return $users
                    ->with('administrator')
                    ->with('teacher')
                    ->with('student')
                    ->paginate($perPage);
            }
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene un usuario dado su id en una organización
     * @param string $id Id del usuario
     * @param string $userType Tipos de usuario A,T,S
     * @param string $organizationId Id de la organiación
     * @return User|integer De acuerdo al tipo de usuario, devuelve al usuario con su relación asociada,
     * si falla devolverá 0
     */
    public static function getUserById($id,$userType,$organizationId)
    {
        try{
            $user =self::where('id',$id)
                ->where('organization_id',$organizationId)
                ->whereHas('roles',function (Builder $query) use ($userType){
                    $query
                        ->where('user_type','=',$userType);
                });
            return $user
                ->with('administrator')
                ->with('teacher')
                ->with('student')
                ->with('roles')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene un usuario dado su id en una organización
     * @param string $id Id del usuario
     * @param string $organizationId Id de la organiación
     * @return User|integer De acuerdo al id del usuario, devuelve al usuario con su relación asociada,
     * si falla devolverá 0
     */
    public static function getUserByIdWithoutFilterRol($id, $organizationId)
    {
        try{
            $user =self::where('id',$id)
                ->where('organization_id',$organizationId);
            return $user
                ->with('administrator')
                ->with('teacher')
                ->with('student')
                ->with('roles')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si existe un usuario dado su id en una organización
     * @param string $id Id del usuario
     * @param string $userType Tipos de usuario A,T,S
     * @param string $organizationId Id de la organiación
     * @return bool|integer Devuelve true si el usuario existe, false en caso contrario, si falla devolverá 0
     */
    public static function existUserById($id,$userType,$organizationId)
    {
        try{
            return self::where('id',$id)
                ->where('organization_id',$organizationId)
                ->whereHas('roles',function (Builder $query) use ($userType){
                    $query
                        ->where('user_type','=',$userType);
                })
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si existe un usuario dado su id en una organización
     * @param string $id Id del usuario
     * @param string $organizationId Id de la organiación
     * @return bool|integer Devuelve true si el usuario existe, false en caso contrario, si falla devolverá 0
     */
    public static function existUserByIdWithoutFilterRol($id,$organizationId)
    {
        try{
            return self::where('id',$id)
                ->where('organization_id',$organizationId)
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si existe un usuario dado su identicación en una organización
     * @param string $identification identificación del usuario
     * @param string $organizationId Id de la organiación
     * @return bool|integer Dado una identificación y organización devuelve true si el usuario existe de lo contrario
     * será false, si falla devolverá 0
     */
    public static function existUserByIdentification($identification,$organizationId)
    {
        try{
            return self::where('identification',$identification)
                ->where('organization_id',$organizationId)
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si existe un usuario dado su email en una organización
     * @param string $email correo electrónico del usuario
     * @param string $organizationId Id de la organiación
     * @return bool|integer Dado un correo electrónico, tipo de usuario y organización devuelve true si el usuario
     * existe de lo contrario será false, si falla devolverá 0
     */
    public static function existUserByEmail($email,$organizationId)
    {
        try{
            return self::where('email',$email)
                ->where('organization_id',$organizationId)
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Crea un usuario en el sistema
     * @param mixed $user Objeto de tipo usuario (contiene los atributos del modelo)
     * @return integer Agrega un usuario con la data del objeto $user y devuelve el id, si falla devolverá 0
     */
    public static function addUser($user)
    {
        try{
            return self::insertGetId($user->only('identification','first_name','second_name','first_surname',
                'second_surname','telephone','mobile','work_phone','email', 'password','level_instruction',
                'active', 'with_disabilities','sex','nationality','organization_id','level_instruction_name'));
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Elimina un usuario en el sistema
     * @param integer $id Id del usuario
     * @return integer Elimina un usuario dado un id, si falla devolverá 0
     */
    public static function deleteUser($id)
    {
        try{
            self::find($id)
                ->delete();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Obtiene un usuario dado su identificación en una organización
     * @param string $identification identificación del usuario
     * @param string $organizationId Id de la organiación
     * @return User|integer Obtiene un usuario dado una identificación y organización a la que pertenece,
     * si falla devolverá 0
     */
    public static function getUserByIdentification($identification,$organizationId)
    {
        try{
            return self::where('identification',$identification)
                ->where('organization_id',$organizationId)
                ->with('administrator')
                ->with('teacher')
                ->with('student')
                ->with('roles')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene un usuario dado su identificación en una organización
     * @param string $email correo electrónico del usuario
     * @param string $organizationId Id de la organiación
     * @return User|integer Obtiene un usuario dado un correo electrónico y organización a la que pertenece,
     * si falla devolverá 0
     */
    public static function getUserByEmail($email,$organizationId)
    {
        try{
            return self::where('email',$email)
                ->where('organization_id',$organizationId)
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Actualiza un usuario dado su id en el sistema
     * @param integer $id Id del usuario
     * @param mixed $user Objeto de tipo usuario (contiene los atributos del modelo)
     * @return integer Actualiza los datos del id de usuario dado con la data del objeto $user, si falla devolverá 0
     */
    public static function updateUser($id,$user)
    {
        try{
            self::find($id)
                ->update($user->all());
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Actualiza un usuario dado su id en el sistema
     * @param integer $id Id del usuario
     * @param mixed $user Objeto de tipo usuario (contiene los atributos del modelo)
     * @return integer Actualiza los datos del id de usuario dado con la data del array $user, si falla devolverá 0
     */
    public static function updateUserLikeArray($id,$user)
    {
        try{
            self::find($id)
                ->update($user);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }

    }

    /**
     *Obtiene los usuarios activos de una organización
     * @param string $userType Tipos de usuario A,T,S
     * @param string $organizationId Id de la organiación
     * @param integer $perPage Parámetro opcional, cantidad de elementos por página, default:0
     * @return User|integer De acuerdo al tipo de usuario, devuelve todos los usuarios activos con su relación asociada,
     * si falla devolverá 0
     */
    public static function getUsersActive($userType,$organizationId,$perPage=0)
    {
        try{
            $users = self::where('active',true)
                ->where('organization_id',$organizationId)
                ->whereHas('roles',function (Builder $query) use ($userType){
                    $query
                        ->where('user_type','=',$userType);
                });
            if ($perPage == 0){
                return $users
                    ->with('administrator')
                    ->with('teacher')
                    ->with('student')
                    ->get();
            }else{
                return $users
                    ->with('administrator')
                    ->with('teacher')
                    ->with('student')
                    ->paginate($perPage);
            }
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Disparador para generar la recuperación de contraseña.
     * @param string $token Token para resetear clave
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }
}
