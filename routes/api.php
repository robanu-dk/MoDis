<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\GuideController;
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
    Route::post('update', [UserController::class, 'update']);
    Route::post('change-password', [UserController::class, 'changePassword']);
});

Route::prefix('guide')->group(function() {
    Route::post('all-user', [GuideController::class, 'getAllUser']);
    Route::post('all-user-based-guide', [GuideController::class, 'getUserBasedGuide']);
    Route::post('choose-user', [GuideController::class, 'chooseUser']);
    Route::post('create-user', [GuideController::class, 'createUser']);
    Route::post('remove-user', [GuideController::class, 'removeUser']);
});

Route::prefix('event')->group(function() {
    Route::post('all-today-event-child-user-based-id', [EventController::class, 'getEventTodayByUserBasedGuide']);
});
