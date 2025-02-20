<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['guest'])->group(base_path('routes/child/auth.php'));
Route::middleware(['auth:sanctum'])->group(base_path('routes/child/epresences.php'));
