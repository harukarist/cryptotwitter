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


Route::get('/trend/sv', 'TrendController@index');
Route::get('/ticker/getTicker', 'TrendController@getTicker');
Route::get('/ticker/getTweet', 'TrendController@getTweet');
Route::get('/getNews', 'NewsController@getNews');

Auth::routes();

// Twitterログイン認証
Route::get('/auth/twitter', 'Auth\TwitterAuthController@redirectToProvider')->name('auth.twitter');
Route::get('/auth/twitter/callback', 'Auth\TwitterAuthController@handleProviderCallback');
Route::get("/auth/twitter/logout", "Auth\TwitterAuthController@logout")->name('auth.logout');


// 初回アクセス時のみLaravel側でapp.blade.phpを表示し、
// 以後はフロント側のVueRouterでルーティングを行う
// {any?} で任意のパスパラメータ any を受け入れ
// パスパラメータの文字列は任意'.+'
Route::get('/{any?}', function () {
    return view('layouts.app');
})->where('any', '.+');



// Route::get('/home', 'HomeController@index')->name('home');
