<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusController;

Route::get('/buses', [BusController::class, 'getBuses']);
Route::get('/seats', [BusController::class, 'getSeats']);
Route::post('/reserve', [BusController::class, 'reserveSeats']);
