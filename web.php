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
Route::get('oAuth', 'GoogleCalendarController@connect');
Route::get('list-event', 'GoogleCalendarController@getEvent');
Route::get('store-event', 'GoogleCalendarController@store');
Route::get('destroy-event/{id}', 'GoogleCalendarController@destroy');
