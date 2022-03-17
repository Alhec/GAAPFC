<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class Teacher extends Model
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
    protected $fillable = ['id','teacher_type','dedication','category','home_institute','country'];

    /**
     *Asociación de la relación user con teacher
     */
    public function user() {
        return $this->belongsTo('App\User','id','id');
    }

    /**
     *Crea una asociacion teacher a un usuario
     * @param mixed $teacher Objeto de tipo profesor (contiene los atributos del modelo)
     * @return integer Crea un profesor con un usuario asociado, si falla devolverá 0
     */
    public static function addTeacher($teacher)
    {
        try{
            self::create($teacher);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Actualiza una entidad teacher dado su id
     * @param integer $userId Id del usuario
     * @param mixed $teacher Objeto de tipo profesor (contiene los atributos del modelo)
     * @return integer Actualiza los datos de un profesor dado su id, si falla devolverá 0
     */
    public static function updateTeacher($userId,$teacher)
    {
        try{
            self::where('id',$userId)
                ->get()[0]
                ->update($teacher);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Elimina un teacher asociado a un usuario
     * @param integer $id Id del usuario
     * @return integer Elimina la entidad teacher asociada a un usuario, si falla devolverá 0
     */
    public static function deleteTeacher($id)
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
