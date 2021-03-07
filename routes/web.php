<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// パスワード再発行メールURL経由でのパスワードリセットフォーム表示
Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');

// TwitterAPIからのコールバック処理
Route::get('/auth/twitter/callback', 'Auth\TwitterAuthController@handleProviderCallback');

// ログイン後のユーザーのみアクセス可能なルーティング
Route::group(['middleware' => 'auth'], function () {
    // Twitterログイン認証（TwitterAPIへのリダイレクト）
    Route::get('/auth/twitter/login', 'Auth\TwitterAuthController@redirectToProvider');
});

// 上記以外のルートは初回アクセス時のみLaravel側でapp.blade.phpを表示し、
// 以後はフロント側のVueRouterでルーティングを行う。
// VueRouterのルーティングは resources/js/router.js にて指定。
Route::middleware(['cors'])->group(function () {
    // {any?} で任意のパスパラメータ'any'を受け入れる
    Route::get('/{any?}', function () {
        return view('layouts.app');
        // パスパラメータ'any'がある場合の形式を'.+'（任意の文字1文字以上）とする
    })->where('any', '.+');
});
