<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\GuideController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoCategoryController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\WeightController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('user')->group(function() {
    Route::post('login', [UserController::class, 'login']);
    Route::post('registrasi', [UserController::class, 'regist']);
    Route::delete('logout', [UserController::class, 'logout']);
    Route::post('forget-password', [UserController::class, 'forgetPassword']);
    Route::post('generate-password', [UserController::class, 'generateNewRandomPassword']);
    Route::post('update', [UserController::class, 'update']);
    Route::put('change-password', [UserController::class, 'changePassword']);
});

Route::prefix('guide')->group(function() {
    Route::post('all-user', [GuideController::class, 'getAllUser']);
    Route::post('all-user-based-guide', [GuideController::class, 'getUserBasedGuide']);
    Route::post('choose-user', [GuideController::class, 'chooseUser']);
    Route::post('create-user', [GuideController::class, 'createUser']);
    Route::delete('remove-user', [GuideController::class, 'removeUser']);
});

Route::prefix('event')->group(function() {
    Route::post('all-today-event-child-user-based-id', [EventController::class, 'getEventTodayByUserBasedGuide']);
});

Route::prefix('weight')->group(function() {
    Route::post('weight-based-guide', [WeightController::class, 'getWeightByGuide']);
    Route::post('weight-based-user', [WeightController::class, 'getUserWeight']);
    Route::post('store', [WeightController::class, 'storeWeight']);
    Route::put('update', [WeightController::class, 'updateWeight']);
    Route::delete('delete', [WeightController::class, 'deleteWeight']);
});

Route::prefix('video')->group(function () {
    Route::post('get-video', [VideoController::class, 'getVideo']);
    Route::post('update-video', [VideoController::class, 'updateVideo']);
    Route::post('upload-video', [VideoController::class, 'createVideo']);
    Route::delete('delete-video', [VideoController::class, 'deleteVideo']);
});

Route::prefix('video-categories')->group(function () {
    Route::get('get', [VideoCategoryController::class, 'getAllCategories']);
});
