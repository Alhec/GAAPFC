<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class FinalWork extends Model
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
    protected $fillable = ['title','student_id','subject_id','project_id','is_project','approval_date'];

    /**
     * Nombre de la tabla asociada
     *
     */
    protected $table = 'final_works';

    /**
     *Asociación de la relación schoolPeriods con finalWork
     */
    public function schoolPeriods()
    {
        return $this->belongsToMany('App\SchoolPeriodStudent','final_work_school_period')
            ->as('finalWorkSchoolPeriod')
            ->withPivot('id','status','description_status','final_work_id','school_period_student_id');
    }

    /**
     *Asociación de la relación teacher con finalWork
     */
    public function teachers()
    {
        return $this->belongsToMany('App\Teacher','advisors')
            ->as('advisors')
            ->withPivot('teacher_id','final_work_id')
            ->with('user');
    }

    /**
     *Asociación de la relación subject con finalWork
     */
    public function subject()
    {
        return $this->belongsTo('App\Subject');
    }

    /**
     *Valida si existe una asignatura finalWork dado el id del estudiante y su tipo Proyecto o TEG
     * @param string $studentId: Id del estudiante.
     * @param string $isProject: Flag para determinar si es un proyecto o un trabajo de grado.
     * @return bool|integer Devuelve true si no existe dependiendo del flag el proyecto o trabajo de grado aprobado
     * asociado al estudiante de lo contrario será false.
     */
    public static function existNotApprovedFinalWork($studentId, $isProject)
    {
        try{
            return self::where('student_id',$studentId)
                ->where('is_project',$isProject)
                ->where('approval_date','=',null)
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene una asignatura finalWork no aprobada dado el id del estudiante y su tipo Proyecto o TEG
     * @param string $studentId: Id del estudiante.
     * @param string $isProject: Flag para determinar si es un proyecto o un trabajo de grado.
     * @return FinalWork|integer Obtiene el trabajo de grado o proyecto no aprobado dado un estudiante asociado.
     */
    public static function getNotApprovedFinalWork($studentId, $isProject)
    {
        try{
            return self::where('student_id',$studentId)
                ->where('is_project',$isProject)
                ->where('approval_date',null)
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene una asignatura finalWork dado el id del estudiante, su estatus y su tipo Proyecto o TEG
     * @param string $studentId: Id del estudiante.
     * @param string $isProject: Flag para determinar si es un proyecto o un trabajo de grado.
     * @param string $status: El estatus del trabajo de grado o proyecto
     * @return FinalWork|integer Obtiene el trabajo de grado o proyecto dado su estatus y estudiante asociado.
     */
    public static function getFinalWorkByStudentAndStatus($studentId, $isProject, $status)
    {
        try{
            return self::where('student_id',$studentId)
                ->where('is_project',$isProject)
                ->whereHas('schoolPeriods',function (Builder $query) use ($status) {
                    $query
                        ->where('final_work_school_period.status','=',$status);
                })
                ->with('teachers')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene todos los finalWork dado el id del estudiante y su tipo Proyecto o TEG
     * @param string $studentId: Id del estudiante.
     * @param string $isProject: Flag para determinar si es un proyecto o un trabajo de grado.
     * @return FinalWork|integer Obtiene el trabajo de grado o proyecto dado un estudiante asociado.
     */
    public static function getFinalWorksByStudent($studentId, $isProject)
    {
        try{
            return self::where('student_id',$studentId)
                ->where('is_project',$isProject)
                ->with('schoolPeriods')
                ->with('teachers')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Crea una finalWork en el sistema
     * @param mixed $finalWork Objeto de tipo finalWork (contiene los atributos del modelo)
     * @return integer Crea un trabajo de grado o proyecto de ser exitoso devolverá el id  de  lo contrario devolverá 0.
     */
    public static function addFinalWork($finalWork)
    {
        try{
            return self::insertGetId($finalWork);
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Actualiza una finalWork dado su id en el sistema
     * @param integer $id Id del finalWork
     * @param mixed $finalWork Objeto de tipo finalWork (contiene los atributos del modelo)
     * @return integer Edita un trabajo de grado o proyecto de lo contrario devolverá 0.
     */
    public static function updateFinalWork($id,$finalWork)
    {
        try{
            return self::find($id)
                ->update($finalWork);
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene todos los finalWork dado el id del estudiante, el id de la asignatura final y su tipo Proyecto o TEG
     * @param string $studentId: Id del estudiante.
     * @param string $subjectId: Id de la asignatura.
     * @param string $isProject: Flag para determinar si es un proyecto o un trabajo de grado.
     * @return FinalWork|integer Obtiene el trabajo de grado o proyecto dado un estudiante y el id de la asignatura
     * asociada.
     */
    public static function getFinalWorksByStudentSubject($studentId,$subjectId, $isProject)
    {
        try{
            return self::where('student_id',$studentId)
                ->where('is_project',$isProject)
                ->where('subject_id',$subjectId)
                ->with('schoolPeriods')
                ->with('teachers')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si existe una asignatura finalWork dado el id del finalWork, estatus y su tipo Proyecto o TEG
     * @param string $id: Id del FinalWork.
     * @param string $isProject: Flag para determinar si es un proyecto o un trabajo de grado.
     * @param string $status: El estatus del trabajo de grado o proyecto
     * @return bool|integer Verifica si el trabajo de grado o proyecto dado su estatus e id asociado existe, devolverá
     * true de lo contrario será false.
     */
    public static function existFinalWorkByIdAndStatus($id, $isProject, $status)
    {
        try{
            return self::where('id',$id)
                ->where('is_project',$isProject)
                ->whereHas('schoolPeriods',function (Builder $query) use ($status) {
                    $query
                        ->where('final_work_school_period.status','=',$status);
                })
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Elimina una asignatura FinalWork de un estudiante en el sistema
     * @param integer $id Id del FinalWork
     * @return integer Elimina un finalWork dado su id, de fallar devolverá 0.
     */
    public static function deleteFinalWork($id)
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
     *Obtiene un finalWork dado su id
     * @param string $id Id del FinalWork
     * @return FinalWork|integer Obtiene un trabajo de grado o proyecto dado su id con sus respectivas relaciones de
     * tutores y periodos escolares asociados
     */
    public static function getFinalWork($id)
    {
        try{
            return self::where('id',$id)
                ->with('schoolPeriods')
                ->with('teachers')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si existe una asignatura finalWork dado su id
     * @param string $id: Id del FinalWork.
     * @return bool|integer Valida si existe el finalWork dado su id.
     */
    public static function existFinalWorkById($id)
    {
        try{
            return self::where('id',$id)
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene todos los finalWork dado el id del estudiante, inscripcion del estudiante y su tipo Proyecto o TEG
     * @param string $studentId: Id del estudiante.
     * @param string $isProject: Flag para determinar si es un proyecto o un trabajo de grado.
     * @param string $inscriptionId: Id de la inscripcion.
     * @return FinalWork|integer Obtiene el trabajo de grado o proyecto dado un estudiante asociado y su inscripcion.
     */
    public static function getFinalWorksByStudentAndSchoolPeriod($studentId, $isProject, $inscriptionId)
    {
        try{
            return self::where('student_id',$studentId)
                ->where('is_project',$isProject)
                ->whereHas('schoolPeriods',function (Builder $query) use ($inscriptionId) {
                    $query
                        ->where('final_work_school_period.school_period_student_id','=',$inscriptionId);
                })
                ->with('schoolPeriods')
                ->with('teachers')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

}
