<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class Schedule extends Model
{
    /**
     * Primary Key de tipo string
     *
     */
    protected $primaryKey = 'school_period_subject_teacher_id';

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
    protected $fillable = ['school_period_subject_teacher_id','day','classroom','start_hour','end_hour'];

    /**
     * Nombre de la tabla asociada
     *
     */
    protected $table = 'schedules';

    /**
     *Crea un horario asociado a un SchoolPeriodSubjeectTeacher que representa una asignatura en un periodo escolar
     * @param mixed $schedule Objeto de tipo Schedule (contiene los atributos del modelo)
     * @return integer Crea un objeto schedule, si falla devolverá 0.
     */
    public static function addSchedule($schedule)
    {
        try{
            self::create($schedule);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Elimina todos los horarios asociados a una asignatura en un periodo escolar
     * @param integer $schoolPeriodSubjectTeacherId Id del objeto schoolPeriodSubjectTeacher
     * @return integer Elimina todos los horarios asociados al id del objeto schoolPeriodSubjectTeacher, de fallar
     * devolverá 0.
     */
    public static function deleteAllSchedule($schoolPeriodSubjectTeacherId)
    {
        try{
            self::where('school_period_subject_teacher_id',$schoolPeriodSubjectTeacherId)
                ->delete();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }

    }
}
