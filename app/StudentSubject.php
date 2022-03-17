<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class StudentSubject extends Model
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
    protected $fillable = ['school_period_student_id','school_period_subject_teacher_id','qualification','status'];

    /**
     * Nombre de la tabla asociada
     *
     */
    protected $table = 'student_subject';

    /**
     *Asociación de la relación SchoolPeriodStudent con studentSubject (representa la inscripcion del estudiante en el
     * periodo escolar)
     */
    public function dataStudent()
    {
        return $this->belongsTo('App\SchoolPeriodStudent','school_period_student_id','id')
            ->with('student');
    }

    /**
     *Asociación de la relación SchoolPeriodSubjectTeacher con studentSubject (representa la asignatura asociada al periodo
     * escolar que el estudiante esta inscribiendo)
     */
    public function dataSubject()
    {
        return $this->belongsTo('App\SchoolPeriodSubjectTeacher','school_period_subject_teacher_id','id')
            ->with('subject')
            ->with('schedules')
            ->with('teacher');
    }

    /**
     *Obtiene todas las asignaturas inscritas con excepcion de las que han sido retiradas
     * @param string $studentId: Id del estudiante
     * @return integer|StudentSubject Devuelve todas las asignaturas que inscribió el estudiante dado su id con
     * excepción de las que ha retirado.
     */
    public static function getAllSubjectsEnrolledWithoutRET($studentId)
    {
        try{
            return self::where('status','!=','RET')
                ->with('dataSubject')
                ->whereHas('dataStudent',function (Builder $query) use ($studentId){
                    $query
                        ->where('student_id','=',$studentId);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene todas las asignaturas inscritas con excepcion de las que han sido retiradas o estan siendo cursadas
     * @param string $studentId: Id del estudiante
     * @return integer|StudentSubject Devuelve todas las asignaturas que inscribió el estudiante dado su id con excepción
     * de las que ha retirado o las que está cursando.
     */
    public static function getAllSubjectsEnrolledWithoutRETCUR($studentId)
    {
        try{
            return self::where('status','!=','RET')
                ->where('status','!=','CUR')
                ->with('dataSubject')
                ->whereHas('dataStudent',function (Builder $query) use ($studentId){
                    $query
                        ->where('student_id','=',$studentId);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene la cantidad de asignaturas inscritas con excepcion de las que han sido retiradas o estan siendo cursadas
     * @param string $studentId Id del estudiante
     * @return integer Devuelve la cantidad de asignaturas que inscribió el estudiante dado su id con excepción de las
     * que ha retirado o las que está cursando.
     */
    public static function cantAllSubjectsEnrolledWithoutRETCUR($studentId)
    {
        try{
            return self::where('status','!=','RET')
                ->where('status','!=','CUR')
                ->whereHas('dataStudent',function (Builder $query) use ($studentId){
                    $query
                        ->where('student_id','=',$studentId);
                })
                ->count();
        }catch (\Exception $e){
            return 'e';//retorna e porque esta ocasion en un caso correcto puede devolver 0
        }
    }

    /**
     *Obtiene todas las asignaturas inscritas de un estudiante en un periodo escolar
     * @param string $studentId: Id del estudiante
     * @param string $schoolPeriodId: Id del periodo escolar
     * @return integer|array Obtiene las asignaturas que tiene inscritas un estudiante dado un periodo escolar.
     */
    public static function getEnrolledSubjectsBySchoolPeriodStudent($studentId,$schoolPeriodId)
    {
        try{
            return self::with('dataSubject')
                ->whereHas('dataStudent',function (Builder $query) use ($studentId,$schoolPeriodId){
                    $query
                        ->where('student_id','=',$studentId)
                        ->where('school_period_id','=',$schoolPeriodId);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Crea una inscripcion de un estudiante en una asignatura de un periodo escolar
     * @param mixed $studentSubject: Objeto de tipo studentSubject (contiene los atributos del modelo)
     * @return integer Agrega la inscripción de una asignatura en el periodo escolar de un estudiante, de fallar retorna
     * 0.
     */
    public static function addStudentSubject($studentSubject)
    {
        try{
            self::create($studentSubject);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Obtiene todas las inscripciones que se realizaron a una asignatura del periodo escolar
     * @param string $schoolPeriodSubjectTeacherId: Id de la relacion entre las asignaturas del periodo escolar
     * @return integer|StudentSubject Devuelve los estudiantes inscritos en una asignatura con el id de relación del
     * periodo escolar.
     */
    public static function studentSubjectBySchoolPeriodSubjectTeacherId($schoolPeriodSubjectTeacherId)
    {
        try{
            return self::where('school_period_subject_teacher_id',$schoolPeriodSubjectTeacherId)
                ->with('dataStudent')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene todas las asignaturas que inscribio un estudiante en un periodo escolar
     * @param string $schoolPeriodStudentId: Id de la relación entre el estudiante y el periodo escolar
     * @return integer|StudentSubject Obtiene las asignaturas que cursa un estudiante en un periodo escolar.
     */
    public static function studentSubjectBySchoolPeriodStudent($schoolPeriodStudentId)
    {
        try{
            return self::where('school_period_student_id',$schoolPeriodStudentId)
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene el id de una inscripcion de asignatura de un estudiante
     * @param string $schoolPeriodStudentId: Id de la relación entre el estudiante y el periodo escolar
     * @param string $schoolPeriodSubjectTeacherId: Id de la relacion entre las asignaturas del periodo escolar
     * @return SchoolPeriodStudent|integer Obtiene el id de la relacion entre schoolPeriodStudent y
     * SchoolPeriodSubjectTeacher.
     */
    public  static function findStudentSubjectId($schoolPeriodStudentId, $schoolPeriodSubjectTeacherId)
    {
        try{
            return self::where('school_period_student_id',$schoolPeriodStudentId)
                ->where('school_period_subject_teacher_id',$schoolPeriodSubjectTeacherId)
                ->get('id');
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Actualiza la inscripcion de un estudiante en una asignatura
     * @param integer $id Id de la inscripción de la asignatura en el periodo escolar
     * @param mixed $studentSubject: Objeto de tipo studentSubject (contiene los atributos del modelo)
     * @return integer Actualiza la inscripción de una asignatura en el periodo escolar de un estudiante dado su id, de
     * fallar devolverá 0.
     */
    public static function updateStudentSubject($id,$studentSubject)
    {
        try{
            self::find($id)
                ->update($studentSubject);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Elimina una inscripcion de una asignatura dado su id
     * @param integer $id Id de la inscripción de la asignatura en el periodo escolar
     * @return integer Elimina una asignatura de la inscripción de un estudiante dado su id de fallar devolverá 0.
     */
    public static function deleteStudentSubject($id)
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
     *Elimina todas las inscripciones de asignatura que realizo un estudiante en un periodo escolar
     * @param integer $schoolPeriodStudentId: Id de la inscripción del estudiante en el periodo escolar
     * @return integer Elimina todas las asignaturas inscritas asociadas al id de la inscripción de un estudiante dado
     * su id de fallar devolverá 0.
     */
    public static function deleteStudentSubjectBySchoolPeriodStudentId($schoolPeriodStudentId)
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
     *Obtiene una asignatura inscrita por un estudiante dado el id de esa asignatura inscrita
     * @param string $id Id de la inscripción de la asignatura en el periodo escolar
     * @return SchoolPeriodStudent|integer Obtiene la asignatura inscrita de un estudiante dado su id.
     */
    public static function getStudentSubjectById($id)
    {
        try{
            return self::where('id',$id)
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }
}
