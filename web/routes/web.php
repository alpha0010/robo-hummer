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

Route::get('/', 'HomeController@index')->name('home');
Route::get('/keyboard', 'HomeController@keyboard')->name('keyboard');
Route::get('/slides/{name}', 'SlideController@index')->name('slides');
Route::get('/paper.pdf', 'HomeController@paper')->name('pdf paper');
