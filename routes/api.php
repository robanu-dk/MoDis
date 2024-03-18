<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('user')->group(function() {
    Route::post('login', [UserController::class, 'login']);
    Route::post('registrasi', [UserController::class, 'regist']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('forget-password', [UserController::class, 'forgetPassword']);
    Route::post('generate-password', [UserController::class, 'generateNewRandomPassword']);
});
