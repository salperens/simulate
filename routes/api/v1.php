<?php

use App\Http\Controllers\Api\V1\FixtureController;
use App\Http\Controllers\Api\V1\PlayController;
use App\Http\Controllers\Api\V1\PredictionController;
use App\Http\Controllers\Api\V1\SeasonController;
use App\Http\Controllers\Api\V1\StandingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for version 1.
| These routes are loaded by routes/api.php with 'v1' prefix.
| All routes here will be prefixed with /api/v1
|
*/

Route::get('/standings', [StandingsController::class, 'index']);
Route::get('/season/current', [SeasonController::class, 'current']);
Route::get('/fixtures/week/{week}', [FixtureController::class, 'byWeek']);
Route::put('/fixtures/{id}', [FixtureController::class, 'update']);
Route::get('/predictions/week/{week}', [PredictionController::class, 'byWeek']);
Route::get('/predictions/current', [PredictionController::class, 'current']);
Route::post('/league/week/{week}/play', [PlayController::class, 'week']);
Route::post('/league/play-all', [PlayController::class, 'all']);
