<?php

use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\UserProgressController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('config', ConfigController::class)->name('api.config');
    Route::get('courses', CourseController::class)->name('api.courses');
    Route::get('users/{user}/progress', UserProgressController::class)->name('api.user.progress');
    Route::get('leaderboard', LeaderboardController::class)->name('api.leaderboard');
});
