<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class SchoolPeriodStudent extends Model
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
    protected $fillable = ['student_id','school_period_id','status','financing','financing_description','pay_ref',
        'amount_paid','inscription_date','test_period'];

    /**
     * Nombre de la tabla asociada
     *
     */
    protected $table = 'school_period_student';

    /**
     *Asociación de la relación schoolPeriod con schoolPeriodStudent
     */
    public function schoolPeriod()
    {
        return $this->belongsTo('App\SchoolPeriod');
    }

    /**
     *Asociación de la relación student con schoolPeriodStudent
     */
    public function student()
    {
        return $this->belongsTo('App\Student')
            ->with('user');
    }

    /**
     *Asociación de la relación studentSubject con schoolPeriodStudent (representa una asignatura inscrita del
     * estudiante en el periodo escolar)
     */
    public function enrolledSubjects()
    {
        return $this->hasMany('App\StudentSubject')
            ->with('dataSubject');
    }

    /**
     *Asociación de la relación finalWork con schoolPeriodStudent (representa un proyecto, seminario, o TEG inscrita
     * del estudiante en el periodo escolar)
     */
    public function finalWorkData()
    {
        return $this->hasMany('App\FinalWorkSchoolPeriod')
            ->with('finalWork');
    }

    /**
     *Asociación de la relación doctoralExam con schoolPeriodStudent (representa el examen doctoral presentado del
     * estudiante en el periodo escolar)
     */
    public function doctoralExam()
    {
        return $this->hasOne('App\DoctoralExam','school_period_student_id','id');
    }

    /**
     *Obtiene todas las inscripciones que se han realizado en una organización
     * @param string $organizationId Id de la organiación
     * @return integer|array Devuelve todas las inscripciones que se han realizado en la organización.
     */
    public static function getSchoolPeriodStudent($organizationId)
    {
        try{
            return self::with('schoolPeriod')
                ->with('student')
               /* ->with('enrolledSubjects')
                ->with('finalWorkData')*/
                ->whereHas('schoolPeriod',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene una inscripcion de periodo escolar dado su id en una organización
     * @param string $id Id de la inscripción
     * @param string $organizationId Id de la organiación
     * @return SchoolPeriodStudent|integer Devuelve todas las inscripciones que se han realizado en la organización.
     */
    public static function getSchoolPeriodStudentById($id,$organizationId)
    {
        try{
            return self::where('id',$id)
                ->with('student')
                ->with('enrolledSubjects')
                ->with('schoolPeriod')
                ->with('finalWorkData')
                ->with('doctoralExam')
                ->whereHas('schoolPeriod',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene todas las inscripcion de un periodo escolar dado el id del periodo escolar en una organización
     * @param string $schoolPeriodId Id del periodo escolar
     * @param string $organizationId Id de la organiación
     * @return SchoolPeriodStudent|integer Devuelve todas las inscripciones asociadas al periodo escolar que se han
     * realizado en la organización.
     */
    public static function getSchoolPeriodStudentBySchoolPeriod($schoolPeriodId,$organizationId)
    {
        try{
            return self::where('school_period_id',$schoolPeriodId)
                ->with('student')
                ->with('enrolledSubjects')
                ->with('schoolPeriod')
                ->with('finalWorkData')
                ->whereHas('schoolPeriod',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si existe una inscripcion de un estudiante en un periodo escolar
     * @param string $studentId: Id del estudiante
     * @param string $schoolPeriodId: Id del periodo escolar
     * @return bool|integer Verifica si existe una inscripción de un estudiante en un periodo escolar, de existir
     * retornara true, de lo contrario será false.
     */
    public static function existSchoolPeriodStudent($studentId,$schoolPeriodId)
    {
        try{
            return self::where('student_id',$studentId)
                ->where('school_period_id',$schoolPeriodId)
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Crea una inscripcion en el periodo escolar
     * @param mixed $schoolPeriodStudent: Objeto de tipo schoolPeriodStudent (contiene los atributos del modelo)
     * @return integer Agrega la inscripción de un estudiante al periodo escolar y devuelve su id, de fallar devolverá 0.
     */
    public static function addSchoolPeriodStudent($schoolPeriodStudent){
        try{
            return self::insertGetId($schoolPeriodStudent->only('student_id','school_period_id','status','financing',
                'financing_description','pay_ref','amount_paid','test_period','inscription_date'));
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Crea una inscripcion en el periodo escolar
     * @param mixed $schoolPeriodStudent: Array con los atributos de un objeto de tipo schoolPeriodStudent
     * @return integer Agrega la inscripción de un estudiante al periodo escolar, de fallar devolverá 0, es usado por
     * una tarea programada para actualizar los estatus de los estudiantes que no se inscribieron en el  perido escolar.
     */
    public static function addSchoolPeriodStudentLikeArray($schoolPeriodStudent){
        try{
            return self::create($schoolPeriodStudent);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Valida si existe una inscripcion dado su id
     * @param string $id: Id de la inscripción
     * @param string $organizationId: Id de la organización
     * @return bool|integer Verifica si existe una inscripción dado su id, de existir retornara true, de lo contrario
     * será false.
     */
    public static function existSchoolPeriodStudentById($id,$organizationId){
        try{
            return self::where('id',$id)
                ->whereHas('schoolPeriod',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Elimina una inscripcion dado su id
     * @param integer $id Id de la inscripcion
     * @return integer Elimina una inscripción dado su id, de fallar devolverá 0.
     */
    public static function deleteSchoolPeriodStudent($id)
    {
        try{
            self::find($id)
                ->delete();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Obtiene la inscripcion de un estudiante en un periodo escolar
     * @param string $studentId Id del estudiante
     * @param string $schoolPeriodId Id del periodo escolar
     * @return SchoolPeriodStudent|integer Obtiene la inscripción de un estudiante en un periodo escolar.
     */
    public static function findSchoolPeriodStudent($studentId,$schoolPeriodId)
    {
        try{
            return self::where('student_id',$studentId)
                ->where('school_period_id',$schoolPeriodId)
                ->with('schoolPeriod')
                ->with('enrolledSubjects')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Actualiza la inscripcion de u nestudiante
     * @param integer $id Id de la inscripción
     * @param mixed $schoolPeriodStudent Objeto de tipo schoolPeriodStudent (contiene los atributos del modelo)
     * @return integer Actualiza la inscripción de un estudiante al periodo escolar, de fallar devolverá 0.
     */
    public static function updateSchoolPeriodStudent($id,$schoolPeriodStudent)
    {
        try{
            self::find($id)
                ->update($schoolPeriodStudent->all());
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Actualiza la inscripcion de un estudiante
     * @param integer $id Id de la inscripción
     * @param mixed $schoolPeriodStudent Array con los atributos de un objeto de tipo schoolPeriodStudent
     * @return integer Actualiza la inscripción de un estudiante al periodo escolar, de fallar devolverá 0.
     */
    public static function updateSchoolPeriodStudentLikeArray($id,$schoolPeriodStudent)
    {
        try{
            self::find($id)
                ->update($schoolPeriodStudent);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Valida si existe un periodoescolar en que el estudiante no haya pagado el periodo escolar
     * @param string $studentId: Id del estudiante
     * @return bool|integer Verifica si existe un periodo escolar en el cual el estudiante no pagó su correspondiente
     * periodo escolar, de existir devolverá true de lo contrario será false.
     */
    public static function isThereUnpaidSchoolPeriod($studentId)
    {
        try{
            return self::where('student_id',$studentId)
                ->where('amount_paid',0)
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene todas las inscripcion de un estudiante dado su id en una organización
     * @param string $studentId Id del estudiante
     * @param string $organizationId Id de la organiación
     * @return SchoolPeriodStudent|integer Obtiene toda la trayectoria que tiene un estudiante en el programa escolar
     * ordenado de forma ascendente.
     */
    public static function getEnrolledSchoolPeriodsByStudent($studentId, $organizationId)
    {
        try{
            return self::where('student_id',$studentId)
                ->with('student')
                ->with('enrolledSubjects')
                ->with('schoolPeriod')
                ->with('finalWorkData')
                ->with('doctoralExam')
                ->whereHas('schoolPeriod',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->orderBy('inscription_date','ASC')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene la cantidad de inscripciones de un estudiante dado su id en una organización
     * @param string $studentId Id del estudiante
     * @param string $organizationId Id de la organiación
     * @return integer Obtiene la cantidad de periodos escolares que ha cursado un estudiante.
     */
    public static function getCantEnrolledSchoolPeriodByStudent($studentId,$organizationId)
    {
        try{
            return self::where('student_id',$studentId)
                ->where(function ($query){
                    $query->where('status','REG')
                        ->orWhere('status','RIN-A')
                        ->orWhere('status','RIN-B')
                        ->orWhere('status','REI-A')
                        ->orWhere('status','REI-B');
                })
                ->WhereHas('schoolPeriod',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->count();
        }catch (\Exception $e){
            return 'e';
        }
    }


}
