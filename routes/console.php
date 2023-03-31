<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('ide-helper', function () {
    Artisan::call('ide-helper:generate');
    Artisan::call('ide-helper:models', ['--write-mixin']);
    Artisan::call('ide-helper:meta');
})->purpose('Write IDE helper metas');
