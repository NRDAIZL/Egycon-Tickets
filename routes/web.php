<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Route::get('add-blog-post-form', [PostController::class, 'index']);
Route::get('/home',function(){
    return redirect()->route('admin.home');
})->name('home');
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [ LoginController::class, 'login']);
Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');
Route::middleware('auth')->prefix('/admin')->as('admin.')->group(function(){
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/requests', [PostController::class, 'view_requests'])->name('requests');
    Route::get('/requests/accept/{id}', [PostController::class, 'accept'])->name('accept');
    Route::get('/requests/reject/{id}', [PostController::class, 'reject'])->name('reject');
});

Route::get('/', [PostController::class, 'instructions'])->name('instructions');
Route::post('/', [PostController::class, 'instructions_store']);
