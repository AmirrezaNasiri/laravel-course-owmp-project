<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Models;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('projects', function () {
    $user = request()->user();

    $project = \App\Models\Project::create([
        'name' => request('name'),
        'user_id' => $user->id
    ]);

    return $project;
});

