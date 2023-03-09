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

// signin

Route::middleware('guest')->post('/signin', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user || ! \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    return $user->createToken('personal')->plainTextToken;
});

Route::middleware('guest')->post('/signup', function (Request $request) {
    $request->validate([
        'email' => 'required|email|max:100|unique:users,email',
        'password' => [
            'required',
            'max:100',
            \Illuminate\Validation\Rules\Password::min(6)->letters()->numbers()->symbols()
        ]
    ]);

    $user = \App\Models\User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => \Illuminate\Support\Facades\Hash::make($request->password)
    ]);

    return $user->createToken('personal')->plainTextToken;
});

Route::post('projects', function () {
    $user = request()->user();

    $project = \App\Models\Project::create([
        'name' => request('name'),
        'user_id' => $user->id
    ]);

    return $project;
});

