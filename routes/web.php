<?php

use App\Helpers\PaymentHelper;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscountCodeController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventQuestionController;
use App\Http\Controllers\PaymentMethodController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\QRCodeTicketController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\SubTicketController;
use App\Http\Controllers\UserController;
use App\Models\Post;
use App\Models\PostTicket;
use App\Models\User;
use App\Notifications\NewRequest;
use Illuminate\Support\Facades\Auth;

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
    return redirect()->route('admin.events.view');
})->name('home');

Route::get('/invitations/{token}', [UserController::class, 'accept_invitation'])->name('accept_invitation');
Route::post('/invitations/{token}', [UserController::class, 'accept_invitation_post'])->name('accept_invitation_post');
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [ LoginController::class, 'login']);
Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');
Route::middleware('auth')->prefix('/admin')->as('admin.')->group(function(){
    Route::get('/', function(){
        return redirect()->route('admin.events.view');
    });
    Route::prefix('/events')->as('events.')->group(function(){
        Route::get('/', [EventController::class, 'index'])->name('view');
        Route::get('/add', [EventController::class, 'add'])->name('add');
            Route::post('/add', [EventController::class, 'store']);
    });
    Route::prefix('/event/{event_id}')->middleware(['has_event_id'])->group(function(){
        Route::prefix('/events')->as('event.events.')->group(function () {
            Route::get('/', [EventController::class, 'index'])->name('view');
            Route::get('/add/{id?}', [EventController::class, 'add'])->name('add');
            Route::post('/add/{id?}', [EventController::class, 'store']);
        });
        Route::prefix('/promo_codes')->middleware('check_permissions:admin')->as('promo_codes.')->group(function(){
            Route::get('/',[PromoCodeController::class,'index'])->name('view');
            Route::get('/add',[PromoCodeController::class,'create'])->name('add');
            Route::post('/add',[PromoCodeController::class,'store']);
            Route::get('/edit/{id}',[PromoCodeController::class,'edit'])->name('edit');
            Route::get('/delete/{id}',[PromoCodeController::class,'destroy'])->name('delete');
            Route::get('/generate', [PromoCodeController::class, 'generate'])->name('generate');
            Route::post('/generate', [PromoCodeController::class, 'generate_store']);
            Route::get('/export', [PromoCodeController::class, 'export'])->name('export');
            Route::get('/{promo_code_id}/requests', [PromoCodeController::class, 'show_requests_for_promo_code'])->name('requests');
            Route::get('/{promo_code_id}/tickets', [PromoCodeController::class, 'show_tickets_for_promo_code'])->name('tickets');

        });
        Route::prefix('/event_settings')->middleware('check_permissions:admin')->as('event_settings.')->group(function(){
            Route::get('/event_days', [EventController::class, 'edit_event_days'])->name('event_days');
            Route::post('/event_days', [EventController::class, 'store_event_days']);
            Route::get('/theme', [EventController::class, 'edit_theme'])->name('theme');
            Route::post('/theme', [EventController::class, 'store_theme']);
            Route::get('/templates', [EmailTemplateController::class, 'view'])->name('templates');
            Route::get('/template', [EmailTemplateController::class, 'index'])->name('template');
            Route::get('/template/{type}', [EmailTemplateController::class, 'edit'])->name('template.edit');
            Route::post('/template', [EmailTemplateController::class, 'store'])->name('template.store');

            Route::get('/questions', [EventQuestionController::class, 'index'])->name('questions');
            Route::get('/questions/add', [EventQuestionController::class, 'add'])->name('questions.add');
            Route::post('/questions/add', [EventQuestionController::class, 'store']);
            Route::get('/questions/edit/{id}', [EventQuestionController::class, 'edit'])->name('questions.edit');
        });

        Route::resource('payment_methods', PaymentMethodController::class)->middleware('check_permissions:admin');

        // ! Admin and Organizer
        Route::middleware('check_permissions:organizer|admin')->group(function(){
            Route::get('/view_tickets/{id}', [PostController::class, 'view_tickets'])->name('view_tickets');
            Route::get('/view_tickets/{id}/{ticket_id}', [PostController::class, 'scan_ticket'])->name('scan_ticket');
        });

        Route::get('qr_progress', function () {
            return view('admin.qr_progress');
        })->name('qr_progress');

        Route::get('/requests', [PostController::class, 'view_requests'])->name('requests');
        Route::get('/requests/ticket-type/{ticket_type_id}', [PostController::class, 'view_ticket_type_requests'])->name('requests_by_ticket_type');

        // ! Admin only
        Route::middleware('check_permissions:admin')->group(function(){
            Route::get('generate_qr_codes', [QRCodeTicketController::class, 'generate_qr_codes'])->name('generate_qr_codes');
            Route::post('generate_qr_codes', [QRCodeTicketController::class, 'generate_qr_codes_post']);

            Route::get('generate_qr_tickets', [QRCodeTicketController::class, 'generate_qr_tickets'])->name('generate_qr_tickets');
            Route::post('generate_qr_tickets', [QRCodeTicketController::class, 'generate_qr_tickets_post']);

            Route::get('/requests/accept/{id}', [PostController::class, 'accept'])->name('accept');
            Route::get('/requests/reject/{id}', [PostController::class, 'reject'])->name('reject');
            Route::get('/requests/delete/{id}', [PostController::class, 'destroy'])->name('requests.delete');

            Route::get('/delete_all_tickets', [PostController::class, 'delete_all_view'])->name('delete_all');
            Route::post('/delete_all_tickets', [PostController::class, 'delete_all']);

            Route::get('/requests/export/{query?}/{ticket_id?}', [PostController::class, 'export'])->name('requests.export');

            Route::prefix('/tickets')->as('tickets.')->group(function () {
                Route::get('/add', [TicketController::class, 'add'])->name('add');
                Route::get('/edit/{id}', [TicketController::class, 'edit'])->name('edit');
                Route::post('/add', [TicketController::class, 'store']);
                Route::get('/trash/{id}', [TicketController::class, 'trash'])->name('delete');
                Route::get('/restore/{id}', [TicketController::class, 'restore'])->name('restore');
            });

            Route::prefix('/codes')->as('codes.')->group(function () {
                Route::get('/', [DiscountCodeController::class, 'index'])->name('view');
                Route::get('/add', [DiscountCodeController::class, 'create'])->name('add');
                Route::post('/add', [DiscountCodeController::class, 'store']);
                Route::get('/upload', [DiscountCodeController::class, 'upload'])->name('upload');
                Route::post('/upload', [DiscountCodeController::class, 'upload_store']);
                Route::get('/trash/{id}', [DiscountCodeController::class, 'trash'])->name('delete');
                Route::get('/restore/{id}', [DiscountCodeController::class, 'restore'])->name('restore');
            });

            Route::get('/import', [PostController::class, 'import_sheet'])->name('import');
            Route::post('/import', [PostController::class, 'import_sheet_store']);

            Route::prefix('/users')->as('users.')->group(function () {
                Route::get('/invite', [UserController::class, 'invite'])->name('invite');
                Route::post('/invite', [UserController::class, 'invite_post']);
                Route::get('/edit/{id}', [UserController::class, 'update'])->name('update');
                Route::post('/edit/{id}', [UserController::class, 'update_post']);
            });

            Route::prefix('/sub_tickets')->as('sub_tickets.')->group(function () {
                Route::get('/add', [SubTicketController::class, 'add'])->name('add');
                Route::post('/add', [SubTicketController::class, 'store']);
                Route::get('/trash/{id}', [SubTicketController::class, 'trash'])->name('delete');
                Route::get('/restore/{id}', [SubTicketController::class, 'restore'])->name('restore');
                Route::get('{id}/requests', [SubTicketController::class, 'view_posts'])->name('requests');
            });
        });


        Route::get('/', [DashboardController::class, 'index'])->name('home');


        Route::get('/requests/scan', [PostController::class, 'edit_requests'])->name('scan-requests');
        Route::post('/requests/action', [PostController::class, 'action'])->name('action');

        Route::prefix('/tickets')->as('tickets.')->group(function () {
            Route::get('/', [TicketController::class, 'view'])->name('view');
        });

        Route::prefix('/sub_tickets')->as('sub_tickets.')->group(function () {
            Route::get('/', [SubTicketController::class, 'view'])->name('view');
        });

        Route::prefix('/users')->as('users.')->group(function () {
            Route::get('/', [UserController::class, 'view'])->name('view');
        });

        Route::get('/register',[PostController::class, 'onspot_registration'])->name('register');
        Route::post('/register', [PostController::class, 'onspot_registration_post']);

    });
    Route::get('/settings', [UserController::class,'viewSettings'])->name('settings');
    Route::get('/settings/disable_telegram', [UserController::class,'disableTelegramNotifications'])->name('settings.disableTelegram');
});

Route::get('/{x_event_id}', [PostController::class, 'instructions'])->name('instructions');
Route::post( '/{x_event_id}', [PostController::class, 'register_request_view']);
Route::get('/{x_event_id}/code', [ PostController::class, 'instructions_code'])->name('promo_code');
Route::post('/{x_event_id}/code', [PostController::class, 'instructions_code_store']);
Route::get('/{x_event_id}/code/{code}', [PostController::class, 'instructions_code_show_tickets'])->name( 'promo_code_tickets');
Route::post('/{x_event_id}/code/{code}', [PostController::class, 'register_request_with_promo_view']);

Route::get('/{x_event_id}/payment-success',[PostController::class, 'payment_success'])->name('payment-success');

Route::get('/{x_event_id}/thank_you',[PostController::class, 'thank_you'])->name('thank_you');


Route::get('/test1234/x', function(){
    $user = Auth::user();
    $request = Post::find(145);
    $user->notify((new NewRequest($request)));
});