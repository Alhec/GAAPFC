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
class Subject extends Model
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
    protected $fillable = ['code','name','uc','is_final_subject','is_project_subject','theoretical_hours',
        'practical_hours','laboratory_hours'];

    /**
     *Asociación de la relación schoolPrograms con subject
     */
    public function schoolPrograms()
    {
        return $this->belongsToMany('App\SchoolProgram','school_program_subject')
            ->as('schoolProgramSubject')
            ->withPivot('type','subject_group');
    }

    /**
     *Obtiene las asignaturas presentes en una organización
     * @param string $organizationId Id de la organiación
     * @param integer $perPage Parámetro opcional, cantidad de elementos por página, default:0
     * @return integer|array|object Lista las asignaturas de todos los programas escolares asociadas a una organización.
     */
    public static function getSubjects($organizationId, $perPage=0){
        try{
            if ($perPage == 0){
                return self::with('schoolPrograms')
                    ->whereHas('schoolPrograms',function (Builder $query) use ($organizationId){
                        $query
                            ->where('organization_id','=',$organizationId);
                    })
                    ->get();
            }else{
                return self::with('schoolPrograms')
                    ->whereHas('schoolPrograms',function (Builder $query) use ($organizationId){
                        $query
                            ->where('organization_id','=',$organizationId);
                    })
                    ->paginate($perPage);
            }
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene una asignatura dado su id en una organización
     * @param string $id Id de la asignatura
     * @param string $organizationId Id de la organiación
     * @return Subject|integer Devuelve una asignatura dado su id en una organización.
     */
    public static function getSubjectById($id,$organizationId){
        try{
            return self::where('id',$id)
                ->with('schoolPrograms')
                ->whereHas('schoolPrograms',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si existe una asignatura dado su codigo en una organización
     * @param string $code Código de la asignatura
     * @param string $organizationId Id de la organiación
     * @return bool|integer Devuelve true si el código de la asignaturas está presente en la organización de lo
     * contrario será false.
     */
    public static function existSubjectByCode($code,$organizationId){
        try{
            return self::where('code',$code)
                ->with('schoolPrograms')
                ->whereHas('schoolPrograms',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->exists();
        }catch (\Exception $e){
            return 0;
        }

    }

    /**
     *Crea una asignatura en el sistema
     * @param mixed $subject Objeto de tipo subject (contiene los atributos del modelo)
     * @return integer Crea una asignatura de ser exitosa devolverá su id, si falla devolverá 0.
     */
    public static function addSubject($subject)
    {
        try{
            return self::insertGetId($subject->only('code','name','uc','is_final_subject','is_project_subject',
                'theoretical_hours','practical_hours','laboratory_hours'));
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Obtiene una asignatura dado su code en una organización
     * @param string $code Código de la asignatura
     * @param string $organizationId Id de la organiación
     * @return Subject|integer Obtiene la asignatura asociada al código en la organización.
     */
    public static function getSubjectByCode($code,$organizationId)
    {
        try{
            return self::where('code',$code)
                ->with('schoolPrograms')
                ->whereHas('schoolPrograms',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }

    }

    /**
     *Valida si existe una asignatura dado su id en una organización
     * @param string $id Id de la asignatura
     * @param string $organizationId Id de la organiación
     * @return bool|integer Devuelve true si el id de la asignatura está presente en la organización de lo contrario
     * será false.
     */
    public static function existSubjectById($id,$organizationId)
    {
        try{
            return self::where('id',$id)
                ->with('schoolPrograms')
                ->whereHas('schoolPrograms',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Elimina una asignatura en el sistema
     * @param integer $id Id de la asignatura
     * @return integer Elimina una asignatura dado su id, si falla devolverá 0.
     */
    public static function deleteSubject($id)
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
     *Actualiza una asignatura dado su id en el sistema
     * @param integer $id Id del usuario
     * @param mixed $subject Objeto de tipo subject (contiene los atributos del modelo)
     * @return integer Actualiza los datos de una asignatura dado su id.
     */
    public static function updateSubject($id,$subject)
    {
        try{
            self::find($id)
                ->update($subject->all());
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Actualiza una asignatura dado su id en el sistema
     * @param integer $id Id del usuario
     * @param mixed $subject Array con los atributos del objeto subject
     * @return integer Actualiza los datos de una asignatura dado su id.
     */
    public static function updateSubjectLikeArray($id,$subject)
    {
        self::find($id)
            ->update($subject);
        try{

        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Obtiene las asignaturas presentes en un programa escolar de una organización
     * @param string $schoolProgramId Id del programa escolar
     * @param string $organizationId Id de la organiación
     * @param integer $perPage Parámetro opcional, cantidad de elementos por página, default:0
     * @return integer|Subject Devuelve las asignaturas asociadas a un programa escolar en una organización.
     */
    public static function getSubjectsBySchoolProgram($schoolProgramId, $organizationId,  $perPage=0){
        try{
            if($perPage == 0){
                return self::whereHas('schoolPrograms',function (Builder $query) use ($schoolProgramId,$organizationId){
                    $query
                        ->where('organization_id','=',$organizationId)
                        ->where('school_program_id','=',$schoolProgramId);
                    })
                    ->get();
            }else{
                return self::whereHas('schoolPrograms',function (Builder $query) use ($schoolProgramId,$organizationId){
                    $query
                        ->where('organization_id','=',$organizationId)
                        ->where('school_program_id','=',$schoolProgramId);
                    })
                    ->paginate($perPage);
            }
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene las asignaturas de tipo proyecto en un programa escolar de una organización
     * @param string $schoolProgramId Id del programa escolar
     * @param string $organizationId Id de la organiación
     * @return integer|Subject Devuelve la(s) asignatura(s) proyecto de acuerdo al programa escolar en la organización.
     */
    public static function getProjectBySchoolProgram($schoolProgramId,$organizationId)
    {
        try{
            return self::where('is_project_subject',true)
                ->whereHas('schoolPrograms',function (Builder $query) use ($schoolProgramId,$organizationId){
                $query
                    ->where('organization_id','=',$organizationId)
                    ->where('school_program_id','=',$schoolProgramId);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene las asignaturas de tipo finalWork en un programa escolar de una organización
     * @param string $schoolProgramId Id del programa escolar
     * @param string $organizationId Id de la organiación
     * @return integer|Subject Devuelve la asignatura de trabajo final de acuerdo al programa escolar en la organización
     */
    public static function getFinalWorkBySchoolProgram($schoolProgramId, $organizationId)
    {
        try{
            return self::where('is_final_subject',true)
                ->whereHas('schoolPrograms',function (Builder $query) use ($schoolProgramId,$organizationId){
                    $query
                        ->where('organization_id','=',$organizationId)
                        ->where('school_program_id','=',$schoolProgramId);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene una asignatura dado su id en una organización
     * @param string $id Id de la asignatura
     * @param string $organizationId Id de la organiación
     * @return Subject|integer Devuelve una asignatura dado su id en una organización sin relación con algun programa
     * escolar.
     */
    public static function getSimpleSubjectById($id,$organizationId){
        try{
            return self::where('id',$id)
                ->whereHas('schoolPrograms',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene las asignaturas que no son de proyecto o de trabajo de grado presentes en una organización
     * @param string $organizationId Id de la organiación
     * @param integer $perPage Parámetro opcional, cantidad de elementos por página, default:0
     * @return integer|object Lista las asignaturas de todos los programas escolares asociadas a una organización sin
     * los programas escolares y proyectos.
     */
    public static function getSubjectsWithoutFinalWorks($organizationId, $perPage=0){
        try{
            if ($perPage == 0){
                return self::with('schoolPrograms')
                    ->whereHas('schoolPrograms',function (Builder $query) use ($organizationId){
                        $query
                            ->where('organization_id','=',$organizationId);
                    })
                    ->where('is_final_subject','=',false)
                    ->where('is_project_subject','=',false)
                    ->get();
            }else{
                return self::with('schoolPrograms')
                    ->whereHas('schoolPrograms',function (Builder $query) use ($organizationId){
                        $query
                            ->where('organization_id','=',$organizationId);
                    })
                    ->where('is_final_subject','=',false)
                    ->where('is_project_subject','=',false)
                    ->paginate($perPage);
            }
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene las asignaturas que que estan en programas no conducente a grado presentes en una organización
     * @param string $organizationId Id de la organiación
     * @return integer|object Lista las asignaturas de todos los programas escolares que no son conducentes a grado
     * asociadas a una organización
     */
    public static function getSubjectsInProgramsNotDegree($organizationId){
        try{
            return self::with('schoolPrograms')
                ->whereHas('schoolPrograms',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId)
                        ->where('conducive_to_degree','=',false);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }
}
