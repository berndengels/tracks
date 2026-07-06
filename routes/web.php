<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\Admin\AdminTrackController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [TrackController::class, 'index'])->name('tracks');
});

Route::group([
    'middleware'    => 'auth',
    'prefix' => 'admin',
    'as'    => 'admin.',
], function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('tracks', AdminTrackController::class)->except(['show','create']);
});

require __DIR__.'/auth.php';
