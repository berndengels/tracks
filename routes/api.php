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
/*
Route::group([
	'as'        => 'planner.',
	'middleware' => ['auth:sanctum'],
], function () {
	Route::resource('route-planner-data', ApiRoutePlannerDataController::class);
	Route::post('route-planner-data/{routePlanner}', [ApiRoutePlannerDataController::class, 'storePoints']);
});
*/
Route::resource('route-planner-data', ApiRoutePlannerDataController::class);
Route::post('route-planner-data/{routePlanner}', [ApiRoutePlannerDataController::class, 'storePoints']);

//Route::get('route-planner-data/{routePlanner}', [ApiRoutePlannerDataController::class, 'index']);
Route::post('tracks', [ApiTrackController::class, 'index']);
