<?php

use App\Http\Controllers\DashboardController;
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

    Volt::route('entidades', 'entidades.index')->name('entidades.index');
    Volt::route('contratos', 'contratos.index')->name('contratos.index');
    Volt::route('contratos/{contrato}', 'contratos.show')->name('contratos.show');
    Volt::route('receber', 'receivable.index')->name('receber');
    Volt::route('pagar', 'payable.index')->name('pagar');
    Volt::route('conciliacao', 'conciliacao.index')->name('conciliacao');
    Route::view('hub-artista', 'hub-artista')->name('hub-artista');
    Route::view('internacional', 'internacional')->name('internacional');

    // Módulos do Sistema de Eventos (legacy - usar componentes Livewire)
    Route::view('contracts', 'contracts')->name('contracts');
});

require __DIR__.'/auth.php';
