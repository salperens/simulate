<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->group(base_path('routes/api/v1.php'));

// in the feature
// Route::prefix('v2')
//     ->name('api.v2.')
//     ->group(base_path('routes/api/v2.php'));
