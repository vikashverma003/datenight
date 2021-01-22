<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;


class UserAuth
{
    use \App\Traits\APIResponseManager;
    use \App\Traits\CommonUtil;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user=Auth::user();
        if($user->role !=config('constants.role.USER')){
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'ACCESS_ERROR', 'error_details', '');
        }
        return $next($request);
    }
}
