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

Route::post("/upload", "SearchController@search")->name("search");
Route::post("/uploadCSV", "SearchController@searchCSV")->name("searching by csv");
Route::post("/media", "MediaController@post")->name("post media");
Route::get("/media/{number}", "MediaController@getInfo")->name("get media entry");
