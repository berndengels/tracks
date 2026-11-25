<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiTrackController;
use App\Http\Controllers\Api\ApiRoutePlannerDataController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('route-planner-data/{routePlanner}', [ApiRoutePlannerDataController::class, 'index']);
Route::post('route-planner-data/{routePlanner}', [ApiRoutePlannerDataController::class, 'store']);
Route::post('tracks', [ApiTrackController::class, 'index']);
