<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileNodeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PromptController;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::post('google', 'google');
    Route::get('me', 'me');
});

Route::controller(ProjectController::class)->group(function () {
    Route::get('projects', 'index');
    Route::post('project', 'create');
    Route::get('project/{id}', 'load');
    Route::put('project/{id}', 'update');
    Route::put('project/{id}/api_key', 'set_api_key');
    Route::delete('project/{id}', 'destroy');
    Route::get('project/{id}/prompts', 'get_prompts');
    Route::get('project/{id}/files', 'get_files');
});


Route::controller(PromptController::class)->group(function () {
    Route::get('prompts', 'index');
    Route::post('prompt', 'create');
    Route::get('prompt/{id}', 'load');
    Route::put('prompt/{id}', 'update');
    Route::put('prompt/{id}/run', 'run');
    Route::delete('prompt/{id}', 'destroy');
});

Route::controller(FileNodeController::class)->group(function () {
    // only need to get via project
    Route::post('file', 'create');
    Route::put('file/{id}', 'update');
    Route::delete('file/{id}', 'destroy');
});