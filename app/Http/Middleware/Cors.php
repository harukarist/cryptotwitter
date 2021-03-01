<?php

namespace App\Http\Middleware;

use Closure;

// CORSを設定するミドルウェア
class Cors
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
        // レスポンスのHTTPヘッダーにフィールドを追加
        return $next($request)
            ->header('Access-Control-Allow-Origin', '*') //全てのドメインからのアクセスを許可
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS') //許可するHTTPリクエストメソッド
            ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With'); //許可するHTTPヘッダー

    }
}
