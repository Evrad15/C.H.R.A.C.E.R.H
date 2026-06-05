<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\Api\AttendanceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route publique pour le tableau de la borne QR
Route::get('/kiosk/recent-pointages', [KioskController::class, 'getRecentPointages']);

// Route pour soumettre un scan depuis le mobile
Route::post('/pointages/scan', [PointageController::class, 'scan']);

// Route pour vérifier le matricule en ligne
Route::post('/matricule/check', [AttendanceController::class, 'checkMatricule']);