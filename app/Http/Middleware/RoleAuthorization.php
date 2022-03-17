<?php

namespace App\Http\Middleware;

use Closure;

/**
 * @package : Middleware
 * @author : Hector Alayon
 * @version : 1.0
 */
class RoleAuthorization
{
    /**
     * Valida que el usuario realice una petición solo a su organización y permite la autorización mediante los roles
     * que se les pase por parámetro.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  \array  $roles
     * @return mixed
     */
    public function handle($request, Closure $next,...$roles)
    {
        $organizationId = $request->header('Organization-Key');
        if (auth()->payload()['user']->organization_id!=$organizationId){
            return response()->json(['error' => 'Unauthorized'],401);
        }
        $usersRol = array_column(auth()->payload()['user']->roles,'user_type');
        $authorized = false;
        foreach ($usersRol as $rol){
            if(in_array($rol,$roles)){
                $authorized = true;
                break;
            }
        }
        if(!$authorized){
            return response()->json(['error' => 'Unauthorized'],401);
        }
        return $next($request);
    }
}
