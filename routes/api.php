<?php

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

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
    return request()->user()->projects()->get()->each->only([
        'name', 'id'
    ]);
});

Route::middleware('auth:sanctum')->get('projects/{projectId}', function ($projectId) {
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
            Rule::unique('projects', 'name')
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
            Rule::unique('projects', 'name')
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
    $project->tasks()->delete();
    $project->boards()->delete();
    $project->delete();
});


Route::middleware('auth:sanctum')->post('boards', function () {
    request()->validate([
        'name' => [
            'required',
            'string',
            'min:4',
            'max:100',
            Rule::unique('boards', 'name')
                ->where('creator_id', request()->user()->id)
        ],
        'project_id' => [
            'required',
            Rule::exists('projects', 'id')->where(function (Builder $query) {
                return $query->where('creator_id', request()->user()->id);
            }),
        ]
    ]);

    $board = \App\Models\Board::create([
        'project_id' => request('project_id'),
        'name' => request('name'),
        'creator_id' => request()->user()->id,
    ]);

    return [
        'id' => $board->id,
        'name' => $board->name
    ];
});

Route::middleware('auth:sanctum')->put('boards/{boardId}', function ($boardId) {
    $board = \App\Models\Board::findOrFail($boardId);

    request()->validate([
        'name' => [
            'required',
            'string',
            'min:4',
            'max:100',
            Rule::unique('boards', 'name')
                ->where('creator_id', request()->user()->id)
                ->where('project_id', $board->project_id)
                ->ignore($boardId)

        ]
    ]);

    $board = request()->user()->boards()->findOrFail($boardId);

    $board->update([
        'name' => request('name')
    ]);

    return [
        'id' => $board->id,
        'name' => $board->name
    ];
});

Route::middleware('auth:sanctum')->get('boards', function () {
    return request()->user()->boards()->whereProjectId(request('project_id'))->get()->each->only([
        'name', 'id'
    ]);
});

Route::middleware('auth:sanctum')->get('boards/{boardId}', function ($boardId) {
    return request()->user()->boards()->findOrfail($boardId)->only([
        'name', 'id'
    ]);
});

Route::middleware('auth:sanctum')->delete('boards/{boardId}', function ($boardId) {
    $board = request()->user()->boards()->findOrFail($boardId);
    $board->tasks()->delete();
    $board->delete();
});


// Tasks
Route::middleware('auth:sanctum')->post('tasks', function () {
    request()->validate([
        'name' => [
            'required',
            'string',
            'min:4',
            'max:100',
        ],
        'description' => [
            'string',
            'max:5000',
        ],
        'deadline' => [
            'string',
            'date',
        ],
        'board_id' => [
            'required',
            Rule::exists('boards', 'id')->where(function (Builder $query) {
                return $query->where('creator_id', request()->user()->id);
            }),
        ]
    ]);

    $task = Task::create([
        'board_id' => request('board_id'),
        'name' => request('name'),
        'description' => request('description'),
        'deadline' => request('deadline'),
        'creator_id' => request()->user()->id,
        'status' => TaskStatus::TODO,
    ]);

    return [
        'id' => $task->id,
        'board_id' => $task->board->id,
        'name' => $task->name,
        'description' => $task->description,
        'deadline' => $task->deadline,
        'status' => $task->status,
    ];
});

Route::middleware('auth:sanctum')->patch('tasks/{taskId}', function ($taskId) {
    request()->validate([
        'name' => [
            'string',
            'min:4',
            'max:100',
        ],
        'description' => [
            'nullable',
            'string',
            'max:5000',
        ],
        'deadline' => [
            'nullable',
            'string',
            'date',
        ],
    ]);

    $task = request()->user()->tasks()->findOrFail($taskId);

    $task->update([
        'name' => request()->has('name') ? request('name') : $task->name,
        'description' => request()->has('description') ? request('description') : $task->description,
        'deadline' => request()->has('deadline') ? request('deadline') : $task->deadline,
    ]);

    return [
        'id' => $task->id,
        'board_id' => $task->board->id,
        'name' => $task->name,
        'description' => $task->description,
        'deadline' => $task->deadline,
        'status' => $task->status,
    ];
});

Route::middleware('auth:sanctum')->get('tasks', function () {
    return request()->user()->tasks()->whereBoardId(request('board_id'))->get()->each->only([
        'name', 'id', 'description', 'deadline', 'status', 'board_id'
    ]);
});

Route::middleware('auth:sanctum')->get('tasks/{taskId}', function ($taskId) {
    return request()->user()->tasks()->findOrfail($taskId)->only([
        'name', 'id', 'description', 'deadline', 'status', 'board_id'
    ]);
});

Route::middleware('auth:sanctum')->delete('tasks/{taskId}', function ($taskId) {
    $task = request()->user()->tasks()->findOrFail($taskId);
    $task->delete();
});
