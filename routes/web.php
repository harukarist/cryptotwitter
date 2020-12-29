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
    return view('welcome');
});

Auth::routes();

Route::get('/trend', 'TrendController@getTicker')->name('trend.get');
Route::get('/news', 'NewsController@getNews')->name('news.get');
// Route::get('/home', 'HomeController@index')->name('home');
