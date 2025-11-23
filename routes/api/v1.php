<?php

use App\Http\Controllers\Api\V1\LeagueController;
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

Route::get('/standings', [LeagueController::class, 'standings']);
Route::get('/season/current', [LeagueController::class, 'currentSeason']);
Route::get('/fixtures/week/{week}', [LeagueController::class, 'fixtures']);

