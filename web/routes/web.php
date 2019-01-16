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

Route::get("/hummer", "HomeController@index")->name("hummer");
Route::get("/", "HomeController@keyboard")->name("keyboard");
Route::get("/slides/{name}", "SlideController@index")->name("slides");
Route::get("/paper.pdf", "HomeController@paper")->name("pdf paper");
Route::get("/about", "HomeController@about")->name("about");
Route::get("/media/{number}/{type}", "MediaController@get")->name("get media");
Route::get("/dynamic/{number}", "DynamicController@index")->name("get dynamic");
