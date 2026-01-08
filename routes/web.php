<?php

use App\Http\Controllers\PasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/passwords', [PasswordController::class, 'index'])
    ->name('passwords.index');
