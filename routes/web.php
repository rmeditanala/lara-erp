<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\User\UserInvitationController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Company Registration Routes
Route::prefix('company')->name('company.')->group(function () {
    Route::get('/register', [CompanyController::class, 'create'])->name('register');
    Route::post('/register', [CompanyController::class, 'store']);
});

// Protected Routes with Company Middleware
Route::middleware(['auth', 'verified', 'ensure.company'])->group(function () {
    Route::get('dashboard', [CompanyController::class, 'dashboard'])->name('dashboard');

    // Company Management Routes
    Route::prefix('company')->name('company.')->group(function () {
        Route::get('/settings', [CompanyController::class, 'settings'])->name('settings');
        Route::put('/settings', [CompanyController::class, 'updateSettings'])->name('settings.update');
        Route::get('/subscription', [CompanyController::class, 'subscription'])->name('subscription');
    });

    // User Management Routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/invitations', [UserInvitationController::class, 'index'])->name('invitations.index');
        Route::get('/invitations/create', [UserInvitationController::class, 'create'])->name('invitations.create');
        Route::post('/invitations', [UserInvitationController::class, 'store'])->name('invitations.store');
        Route::delete('/invitations/{invitation}', [UserInvitationController::class, 'destroy'])->name('invitations.destroy');
    });
});

// Public invitation routes (no auth required)
Route::get('/invitation/{token}', [UserInvitationController::class, 'show'])->name('invitation.show');
Route::post('/invitation/{token}/accept', [UserInvitationController::class, 'accept'])->name('invitation.accept');

require __DIR__.'/settings.php';
