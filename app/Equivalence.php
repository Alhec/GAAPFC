<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class Equivalence extends Model
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
    protected $fillable = ['student_id','subject_id','qualification'];

    /**
     * Los atributos que deben ocultarse para los Array.
     *
     * @var array
     */
    protected $hidden = ['student_id'];

    /**
     *Asociación de la relación subject con equivalence
     */
    public function subject() {
        return $this->belongsTo('App\Subject');
    }

    /**
     *Crea una asociacion equivalence a un estudiante
     * @param mixed $equivalence: Objeto de tipo equivalence (contiene los atributos del modelo)
     * @return integer Crea una equivalencia con un estudiante y asignatura asociados, si falla devolverá 0.
     */
    public static function addEquivalence($equivalence)
    {
        try{
            self::create($equivalence);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Elimina todos los equivalence asociado a un estudiante
     * @param integer $studentId: Id del estudiante
     * @return integer Elimina todas las equivalencias asociadas al id del estudiante, si falla devolverá 0.
     */
    public static function deleteEquivalence($studentId)
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
