<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class Roles extends Model
{
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
    protected $fillable = ['user_id','user_type'];

    /**
     *Asociación de la relación user con roles
     */
    public function user() {
        return $this->belongsTo('App\User','id','user_id');
    }

    /**
     *Crea una asociacion de rol con un usuario
     * @param array $rol: objeto de tipo rol
     * @return integer Asociar rol a un usuario.
     */
    public static function addRol($rol)
    {
        try{
            self::create($rol);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Elimina un rol asociado a un usuario
     * @param integer $userId Id del usuario
     * @param integer $userType: Tipo de rol
     * @return integer Elimina un rol asociado a un usuario, si falla devolverá 0.
     */
    public static function deleteRol($userId,$userType)
    {
        try{
            self::where('user_id',$userId)
                ->where('user_type',$userType)
                ->delete();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }

    }
}
