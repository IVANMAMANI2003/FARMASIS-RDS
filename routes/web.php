<?php

use App\Livewire\CategoryMain;
use App\Livewire\PurchaseMain;
use App\Livewire\SalesMain;
use App\Livewire\SupplierMain;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    Route::get('categories',CategoryMain::class)->name('categories');
    Route::get('suppliers',SupplierMain::class)->name('suppliers');
    Route::get('purchase',PurchaseMain::class)->name('purchase');
    Route::get('sales',SalesMain::class)->name('sales');
});

require __DIR__.'/auth.php';
