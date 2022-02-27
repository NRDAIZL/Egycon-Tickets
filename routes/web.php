<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TicketController;

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
    Route::get('/requests/delete/{id}', [PostController::class, 'destroy'])->name('requests.delete');
    
    Route::get('/requests/edit', [PostController::class, 'edit_requests'])->name('edit-requests');
    Route::post('/requests/action', [PostController::class, 'action'])->name('action');

    Route::prefix('/tickets')->as('tickets.')->group(function(){
        Route::get('/', [TicketController::class, 'view'])->name('view');
        Route::get('/add', [TicketController::class, 'add'])->name('add');
        Route::post('/add', [TicketController::class, 'store']);
    });

    Route::get('/import', [PostController::class,'import_sheet'])->name('import');
    Route::post('/import', [PostController::class,'import_sheet_store']);

});

Route::get('/', [PostController::class, 'instructions'])->name('instructions');
Route::post('/', [PostController::class, 'instructions_store']);
