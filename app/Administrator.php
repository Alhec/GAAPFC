<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class Administrator extends Model
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
    protected $fillable = ['id','rol','principal'];

    /**
     *Asociación de la relación user con administrator
     */
    public function user() {
        return $this->belongsTo('App\User');
    }

    /**
     *Crea una asociacion administrator a un usuario
     * @param mixed $administrator Objeto de tipo administrator (contiene los atributos del modelo)
     * @return integer Crea un administrador con un usuario asociado, si falla devolverá 0
     */
    public static function addAdministrator($administrator)
    {
        try{
            self::create($administrator);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Actualiza una entidad administrator dado su id
     * @param integer $userId Id del usuario
     * @param mixed $administrator Objeto de tipo administrador (contiene los atributos del modelo)
     * @return integer Actualiza los datos de un administrador dado su id, si falla devolverá 0
     */
    public static function updateAdministrator($userId,$administrator)
    {
        try{
            self::where('id',$userId)
                ->get()[0]
                ->update($administrator);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Obtiene el administrador principal de la organización
     * @param string $organizationId Id de la organiación
     * @return User|integer Obtiene el coordinador principal de una organización, si falla devolverá 0
     */
    public static function getPrincipalCoordinator($organizationId)
    {
        try{
            return User::where('organization_id',$organizationId)
                ->whereHas('roles',function (Builder $query){
                    $query
                        ->where('user_type','=','A');
                })
                ->whereHas('administrator',function (Builder $query){
                    $query
                        ->where('rol','=','COORDINATOR')
                        ->where('principal','=',1);
                })
                ->with('administrator')
                ->with('teacher')
                ->with('student')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Elimina un administrador asociado a un usuario
     * @param integer $id Id del usuario
     * @return integer Elimina la entidad administrator asociada a un usuario, si falla devolverá 0
     */
    public static function deleteAdministrator($id)
    {
        try{
            self::where('id',$id)
                ->delete();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }
}
