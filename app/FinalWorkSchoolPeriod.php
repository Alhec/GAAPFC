<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class FinalWorkSchoolPeriod extends Model
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
    protected $fillable = ['status','description_status','final_work_id','school_period_student_id'];

    /**
     * Nombre de la tabla asociada
     *
     */
    protected $table = 'final_work_school_period';

    /**
     *Asociación de la relación finalWork con finalWorkSchoolPeriod
     */
    public function finalWork()
    {
        return $this->belongsTo('App\FinalWork')
            ->with('teachers')
            ->with('subject');
    }

    /**
     *Crea una relacion finalWorkSchoolPeriod en el sistema
     * @param mixed $finalWorkSchoolPeriod Objeto de tipo finalWorkSchoolPeriod (contiene los atributos del modelo)
     * @return integer Crea un objeto finalWorkSchoolPeriod, si falla devolverá 0.
     */
    public static function addFinalWorkSchoolPeriod($finalWorkSchoolPeriod)
    {
        try{
            return self::create($finalWorkSchoolPeriod);
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Actualiza una relacion finalWorkSchoolPeriod
     * @param integer $id Id del finalWorkSchoolPeriod
     * @param mixed $finalWorkSchoolPeriod: Objeto de tipo finalWorkSchoolPeriod (contiene los atributos del modelo)
     * @return integer Actualiza un finalWorkSchoolPeriod dado su id, si falla devolverá 0.
     */
    public static function updateFinalWorkSchoolPeriod($id,$finalWorkSchoolPeriod)
    {
        try{
            return self::find($id)
                ->update($finalWorkSchoolPeriod);
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si existe una aociacion finalWorkSchoolPeriod dado el id de la inscripcion del estudiante en un periodo
     * escolar
     * @param string $schoolPeriodStudentId: Id del objeto SchoolPeriodStudent que representa la inscripción de un
     * estudiante al periodo escolar
     * @return bool|integer Valida si existe un proyecto o trabajo final asociado a la inscripción del estudiante en el
     * semestre, de existir devolverá true, de lo contrario será false.
     */
    public static function existFinalWorkSchoolPeriodBySchoolPeriodStudent($schoolPeriodStudentId){
        try{
            return self::where('school_period_student_id',$schoolPeriodStudentId)
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene  los finalWork inscritos por el estudiante en un periodo escolar
     * @param string $schoolPeriodStudentId: Id del objeto SchoolPeriodStudent que representa la inscripción de un
     * estudiante al periodo escolar
     * @return FinalWorkSchoolPeriod|integer Obtiene los proyectos o trabajos de grado inscritos en un periodo escolar
     * que inscribio un estudiante.
     */
    public static function getFinalWorkSchoolPeriodBySchoolPeriodStudentId($schoolPeriodStudentId){
        try{
            return self::where('school_period_student_id',$schoolPeriodStudentId)
                ->with('finalWork')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Elimina una relacion FinalWorkSchoolPeriod dado su id
     * @param integer $id Id del FinalWorkSchoolPeriod
     * @return integer Elimina una relacion de proyecto o trabajo de grado en un periodo escolar dado su id, de fallar
     * devolverá 0.
     */
    public static function deleteFinalWorkSchoolPeriod($id){
        try{
            self::find($id)
                ->delete();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Elimina una  relacion FinalWorkSchoolPeriod dado el id de la inscripcion del estudiante
     * @param integer $schoolPeriodStudentId: Id del objeto SchoolPeriodStudent que representa la inscripción de un
     * estudiante al periodo escolar
     * @return integer Elimina un(os) FinalWorkSchoolPeriod dado un schoolPeriodStudentId, si falla devolverá 0.
     */
    public static function deleteFinalWorkSchoolPeriodBySchoolPeriodStudentId($schoolPeriodStudentId){
        try{
            self::where('school_period_student_id',$schoolPeriodStudentId)
                ->delete();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }
}
