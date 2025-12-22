<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return response()->json(['message' => 'login'], 200);
})->name('login');

Route::get('/', function () {
    return response()->json(['ok' => true], 200);
});