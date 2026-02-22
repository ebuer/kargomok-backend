<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reset-password', function () {
    return view('reset-password', [
        'token' => request('token', ''),
        'email' => request('email', ''),
    ]);
})->name('reset-password');
