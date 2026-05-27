<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KioskController;

Route::get('/', function () {
    return view('welcome');
});

// Page d'affichage de la borne (Interface Web)
Route::get('/kiosk', [KioskController::class, 'showView']);