<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\User\UserInvitationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Lead\LeadController;

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

    // Customer Management Routes
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
        Route::get('/search/api', [CustomerController::class, 'search'])->name('search.api');
        Route::get('/stats/api', [CustomerController::class, 'stats'])->name('stats.api');
    });

    // Contact Management Routes
    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/create', [ContactController::class, 'create'])->name('create');
        Route::post('/', [ContactController::class, 'store'])->name('store');
        Route::get('/search/api', [ContactController::class, 'search'])->name('search.api');
        Route::post('/bulk-action', [ContactController::class, 'bulkAction'])->name('bulk.action');

        // Nested contact routes for specific customers
        Route::prefix('/{customer}')->group(function () {
            Route::get('/', [ContactController::class, 'index'])->name('index');
            Route::get('/{contact}', [ContactController::class, 'show'])->name('show');
            Route::get('/{contact}/edit', [ContactController::class, 'edit'])->name('edit');
            Route::put('/{contact}', [ContactController::class, 'update'])->name('update');
            Route::delete('/{contact}', [ContactController::class, 'destroy'])->name('destroy');
            Route::post('/{contact}/make-primary', [ContactController::class, 'makePrimary'])->name('make.primary');
        });
    });

    // Lead Management Routes
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/', [LeadController::class, 'index'])->name('index');
        Route::get('/create', [LeadController::class, 'create'])->name('create');
        Route::post('/', [LeadController::class, 'store'])->name('store');
        Route::get('/search/api', [LeadController::class, 'search'])->name('search.api');
        Route::get('/stats/api', [LeadController::class, 'stats'])->name('stats.api');
        Route::post('/bulk-action', [LeadController::class, 'bulkAction'])->name('bulk.action');
        Route::post('/{lead}/convert', [LeadController::class, 'convert'])->name('convert');

        Route::get('/{lead}', [LeadController::class, 'show'])->name('show');
        Route::get('/{lead}/edit', [LeadController::class, 'edit'])->name('edit');
        Route::put('/{lead}', [LeadController::class, 'update'])->name('update');
        Route::delete('/{lead}', [LeadController::class, 'destroy'])->name('destroy');
    });
});

// Public invitation routes (no auth required)
Route::get('/invitation/{token}', [UserInvitationController::class, 'show'])->name('invitation.show');
Route::post('/invitation/{token}/accept', [UserInvitationController::class, 'accept'])->name('invitation.accept');

require __DIR__.'/settings.php';
