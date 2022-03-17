<?php

namespace App\Http\Middleware;

use Closure;

/**
 * @package : Middleware
 * @author : Hector Alayon
 * @version : 1.0
 */
class AppAuthorization
{
    /**
     * Valida que la app web use la llave de autorizacion para hacer uso del Api.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $appAuth = $request->header('App-Auth');
        if ($appAuth !== env('APP_NAME', 'GAAPFC')){
            return response()->json(['error' => 'App Unauthorized'],401);
        }
        return $next($request);
    }
}
