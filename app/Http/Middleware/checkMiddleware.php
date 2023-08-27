<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class checkMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = auth('customer')->userOrFail();
            if($user->ban_times != 0)
            {
                return $this->returnError(201, 'you have been banned for '.$user->ban_times);
            }
            if($user->isBlocked == 1)
            {
                return $this->returnError(201, 'you have been blocked');
            }
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return $next($request);
        }
        return $next($request);
    }
    public function returnError($errNum, $msg)
    {
        return response([
            'status' => false,
            'code' => $errNum,
            'msg' => $msg
        ], $errNum)
            ->header('Content-Type', 'text/json');
    }
}

