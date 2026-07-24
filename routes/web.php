<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\Admin\AdminTrackController;
use App\Http\Controllers\Admin\AdminMediaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/{modulo?}', [TrackController::class, 'index'])->name('tracks');
});

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
});

require __DIR__.'/auth.php';
