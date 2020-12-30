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

Route::get('/', function () {
    return view('top');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/trend', 'TrendController@getTicker')->name('trend.get');
Route::get('/news', 'NewsController@getNews')->name('news.get');

// Twitterログイン認証
Route::get('/auth/twitter', 'Auth\TwitterAuthController@redirectToProvider')->name('auth.twitter');
Route::get('/auth/twitter/callback', 'Auth\TwitterAuthController@handleProviderCallback');
Route::get("/auth/twitter/logout", "Auth\TwitterAuthController@logout");
