<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ScheduleShareController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Públicas — sem autenticação
|--------------------------------------------------------------------------
*/

Route::get('/login',    fn() => view('auth.login'))->name('login');
Route::post('/login',   [LoginController::class, 'store'])->name('login.store');
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register',[RegisterController::class, 'store'])->name('register.store');

Route::get('/auth/google/redirect',    [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback',    [SocialAuthController::class, 'handleGoogleCallback']);
Route::get('/auth/microsoft/redirect', [SocialAuthController::class, 'redirectToMicrosoft'])->name('auth.microsoft');
Route::get('/auth/microsoft/callback', [SocialAuthController::class, 'handleMicrosoftCallback']);

// Agendamento público via link/token (sem auth)
Route::get('/book/{token}',  [PublicBookingController::class, 'show'])->name('public.book');
Route::post('/book/{token}', [PublicBookingController::class, 'store'])->name('public.book.store');

/*
|--------------------------------------------------------------------------
| Rotas Autenticadas
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

    // Seleção / criação de tenant
    Route::get('/workspaces/create',  [TenantController::class, 'create'])->name('tenants.create');
    Route::post('/workspaces',        [TenantController::class, 'store'])->name('tenants.store');
    Route::post('/workspaces/{tenant}/switch', [TenantController::class, 'switch'])->name('tenants.switch');

    /*
    |----------------------------------------------------------------------
    | Rotas que exigem tenant ativo
    |----------------------------------------------------------------------
    */
    Route::middleware(['tenant'])->group(function () {

        // Dashboard
        Route::get('/', DashboardController::class)->name('dashboard');

        // Agendas
        Route::resource('schedules', ScheduleController::class);

        // Compartilhamento de agenda
        Route::prefix('schedules/{schedule}')->group(function () {
            Route::get('/share',                           [ScheduleShareController::class, 'index'])->name('schedules.share');
            Route::post('/shares',                         [ScheduleShareController::class, 'store'])->name('schedules.shares.store');
            Route::patch('/shares/{share}',                [ScheduleShareController::class, 'update'])->name('schedules.shares.update');
            Route::delete('/shares/{share}',               [ScheduleShareController::class, 'destroy'])->name('schedules.shares.destroy');
            Route::post('/booking-links',                  [ScheduleController::class, 'createBookingLink'])->name('schedules.booking-links.store');
            Route::delete('/booking-links/{link}',         [ScheduleController::class, 'revokeBookingLink'])->name('schedules.booking-links.destroy');
        });

        // Agendamentos — JSON (consumidos pelo React Calendar)
        Route::prefix('api/schedules/{schedule}')->group(function () {
            Route::get('/appointments',  [AppointmentController::class, 'index']);
            Route::post('/appointments', [AppointmentController::class, 'store']);
        });
        Route::prefix('api/appointments')->group(function () {
            Route::get('/{appointment}',         [AppointmentController::class, 'show']);
            Route::put('/{appointment}',         [AppointmentController::class, 'update']);
            Route::delete('/{appointment}',      [AppointmentController::class, 'destroy']);
            Route::patch('/{appointment}/status',[AppointmentController::class, 'updateStatus']);
        });

        /*
        |------------------------------------------------------------------
        | Admin — acesso restrito a admins do tenant
        |------------------------------------------------------------------
        */
        Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/',                              [AdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/users',                         [AdminController::class, 'users'])->name('users');
            Route::post('/users/invite',                 [AdminController::class, 'inviteUser'])->name('users.invite');
            Route::patch('/users/{user}/role',           [AdminController::class, 'updateUserRole'])->name('users.role');
            Route::delete('/users/{user}',               [AdminController::class, 'removeUser'])->name('users.destroy');
        });
    });
});
