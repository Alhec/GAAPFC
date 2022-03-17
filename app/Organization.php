<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @package : Model
 * @author : Hector Alayon
 * @version : 1.0
 */
class Organization extends Model
{
    /**
     * Primary Key de tipo string
     *
     */
    protected $keyType='string';

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
    protected $fillable = ['id','name','faculty_id','organization_id','website','address'];

    /**
     *Asociación de la relación user con organization
     */
    public function users()
    {
        return $this->hasMany('App\User','organization_id','id');
    }

    /**
     *Obtiene una organización dado un id
     * @param string $organizationId Id de la organiación
     * @return Organization|integer De ser correcto devolvera un objeto Organization, en caso de fallar devolvera 0
     */
    public static function getOrganizationById($organizationId)
    {
        try{
            return self::where('id',$organizationId)
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     *Obtiene todas las organizaciones de la base de datos
     * @return Organization|integer De ser correcto devolvera un array de organization, en caso de fallar devolvera 0
     */
    public static function getOrganizations(){
        try{
            return self::get('id');
        }catch (\Exception $e){
            return 0;
        }
    }
}
