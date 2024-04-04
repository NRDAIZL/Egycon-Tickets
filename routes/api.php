<?php

use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\TelegramController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/logout', [LoginController::class, 'logout']);
    Route::get('/user', [LoginController::class, 'user']);
    Route::get('/events', [EventController::class, 'index']);
    Route::post('/scan', [EventController::class, 'scan']);
    Route::post('/search', [TicketController::class, 'search']);
});

Route::any('/telegram', [TelegramController::class,'index']);

