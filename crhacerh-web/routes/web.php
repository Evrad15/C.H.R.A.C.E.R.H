<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\DashboardController;

// Page d'affichage de la borne (Interface Web)
Route::get('/kiosk', [KioskController::class, 'showView']);

// routes/web.php

// routes/web.php
Route::get('/login', function () {
    return redirect()->route('filament.ressources Humaines.auth.login');
})->name('login');