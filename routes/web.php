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

// パスワードリマインダフォーム表示
Route::get('/password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');

// パスワードリセットフォーム表示
Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
// パスワードリセット処理
Route::post('/password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

// パスワード再設定フォーム
Route::get('password/confirm', 'Auth\ConfirmPasswordController@showConfirmForm')->name('password.confirm');
// パスワード再設定処理
Route::post('password/confirm', 'Auth\ConfirmPasswordController@confirm');

Route::group(['middleware' => 'auth'], function () {
    // Twitterログイン認証（TwitterAPIへのリダイレクト）
    Route::get('/auth/twitter/login', 'Auth\TwitterAuthController@redirectToProvider');
    // Twitterログイン認証（TwitterAPIからのコールバック）
    Route::get('/auth/twitter/callback', 'Auth\TwitterAuthController@handleProviderCallback');
});


// 初回アクセス時のみLaravel側でapp.blade.phpを表示し、
// 以後はフロント側のVueRouterでルーティングを行う
// {any?} で任意のパスパラメータ any を受け入れ
// パスパラメータの文字列は任意'.+'
Route::middleware(['cors'])->group(function () {
    Route::get('/{any?}', function () {
        return view('layouts.app');
    })->where('any', '.+');
});
