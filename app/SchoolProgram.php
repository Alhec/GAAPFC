<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class SchoolProgram extends Model
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
    protected $fillable = ['organization_id','school_program_name','num_cu', 'min_num_cu_final_work','duration',
        'min_duration','grant_certificate','conducive_to_degree','doctoral_exam','min_cu_to_doctoral_exam'];

    /**
     * Los atributos que deben ocultarse para los Array.
     *
     * @var array
     */
    protected $hidden = ['organization_id'];

    /**
     *Obtiene todos los programas escolares de una organización
     * @param string $organizationId Id de la organiación
     * @param integer $perPage Parámetro opcional, cantidad de elementos por página, default:0
     * @return SchoolProgram|integer Obtiene todos los programas escolares de una organización, si falla devolverá 0
     */
    public static function getSchoolProgram($organizationId, $perPage=0)
    {
        try{
            if ($perPage == 0){
                return self::where('organization_id',$organizationId)
                    ->get();
            }else{
                return self::where('organization_id',$organizationId)
                    ->paginate($perPage);
            }
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene un programa escolar dado su id en una organización
     * @param string $id Id del programa escolar
     * @param string $organizationId Id de la organiación
     * @return SchoolProgram|integer Obtiene un programa escolar dado su id y su organización, si falla devolverá 0
     */
    public static function getSchoolProgramById($id, $organizationId)
    {
        try{
            return self::where('id',$id)
                ->where('organization_id',$organizationId)
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si el nombre de un programa escolar ya esta guardado en el sistema o no
     * @param string $name nombre del programa
     * @param string $organizationId Id de la organiación
     * @return bool|integer Devuelve un booleano y verifica si el nombre de un programa escolar está en la base de datos,
     * si falla devolverá 0
     */
    public static function existSchoolProgramByName($name, $organizationId)
    {
        try{
            return self::where('school_program_name',$name)
                ->where('organization_id',$organizationId)
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Crea un programa escolar en el sistema
     * @param mixed $schoolProgram Objeto de tipo programa escolar (contiene los atributos del modelo)
     * @return integer Agrega un programa escolar asociado a una organización y devuelve el id, si falla devolverá 0
     */
    public static function addSchoolProgram($schoolProgram)
    {
        try{
            return self::insertGetId($schoolProgram->all());
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Obtiene un programa escolar dado su nombre en una organización
     * @param string $name nombre del programa escolar
     * @param string $organizationId Id de la organiación
     * @return SchoolProgram|integer Obtiene un programa escolar dado su nombre y organización, si falla devolverá 0
     */
    public static function getSchoolProgramByName($name, $organizationId)
    {
        try{
            return self::where('school_program_name',$name)
                ->where('organization_id',$organizationId)
                ->get();
        }catch(\Exception $e){
            return 0;
        }
    }

    /**
     *Valida si el id de un programa escolar ya esta guardado en el sistema
     * @param string $id Id del programa
     * @param string $organizationId Id de la organiación
     * @return bool|integer Devuelve un booleano y verifica si el id de un programa escolar está en la base de datos,
     * si falla devolverá 0
     */
    public static function existSchoolProgramById($id, $organizationId)
    {
        try{
            return self::where('id',$id)
                ->where('organization_id',$organizationId)
                ->exists();
        }catch(\Exception $e){
            return 0;
        }
    }

    /**
     *Elimina un programa escolar
     * @param integer $id Id del usuario
     * @return integer Elimina un programa escolar dado su id, si falla devolverá 0
     */
    public static function deleteSchoolProgram($id)
    {
        try{
            self::find($id)
                ->delete();
        }catch(\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    /**
     *Actualiza un programa escolar dado su id
     * @param integer $id Id del programa escolar
     * @param mixed $schoolProgram Objeto de tipo programa escolar (contiene los atributos del modelo)
     * @return integer Actualiza un programa escolar dado su id, si falla devolverá 0
     */
    public static function updateSchoolProgram($id, $schoolProgram)
    {
        try{
            self::find($id)
                ->update($schoolProgram->all());
        }catch(\Exception $e){
            DB::rollback();
            return 0;
        }
    }

}

