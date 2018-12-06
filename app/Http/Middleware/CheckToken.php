<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (empty($request->input('token'))) {
            return response()->json(['code'=>2001,'message'=>'token不存在，请登录']);
        }
        if(Redis::get('userid:'.$request->input('userid').'token') === null){
            return response()->json(['code'=>2002,'message'=>'token过期，请重新登录获取']);
        }
        if (Redis::get('userid:'.$request->input('userid').'token') != $request->input('token')){
            return response()->json(['code'=>2003,'message'=>'token错误，请重新登录获取']);
        }
        return $next($request);
    }
}