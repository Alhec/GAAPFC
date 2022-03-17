<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class Advisor extends Model
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
    protected $fillable = ['final_work_id','teacher_id'];

    /**
     *Crea un tutor asociado a un TEG en el sistema
     * @param mixed $advisor Objeto de tipo advisor (contiene los atributos del modelo)
     * @return integer Crea un objeto advisor, si falla devolverá 0.
     */
    public static function addAdvisor($advisor)
    {
        try{
            self::create($advisor);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Elimina todos los tutores asociados a un TEG
     * @param integer $finalWorkId: Id del TEG.
     * @return integer Elimina todos los tutores asociados al finalWork,si falla devolverá 0.
     */
    public static function deleteAllAdvisor($finalWorkId)
    {
        try{
            self::where('final_work_id',$finalWorkId)
                ->delete();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }

    }
}
