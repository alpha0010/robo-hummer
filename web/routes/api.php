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

Route::post("/upload", function(Request $request) {
    if (  !$request->audio->isValid()
        || $request->audio->getClientMimeType() != "audio/wav")
    {
        return "Unaccepted mime type.";
    }

    return $request->audio->store("recordings");
});
