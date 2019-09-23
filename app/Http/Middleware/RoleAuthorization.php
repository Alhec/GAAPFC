<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\AuthController;
class RoleAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,...$roles)
    {
        if(!in_array(auth()->payload()['user_type'],$roles)){
            return response()->json(['error' => 'Unauthorized'],401);
        }
        return $next($request);
    }
}