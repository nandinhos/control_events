<?php

use App\Http\Controllers\DashboardController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Entidades - accessible by contract, receivable, admin_finance
    Route::middleware(['role:' . User::ROLE_CONTRACT . ',' . User::ROLE_RECEIVABLE . ',' . User::ROLE_ADMIN_FINANCE])
        ->group(function () {
            Volt::route('entidades', 'entidades.index')->name('entidades.index');
        });

    // Contratos - accessible by contract, receivable, admin_finance
    Route::middleware(['role:' . User::ROLE_CONTRACT . ',' . User::ROLE_RECEIVABLE . ',' . User::ROLE_ADMIN_FINANCE])
        ->group(function () {
            Volt::route('contratos', 'contratos.index')->name('contratos.index');
            Volt::route('contratos/{contrato}', 'contratos.show')->name('contratos.show');
        });

    // Receber - only receivable and admin_finance
    Route::middleware(['role:' . User::ROLE_RECEIVABLE . ',' . User::ROLE_ADMIN_FINANCE])
        ->group(function () {
            Volt::route('receber', 'receivable.index')->name('receber');
        });

    // Pagar - only payable and admin_finance
    Route::middleware(['role:' . User::ROLE_PAYABLE . ',' . User::ROLE_ADMIN_FINANCE])
        ->group(function () {
            Volt::route('pagar', 'payable.index')->name('pagar');
        });

    // Conciliacao - only receivable, payable, admin_finance
    Route::middleware(['role:' . User::ROLE_RECEIVABLE . ',' . User::ROLE_PAYABLE . ',' . User::ROLE_ADMIN_FINANCE])
        ->group(function () {
            Volt::route('conciliacao', 'conciliacao.index')->name('conciliacao');
        });

    // Internacional - only international and admin_finance
    Route::middleware(['role:' . User::ROLE_INTERNATIONAL . ',' . User::ROLE_ADMIN_FINANCE])
        ->group(function () {
            Route::view('internacional', 'internacional')->name('internacional');
        });

    // Hub artista - accessible by all authenticated users (no role restriction)
    Route::view('hub-artista', 'hub-artista')->name('hub-artista');

    // Legacy contracts module - accessible by contract, receivable, admin_finance
    Route::middleware(['role:' . User::ROLE_CONTRACT . ',' . User::ROLE_RECEIVABLE . ',' . User::ROLE_ADMIN_FINANCE])
        ->group(function () {
            Route::view('contracts', 'contracts')->name('contracts');
        });
});

require __DIR__.'/auth.php';
