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
class DoctoralExam extends Model
{
    /**
     * Habilita el uso del campo created_at
     *
     */
    public $timestamps = ["created_at"];

    /**
     * Coloca por defecto null el campo
     *
     */
    const UPDATED_AT = null;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = ['school_period_student_id','status'];

    /**
     * Definicion de clave primaria
     *
     */
    protected $primaryKey = 'school_period_student_id';

    /**
     *Asociación de la relación SchoolPeriodStudent con DoctoralExam
     */
    public function inscription()
    {
        return $this->belongsTo('App\SchoolPeriodStudent','school_period_student_id','id');
    }

    /**
     *Crea un examen doctoral asociado a un periodo escolar inscrito por el estudiante
     * @param integer $schoolPeriodStudentId: Id de la inscripción del estudiante
     * @param integer $status: Estatus del examen doctoral
     * @return integer Crea un examen doctoral, si falla devolverá 0.
     */
    public static function addDoctoralExam($schoolPeriodStudentId,$status)
    {
        try{
             self::create([
                'school_period_student_id'=>$schoolPeriodStudentId,
                'status'=>$status
            ]);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Elimina un examen doctoral dado el id del periodo escolar inscrito por el estudiante
     * @param integer $schoolPeriodStudentId: Id de la inscripción del estudiante
     * @return integer Elimina un examen doctoral de un periodo escolar, si falla devolverá 0.
     */
    public static function deleteDoctoralExam($schoolPeriodStudentId)
    {
        try{
            self::where('school_period_student_id',$schoolPeriodStudentId)
                ->delete();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Valida si existe un examen doctoral aprobado dado un estudiante en un periodo escolar
     * @param string $studentId: Id del estudiante
     * @param string $schoolPeriodId: Id del periodo escolar
     * @return bool|integer Valida si existe un examen doctoral aprobado dado el id de un estudiante de existir en un
     * periodo escolar diferente al dado devolverá true de lo contrario será false.
     */
    public static function existDoctoralExamApprovedByStudentInNotSchoolPeriod($studentId, $schoolPeriodId)
    {
         try{
            return self::where('status','APPROVED')
                ->whereHas('inscription',function (Builder $query) use ($studentId,$schoolPeriodId){
                $query
                    ->where('student_id','=',$studentId)
                    ->where('school_period_id','!=',$schoolPeriodId);
                })
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene el examen doctoral dado el id de un estudiante
     * @param string $studentId: Id del estudiante
     * @return SchoolPeriodStudent|integer Obtiene todos los exámenes doctorales que ha realizado un estudiante.
     */
    public static function getDoctoralExamByStudent($studentId)
    {
        try{
            return self::whereHas('inscription',function (Builder $query) use ($studentId){
                $query
                    ->where('student_id','=',$studentId);
            })->get();
        }catch (\Exception $e){
            return 0;
        }
    }
}
