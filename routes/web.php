<?php

use App\Livewire\Leaderboard;
use App\Livewire\LearningPath;
use App\Livewire\LessonPlayer;
use App\Livewire\Progress;
use App\Livewire\Shop;
use App\Livewire\Social;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('learn') : redirect()->route('login'));

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', LearningPath::class)->name('dashboard');
    Route::get('learn', LearningPath::class)->name('learn');
    Route::get('lesson/{lesson}', LessonPlayer::class)->name('lesson.play');
    Route::get('shop', Shop::class)->name('shop');
    Route::get('leaderboard', Leaderboard::class)->name('leaderboard');
    Route::get('social', Social::class)->name('social');
    Route::get('progress', Progress::class)->name('progress');

    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
