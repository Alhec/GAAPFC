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
class SchoolPeriod extends Model
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
    protected $fillable = ['organization_id','cod_school_period','start_date','end_date','withdrawal_deadline',
        'load_notes','inscription_start_date','inscription_visible','project_duty','final_work_duty'];

    /**
     * Los atributos que deben ocultarse para los Array.
     *
     * @var array
     */
    protected $hidden = ['organization_id'];

    /**
     *Asociación de la relación subjects con SchoolPeriod
     */
    public function subjects()
    {
        return $this->hasMany('App\SchoolPeriodSubjectTeacher','school_period_id','id')
            ->with('subject')
            ->with('teacher')
            ->with('schedules');
    }

    /**
     *Asociación de la relación inscriptions con SchoolPeriod
     */
    public function inscriptions()
    {
        return $this->hasMany('App\SchoolPeriodStudent','school_period_id','id')
            ->with('enrolledSubjects');
    }

    /**
     *Obtiene los Periodos escolares de una organización
     * @param string $organizationId Id de la organiación
     * @param integer $perPage Parámetro opcional, cantidad de elementos por página, default:0
     * @return SchoolPeriod|integer Obtiene todos los periodos escolares de la organización.
     */
    public static function getSchoolPeriods($organizationId, $perPage=0)
    {
        try{
            if ($perPage == 0){
                return self::where('organization_id',$organizationId)
                    ->with('subjects')
                    ->get();
            }else{
                return self::where('organization_id',$organizationId)
                    ->with('subjects')
                    ->paginate($perPage);
            }

        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene un periodo escolar dado su id en una organización
     * @param string $id Id del periodo escolar.
     * @param string $organizationId Id de la organiación
     * @return SchoolPeriod|integer Obtiene el periodo escolar dado su id.
     */
    public static function getSchoolPeriodById($id,$organizationId)
    {
        try{
            return self::where('id',$id)
                ->where('organization_id',$organizationId)
                ->with('subjects')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene el periodo escolar en curso de una organización
     * @param string $organizationId Id de la organiación
     * @return SchoolPeriod|integer Obtiene el periodo escolar actual que transcurre en la organización.
     */
    public static function getCurrentSchoolPeriod($organizationId)
    {
        try{
            return self::where('organization_id',$organizationId)
                ->whereDate('end_date','>=',date("Y-m-d"))
                ->whereDate('inscription_start_date','<=',date("Y-m-d"))
                ->orderBy('start_date','ASC')
                ->with('subjects')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Elimina un periodo escolar en el sistema
     * @param integer $id: Id del periodo escolar
     * @return integer Elimina un periodo escolar dado su id, de fallar devolverá 0.
     */
    public static function deleteSchoolPeriod($id)
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
     *Valida si existe un periodo escolar dado su id en una organización
     * @param integer $id: Id del periodo escolar
     * @param string $organizationId Id de la organiación
     * @return bool|integer Verifica si existe un periodo escolar dado su id de existir devolverá true de lo contrario
     * será false, si falla devolverá 0.
     */
    public static function existSchoolPeriodById($id,$organizationId)
    {
        try{
            return self::where('id',$id)
                ->where('organization_id',$organizationId)
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si existe un periodo escolar dado su codigo en una organización
     * @param integer $codSchoolPeriod: código o corte del periodo escolar
     * @param string $organizationId Id de la organiación
     * @return bool|integer Verifica si existe un periodo escolar dado su código o corte de existir devolverá true de lo
     * contrario será false.
     */
    public static function existSchoolPeriodByCodSchoolPeriod($codSchoolPeriod,$organizationId)
    {
        try{
            return self::where('cod_school_period',$codSchoolPeriod)
                ->where('organization_id',$organizationId)
                ->exists();
        }catch (\Exception $e){
            return 0;
        }

    }

    /**
     *Crea un periodo escolar en el sistema
     * @param mixed $schoolPeriod: Objeto de tipo schoolPeriod (contiene los atributos del modelo)
     * @return integer Agrega un periodo escolar al sistema y devuelve el id del mismo, de fallar devolverá 0.
     */
    public static function addSchoolPeriod($schoolPeriod)
    {

        try{
            return self::insertGetId($schoolPeriod->only('organization_id','cod_school_period','start_date','end_date',
                'withdrawal_deadline','load_notes','inscription_start_date','inscription_visible','project_duty',
                'final_work_duty'));
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Obtiene un periodo escolar dado su codigo en una organización
     * @param string $codSchoolPeriod: código o corte del periodo escolar
     * @param string $organizationId Id de la organiación
     * @return SchoolPeriod|integer Obtiene el periodo escolar dado su código o corte.
     */
    public static function getSchoolPeriodByCodSchoolPeriod($codSchoolPeriod,$organizationId)
    {
        try{
            return self::where('cod_school_period',$codSchoolPeriod)
                ->where('organization_id',$organizationId)
                ->with('subjects')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Actualiza un periodo escolar dado su id en el sistema
     * @param integer $id Id del periodo escolar
     * @param mixed $schoolPeriod: Objeto de tipo schoolPeriod (contiene los atributos del modelo)
     * @return integer Edita un periodo escolar dado su id, si falla devolverá 0.
     */
    public static function updateSchoolPeriod($id,$schoolPeriod)
    {
        try{
            self::find($id)
                ->update($schoolPeriod->all());
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Obtiene los programas escolares en los que participo un profesor dado su id
     * @param string $teacherId: Id del profesor
     * @return SchoolPeriod|integer Devuelve todas las asignaturas que ha dictado un profesor dado su id en orden
     * ascendente de acuerdo a la fecha.
     */
    public static function getSubjectsByTeacher($teacherId)
    {
        try{
            return self::whereHas('subjects',function (Builder $query) use ($teacherId){
                $query
                    ->where('teacher_id','=',$teacherId);
                })
                ->with('subjects')
                ->orderBy('start_date','ASC')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }
}
