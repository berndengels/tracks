<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\Admin\AdminTrackController;
use App\Http\Controllers\Admin\AdminMediaController;

Route::group([
    'middleware'    => 'auth',
    'prefix' => 'admin',
    'as'    => 'admin.',
], function () {
    Route::get('', fn() => view('admin.dashboard'))->name('dashboard');
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('tracks', AdminTrackController::class)->except(['show','create']);
    Route::resource('media', AdminMediaController::class)->except(['show','create']);
    Route::get('media/sanitize', [AdminMediaController::class, 'sanitize'])->name('media.sanitize');
    // toggles
    Route::post('tracks/toggle/{track}', [AdminTrackController::class, 'toggle'])->name('tracks.toggle');
    Route::post('media/toggle/{medium}', [AdminMediaController::class, 'toggle'])->name('media.toggle');
});

Route::middleware('guest')->group(function () {
    Route::get('/{year?}', [TrackController::class, 'index'])->name('tracks');
});

require __DIR__.'/auth.php';
