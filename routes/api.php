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

Route::middleware('auth:sanctum')->get('projects', function () {
    return request()->user()->projects()->each->only([
        'name', 'id'
    ]);
});

Route::middleware('auth:sanctum')->get('projects/{$projectId}', function ($projectId) {
    return request()->user()->projects()->findOrfail($projectId)->only([
        'name', 'id'
    ]);
});

Route::middleware('auth:sanctum')->post('projects', function () {
    request()->validate([
        'name' => [
            'required',
            'string',
            'min:4',
            'max:100',
            \Illuminate\Validation\Rule::unique('projects', 'name')
                ->where('creator_id', request()->user()->id)
        ]
    ]);

    $project = \App\Models\Project::create([
        'name' => request('name'),
        'creator_id' => request()->user()->id,
    ]);

    return [
        'id' => $project->id,
        'name' => $project->name
    ];
});

Route::middleware('auth:sanctum')->put('projects/{projectId}', function ($projectId) {
    request()->validate([
        'name' => [
            'required',
            'string',
            'min:4',
            'max:100',
            \Illuminate\Validation\Rule::unique('projects', 'name')
                ->where('creator_id', request()->user()->id)
                ->ignore($projectId)
        ]
    ]);

    $project = request()->user()->projects()->findOrFail($projectId);

    $project->update([
        'name' => request('name')
    ]);

    return [
        'id' => $project->id,
        'name' => $project->name
    ];
});

Route::middleware('auth:sanctum')->delete('projects/{projectId}', function ($projectId) {
    $project = request()->user()->projects()->findOrFail($projectId);
    $project->delete();
});
