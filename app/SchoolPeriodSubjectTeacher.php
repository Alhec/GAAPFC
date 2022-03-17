<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class SchoolPeriodSubjectTeacher extends Model
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
    protected $fillable = ['teacher_id','subject_id','school_period_id','limit','enrolled_students','duty','modality',
        'start_date','end_date'];

    /**
     * Nombre de la tabla asociada
     *
     */
    protected $table = 'school_period_subject_teacher';

    /**
     *Asociación de la relación subjects con SchoolPeriodSubjectTeacher
     */
    public function subject()
    {
        return $this->belongsTo('App\Subject');
    }

    /**
     *Asociación de la relación teacher con SchoolPeriodSubjectTeacher
     */
    public function teacher()
    {
        return $this->belongsTo('App\Teacher')->with('user');
    }

    /**
     *Asociación de la relación schedules con SchoolPeriodSubjectTeacher
     */
    public function schedules()
    {
        return $this->hasMany('App\Schedule');
    }

    /**
     *Asociación de la relación schoolPeriod con SchoolPeriodSubjectTeacher
     */
    public function schoolPeriod()
    {
        return $this->belongsTo('App\SchoolPeriod');
    }

    /**
     *Crea una relacion SchoolPeriodSubjectTeacher que representa una asignatura con un profesor en un periodo escolar
     * @param mixed $schoolPeriodSubjectTeacher: Objeto de tipo schoolPeriodSubjectTeacher (contiene los atributos del
     * modelo)
     * @return integer Agrega un objeto relación entre asignatura, periodo escolar y profesor, retorna el id del objeto,
     * de fallar devolverá 0.
     */
    public static function addSchoolPeriodSubjectTeacher($schoolPeriodSubjectTeacher)
    {
        try{
            unset($schoolPeriodSubjectTeacher['schedules']);
            return self::insertGetId($schoolPeriodSubjectTeacher);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Elimina todas las relaciones de SchoolPeriodSubjectTeacher que representan asignaturas en un periodo escolar dado
     * el id del periodo escolar
     * @param integer $schoolPeriodId: Id del periodo escolar
     * @return integer Elimina todos los objetos de relación que tengan asociado el id del programa escolar dado, de
     * fallar devolverá 0.
     */
    public static function  deleteSchoolPeriodSubjectTeacherBySchoolPeriod($schoolPeriodId)
    {
        try{
            self::where('school_period_id',$schoolPeriodId)
                ->delete();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }

    }

    /**
     *Obtiene todas las relaciones de SchoolPeriodSubjectTeacher que representan asignaturas en un periodo escolar dado
     * el id del periodo escolar
     * @param string $schoolPeriodId Id del periodo escolar.
     * @return SchoolPeriodSubjectTeacher|integer Obtiene los objetos schoolPeriodSubjectTeacher que tengan asociado el
     * id del programa escolar dado.
     */
    public static function getSchoolPeriodSubjectTeacherBySchoolPeriod($schoolPeriodId)
    {
        try{
            return self::where('school_period_id',$schoolPeriodId)
                ->with('subject')
                ->with('teacher')
                ->with('schedules')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Actualiza una relacion SchoolPeriodSubjectTeacher que representa una asignatura con un profesor en un periodo
     * escolar dado su id
     * @param integer $id Id del objeto schoolPeriodSubjectTeacher
     * @param mixed $schoolPeriodSubjectTeacher: Objeto de tipo schoolPeriodSubjectTeacher (contiene los atributos del
     * modelo)
     * @return integer Edita un objeto SchoolPeriodSubjectTeacher dado su id, de fallar devolverá 0.
     */
    public static function updateSchoolPeriodSubjectTeacher($id,$schoolPeriodSubjectTeacher)
    {
        try{
            self::find($id)
                ->update($schoolPeriodSubjectTeacher);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Obtiene el id de una relacion SchoolPeriodSubjectTeacher
     * @param string $schoolPeriodId Id del periodo escolar
     * @param string $subjectId: Id de la asignatura
     * @param string $teacherId: Id del profesor
     * @return SchoolPeriodSubjectTeacher|integer Obtiene el id del objeto que tiene asociado al id del profesor,
     * maestro y programa escolar dados.
     */
    public static function findSchoolPeriodSubjectTeacherId($schoolPeriodId,$subjectId,$teacherId)
    {
        try{
            return self::where('school_period_id',$schoolPeriodId)
                ->where('subject_id',$subjectId)
                ->where('teacher_id',$teacherId)
                ->get('id');
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si existe alguna relacion de schoolPeriodSubjectTeacher dado el id de un periodo escolar
     * @param integer $schoolPeriodId: Id del periodo escolar
     * @return bool|integer Verifica si existe un objeto schoolPeriodSubjectTeacher con el id del periodo escolar dado
     * de existir devolverá true de lo contrario será false.
     */
    public static function existSchoolPeriodSubjectTeacherBySchoolPeriodId($schoolPeriodId)
    {
        try{
            return self::where('school_period_id',$schoolPeriodId)
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Elimina una relacion SchoolPeriodSubjectTeacher dado su id
     * @param integer $id: Id del objeto schoolPeriodSubjectTeacher
     * @return integer Elimina un objeto schoolPeriodSubjectTeacher dado su id, de fallar devolverá 0.
     */
    public static function deleteSchoolPeriodSubjectTeacher($id)
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
     *Actualiza la cantidad de estudiantes inscritos en una relacion SchoolPeriodSubjectTeacher que representa una
     * asignatura con un profesor en un periodo escolar dado su id
     * @param integer $id Id del objeto schoolPeriodSubjectTeacher
     * @return integer Actualiza la cantidad de estudiantes inscritos en una asignatura, de fallar devolverá 0.
     */
    public static function updateEnrolledStudent($id)
    {
        try{
            $schoolPeriodSubjectTeacher = self::where('id',$id)
                ->get();
            if (is_numeric($schoolPeriodSubjectTeacher)&&$schoolPeriodSubjectTeacher==0){
                return 0;
            }
            $schoolPeriodSubjectTeacher = $schoolPeriodSubjectTeacher->toArray();
            $studentInSubject=StudentSubject::studentSubjectBySchoolPeriodSubjectTeacherId($id);
            if (is_numeric($studentInSubject)&&$studentInSubject==0){
                return 0;
            }
            $schoolPeriodSubjectTeacher[0]['enrolled_students']= count($studentInSubject);
            $result = self::updateSchoolPeriodSubjectTeacher($id,$schoolPeriodSubjectTeacher[0]);
            if (is_numeric($result)&& $result==0){
                return 0;
            }
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Obtiene todas las relaciones de SchoolPeriodSubjectTeacher que representan asignaturas en un periodo escolar dado
     * el id del periodo escolar y el id del profesor
     * @param string $teacherId Id del profesor.
     * @param string $schoolPeriodId Id del periodo escolar.
     * @return SchoolPeriodSubjectTeacher|integer Obtiene las asignaturas que dicta un profesor en un periodo escolar.
     */
    public static function getSchoolPeriodSubjectTeacherBySchoolPeriodTeacher($teacherId,$schoolPeriodId)
    {
        try{
            return self::where('school_period_id',$schoolPeriodId)
                ->where('teacher_id',$teacherId)
                ->with('subject')
                ->with('schedules')
                ->get();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Valida si existe alguna relacion de schoolPeriodSubjectTeacher dado su id
     * @param integer $id: Id del objeto schoolPeriodSubjectTeacher
     * @return bool|integer Verifica que exista un objeto schoolPeriodSubjectTeacher con el id dado, de existir
     * devolverá true, de lo contrario será false.
     */
    public static function existSchoolPeriodSubjectTeacherById($id)
    {
        try{
            return self::where('id',$id)
                ->exists();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Obtiene una relacion schoolPeriodSubjectTeacher dado su id
     * @param string $id: Id del objeto schoolPeriodSubjectTeacher
     * @return SchoolPeriod|integer Obtiene un objeto schoolPeriodSubjectTeacher con el id dado.
     */
    public static function getSchoolPeriodSubjectTeacherById($id)
    {
        try{
            return self::where('id',$id)
                ->get();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

}
