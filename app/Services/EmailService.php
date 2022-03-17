<?php
/**
 * Created by PhpStorm.
 * User: halayon
 * Date: 18/09/19
 * Time: 12:29 PM
 */

namespace App\Services;

use App\User;
use App\Organization;
use Illuminate\Support\Facades\Mail;

/**
 * @package : Services
 * @author : Hector Alayon
 * @version : 1.0
 */
class EmailService
{
    /**
     * Envía correo de usuario creado a los usuarios que pertenecen a la organización con id ICT.
     * @param integer $id Id del usuario
     * @param string $userType Tipo de usuario
     * @param string $organizationId Id de la organiación
     * @return integer, de ocurrir un error devolvera un 0, del o contrario sera un 1.
     */
    public static function createUserICT($id,$userType,$organizationId)
    {
        $user= User::getUserById($id,$userType,$organizationId);
        if (is_numeric($user)&&$user==0){
            return 0;
        }
        $user=$user[0];
        $organization=Organization::getOrganizationById($organizationId);
        if (is_numeric($organization)&&$organization==0){
            return 0;
        }
        $organization=$organization[0];
        $data['name']=$user['first_name'];
        $data['organization']=$organizationId;
        switch ($userType){
            case 'S':
                $data['profile']='Estudiante';
                break;
            case 'T':
                $data['profile']='Profesor';
                break;
            case 'A':
                $data['profile']='Administrador';
                break;
            default:
                break;
        }
        $data['web']=$organization['website'];
        try{
            Mail::send('email.Geoquimica.emailCreateUser',$data,function ($message) use ($user){
                $message->to($user['email'], $user['first_name'])
                    ->subject('Usuario creado exitosamente');
            });
        }catch (\Exception $error){
            return 0;
        }
        return 1;
    }

    /**
     * Define que correo de creación de usuario se enviará de acuerdo a la organización, actualmente sólo está definido
     * el de la organización con id ICT.
     * @param integer $id Id del usuario
     * @param string $organizationId Id de la organiación
     * @param string $userType Tipo de usuario
     * @return integer, de ocurrir un error devolvera un 0, del o contrario sera un 1 si es exitoxo y su etorno por
     * defecto sera 0.
     */
    public static function userCreate($id,$organizationId,$userType)
    {
        switch ($organizationId){
            case 'ICT':
                return self::createUserICT($id,$userType,$organizationId);
                break;
            default:
                return 1;
        }
    }

}
