<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class SchoolProgramSubject extends Model
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
    protected $fillable = ['school_program_id','subject_id','type','subject_group'];

    /**
     * Nombre de la tabla asociada
     *
     */
    protected $table = 'school_program_subject';

    /**
     *Crea una asociacion de asignatura con programa escolar en el sistema
     * @param mixed $schoolProgramSubject: Objeto de tipo schoolProgramSubject (contiene los atributos del modelo)
     * @return integer Crea un objeto de SchoolProgramSubject, si falla devolverá 0.
     */
    public static function addSchoolProgramSubject($schoolProgramSubject)
    {
        try{
            return self::insertGetId($schoolProgramSubject);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Obtiene la asociacion de una asignatura con un programa escolar
     * @param string $subjectId: Id de la asignatura
     * @param string $schoolProgramId: Id del programa escolar
     * @return SchoolProgramSubject|integer Obtiene el objeto SchoolProgramSubject dado un subjectId y schoolProgramId.
     */
    public static function getSchoolProgramSubjectBySubjectAndSchoolProgram($subjectId,$schoolProgramId)
    {
        try{
            return self::where('subject_id',$subjectId)
                ->where('school_program_id',$schoolProgramId)
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene la asociacion de una asignatura con todos sus programas escolares asociados
     * @param string $subjectId: Id de la asignatura
     * @return SchoolProgramSubject|integer Obtiene los programas escolares a los cuales está asociada la asignatura.
     */
    public static function getSchoolProgramSubjectsBySubjectId($subjectId)
    {
        try{
            return self::where('subject_id',$subjectId)
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Actualiza una asociacion de asignatura con programa escolar en el sistema
     * @param integer $id Id del objeto SchoolProgramSubject
     * @param mixed $schoolProgramSubject: Objeto de tipo schoolProgramSubject (contiene los atributos del modelo)
     * @return integer Actualiza un objeto schoolProgramSubject dado su id.
     */
    public static function updateSchoolProgramSubject($id, $schoolProgramSubject)
    {
        self::find($id)
            ->update($schoolProgramSubject);
        try{
            self::find($id)
                ->update($schoolProgramSubject);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Elimina una asociacion entre asignatura y programa escolar
     * @param integer $id Id del objeto SchoolProgramSubject
     * @return integer Elimina una entidad de tipo SchoolProgramSubject.
     */
    public static function deleteSchoolProgramSubject($id)
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
     *Obtiene las asignatura sasociadas a un grupo
     * @param integer $subjectGroup: Id del grupo.
     * @return integer Obtiene el conjunto de asignaturas asociadas en caso de que esta esté asociada con otras.
     */
    public static function getSubjectGroup($subjectGroup)
    {
        try{
            return self::where('subject_group',$subjectGroup)
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

}
