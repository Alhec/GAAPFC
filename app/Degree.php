<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Self_;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class Degree extends Model
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
    protected $fillable = ['student_id','degree_obtained','degree_name','degree_description','university'];

    /**
     * Los atributos que deben ocultarse para los Array.
     *
     * @var array
     */
    protected $hidden = ['student_id'];

    /**
     * Definicion de clave primaria
     *
     */
    protected $primaryKey = 'student_id';

    /**
     *Crea una asociacion degree a un estudiante
     * @param mixed $degree Objeto de tipo degree (contiene los atributos del modelo)
     * @return integer Crea un grado con un estudiante asociado, si falla devolverá 0.
     */
    public static function addDegree($degree)
    {
        try{
            self::create($degree);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Elimina todos los degree asociado a un estudiante
     * @param integer $studentId: Id del estudiante
     * @return integer Elimina todos los grados asociados al id del estudiante, si falla devolverá 0.
     */
    public static function deleteDegree($studentId)
    {
        try{
            self::where('student_id',$studentId)
                ->delete();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }
}
