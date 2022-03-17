<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class Student extends Model
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
    protected $fillable = ['school_program_id','user_id','guide_teacher_id','student_type','home_university',
        'current_postgraduate','type_income','is_ucv_teacher','is_available_final_work','credits_granted','with_work',
        'end_program','test_period','current_status','allow_post_inscription'];

    /**
     *Asociación de la relación user con student
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     *Asociación de la relación degrees con student
     */
    public function degrees()
    {
        return $this->hasMany('App\Degree');
    }

    /**
     *Asociación de la relación teacher con student
     */
    public function guideTeacher()
    {
        return $this->hasOne('App\Teacher','id','guide_teacher_id')
            ->with('User');
    }

    /**
     *Asociación de la relación equivalence con student
     */
    public function equivalence()
    {
        return $this->hasMany('App\Equivalence')
            ->with('subject');
    }

    /**
     *Asociación de la relación schoolProgram con student
     */
    public function schoolProgram()
    {
        return $this->belongsTo('App\SchoolProgram');
    }

    /**
     *Asociación de la relación schoolPeriod con student
     */
    public function schoolPeriod()
    {
        return $this->hasMany('App\SchoolPeriodStudent')
            ->with('schoolPeriod')
            ->with('enrolledSubjects')
            ->with('finalWorkData');
    }

    /**
     *Crea una asociacion student a un usuario
     * @param mixed $student Objeto de tipo student (contiene los atributos del modelo)
     * @return integer Agrega un estudiante asociado al usuario y devuelve un id, si falla devolverá 0
     */
    public static function addStudent($student)
    {
        try{
            return self::insertGetId($student);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Actualiza una entidad student dado su id
     * @param integer $studentId Id del estudiante
     * @param mixed $student Objeto de tipo student (contiene los atributos del modelo)
     * @return integer Edita un estudiante dado su id, si falla devolverá 0
     */
    public static function updateStudent($studentId,$student)
    {
        try{
            self::where('id',$studentId)
                ->get()[0]
                ->update($student);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Valida si existe un estudiante dado su id en una organización
     * @param string $id Id del estudiante
     * @param string $organizationId Id de la organiación
     * @return bool|integer Devuelve un booleano y verifica si el id de un estudiante está en la base de datos,
     * si falla devolverá 0
     */
    public static function existStudentById($id,$organizationId)
    {
        try{
            return self::where('id',$id)
                ->whereHas('user',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene un estudiante dado su id en una organización
     * @param string $id Id del estudiante
     * @param string $organizationId Id de la organiación
     * @return Student|integer Obtiene los datos de un usuario asociado a una organización, si falla devolverá 0
     */
    public static function getStudentById($id,$organizationId)
    {
        try{
            return self::where('id',$id)
                ->whereHas('user',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->with('user')
                ->with('degrees')
                ->with('equivalence')
                ->with('guideTeacher')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si existe un estudiante dado su user id en una organización esta cursando un programa conducente a grado
     * @param string $userId Id del usuario asociado al estudiante
     * @return bool|integer Verifica si un estudiante está cursando un programa que otorgue grado, retorna un booleano,
     * si falla devolverá 0.
     */
    public static function studentHasProgram($userId)
    {
        try{
           return self::where('user_id',$userId)
               ->whereHas('schoolProgram',function (Builder $query){
                   $query
                       ->where('conducive_to_degree','=',true);
               })
               ->where('end_program',false)
               ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si existe un estudiante dado su user id en un programa escolar
     * @param string $userId Id del usuario asociado al estudiante
     * @param string $programId Id del programa escolar
     * @return bool|integer Verifica si un estudiante se encuentra en el programa dado sus id devuelve un booleano true
     * en caso de estar en el programa de lo contrario será false, si falla devolverá 0
     */
    public static function existStudentInProgram($userId,$programId)
    {
        try{
            return self::where('user_id',$userId)
                ->where('school_program_id',$programId)
                ->where('current_status','!=','RET-B')
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene la entidad estudiante que no haya terminado su programa escolar aun
     * @param string $userId Id del usuario
     * @return Student|integer Devuelve al estudiante que no haya terminado el programa escolar dado su userId,
     * si falla devolverá 0
     */
    public static function activeStudentId($userId)
    {
        try{
            return self::where('user_id',$userId)
                ->where('end_program',false)
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Elimina una entidad estudiante asociada a un usuario en el sistema
     * @param integer $userId Id del usuario asociado a la entidad estudiante
     * @param integer $studentId Id del estudiante
     * @return integer Elimina un estudiante dado su id y userId, si falla devolverá 0
     */
    public static function deleteStudent($userId,$studentId)
    {
        try{
            self::where('user_id',$userId)
                ->where('id',$studentId)
                ->delete();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene los estudiantes de una organización que se encuentran en un estatus de incidencia
     * @param string $organizationId Id de la organiación
     * @return Student|integer Devuelve los estudiantes con incidencias de acuerdo a la organización, si falla devolverá 0
     */
    public static function warningStudent($organizationId)
    {
        try{
            return self::whereHas('user',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->where('current_status','DES-A')
                ->orWhere('current_status','DES-B')
                ->orWhere('current_status','RET-A')
                ->orWhere('current_status','RET-B')
                ->orWhere('test_period',true)
                ->with('user')
                ->with('degrees')
                ->with('equivalence')
                ->with('guideTeacher')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene los estudiante de una organización que se encuentran en un programa conducente a grado
     * @param string $organizationId Id de la organiación
     * @return Student|integer Obtiene todos los estudiantes activos que están anexados a programas conducentes a grado,
     * dado una organización, si falla devolverá 0
     */
    public static function getAllStudentToDegree($organizationId)
    {
        try{
            return self::whereHas('user',function (Builder $query) use ($organizationId){
                $query
                    ->where('organization_id','=',$organizationId);
                })
                ->whereHas('schoolProgram',function (Builder $query) {
                    $query
                        ->where('conducive_to_degree','=',true);
                })
                ->where('end_program',false)
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene los estudiantes de una organización
     * @param string $organizationId Id de la organiación
     * @return Student|integer Obtiene todos los estudiantes de una organización con todas las relaciones que esta
     * entidad tiene asociada
     */
    public static function getAllStudent($organizationId)
    {
        try{
            return self::whereHas('user',function (Builder $query) use ($organizationId){
                $query
                    ->where('organization_id','=',$organizationId);
                })
                ->with('user')
                ->with('equivalence')
                ->with('schoolPeriod')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }


    /**
     *Obtiene los estudiante de una organización que se encuentran en un programa no conducente a grado
     * @param string $organizationId Id de la organiación
     * @return Student|integer Obtiene todos los estudiantes activos que están anexados a programas que no son
     * conducentes a grado, dado una organización, si falla devolverá 0
     */
    public static function getAllStudentToNotDegree($organizationId)
    {
        try{
            return self::whereHas('user',function (Builder $query) use ($organizationId){
                $query
                    ->where('organization_id','=',$organizationId);
                })
                ->whereHas('schoolProgram',function (Builder $query) {
                    $query
                        ->where('conducive_to_degree','=',false);
                })
                ->with('user')
                ->with('schoolPeriod')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Elimina las entidades estudiantes asociadas a un usuario
     * @param integer $id Id del usuario
     * @return integer Elimina las entidades estudiantes asociadas a un usuario, si falla devolverá 0
     */
    public static function deleteStudentsByUserId($id)
    {
        try{
            self::where('user_id',$id)
                ->delete();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }
}
