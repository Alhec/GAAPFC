<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class Log extends Model
{
    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = ['user_id','log_description'];

    /**
     * Habilitar solo fecha de creacion
     */
    public $timestamps = ["created_at"]; //only want to used created_at column

    /**
     * Setear solo fecha de actualizacion en null
     */
    const UPDATED_AT = null; //and updated by default null set

    /**
     *Crea un log asociado a una actividad de un usuario
     * @param integer $userId id del usuario
     * @param string $logDescription Descripcion de la actividad
     * @return integer Crea un log de actividad asociado a un usuario, si falla devolverá 0
     */
    public static function addLog($userId,$logDescription)
    {
        try{
            return self::create([
                'user_id'=>$userId,
                'log_description'=>$logDescription
            ]);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }
}
