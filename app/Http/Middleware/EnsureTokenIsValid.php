<?php

namespace App\Http\Middleware;

use App\Util;
use Closure;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (is_null($request->header('Authorization'))) {
            $errorArr = [
                'user_id' => \Config::get('error-messages.AuthTokenIsRequired')
            ];

            return Util::response([], $errorArr, []);
        }

        return $next($request);
    }
}
