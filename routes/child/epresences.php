<?php

use App\Http\Controllers\EpresenceController;
use Illuminate\Support\Facades\Route;

Route::get('/epresence/my', [EpresenceController::class, 'myData']);
Route::get('/epresence/member-final', [EpresenceController::class, 'memberDataFinal']);
Route::get('/epresence/member-raw', [EpresenceController::class, 'memberDataRaw']);

Route::post('/epresence', [EpresenceController::class, 'store']);
Route::post('/epresence-custom-logic', [EpresenceController::class, 'storeCustomLogic']);

Route::put('/epresence/{epresenceId}', [EpresenceController::class, 'update']);
