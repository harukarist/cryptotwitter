<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/news', 'NewsController@index');
Route::get('/trend', 'TrendController@index');
Route::get('/tickers', 'TickerController@index');

// Twitterログイン認証
Route::get('/auth/twitter', 'Auth\TwitterAuthController@redirectToProvider');
Route::get('/auth/twitter/callback', 'Auth\TwitterAuthController@handleProviderCallback');
Route::get("/auth/twitter/logout", "Auth\TwitterAuthController@logout");
