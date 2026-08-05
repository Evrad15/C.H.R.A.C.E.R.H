<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\RapportController;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

// Racine → redirige vers login ou dashboard
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Borne kiosque (accès public — interface biométrique)
Route::get('/kiosk', [KioskController::class, 'showView'])->name('kiosk');

/*
|--------------------------------------------------------------------------
| Routes protégées (authentification requise)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Tableau de bord
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestion des Employés
    Route::get('/employes',           [EmployeController::class, 'index'])->name('employes.index');
    Route::post('/employes',          [EmployeController::class, 'store'])->name('employes.store');
    Route::get('/employes/{id}',      [EmployeController::class, 'show'])->name('employes.show');
    Route::put('/employes/{id}',      [EmployeController::class, 'update'])->name('employes.update');
    Route::delete('/employes/{id}',   [EmployeController::class, 'destroy'])->name('employes.destroy');

    // Gestion des Rendez-vous
    Route::get('/rendez-vous',                    [RendezVousController::class, 'index'])->name('rendezvous.index');
    Route::post('/rendez-vous',                   [RendezVousController::class, 'store'])->name('rendezvous.store');
    Route::put('/rendez-vous/{id}',               [RendezVousController::class, 'update'])->name('rendezvous.update');
    Route::delete('/rendez-vous/{id}',            [RendezVousController::class, 'destroy'])->name('rendezvous.destroy');
    Route::patch('/rendez-vous/{id}/status',      [RendezVousController::class, 'updateStatus'])->name('rendezvous.updateStatus');

    // Gestion des Congés et Permissions
    Route::get('/conges',                [CongeController::class, 'index'])->name('conges.index');
    Route::post('/conges',               [CongeController::class, 'store'])->name('conges.store');
    Route::patch('/conges/{id}/status',  [CongeController::class, 'updateStatus'])->name('conges.updateStatus');

    // Rapports
    Route::get('/rapports',                          [RapportController::class, 'index'])->name('rapports.index');
    Route::post('/rapports/generate',                [RapportController::class, 'generate'])->name('rapports.generate');
    Route::post('/rapports/delete-file',             [RapportController::class, 'deleteFile'])->name('rapports.deleteFile');
    Route::get('/reports/preview/{filename}',        [RapportController::class, 'previewFile'])->where('filename', '.*')->name('rapports.preview');
    Route::get('/reports/open/{filename}',           [RapportController::class, 'openFile'])->where('filename', '.*')->name('rapports.open');
    Route::get('/reports/download/{filename}',       [RapportController::class, 'downloadFile'])->where('filename', '.*')->name('rapports.download');
    Route::get('/reports/legacy-open/{filename}',    [RapportController::class, 'openFile'])->where('filename', '.*')->name('report.open');
    Route::get('/reports/legacy-download/{filename}',[RapportController::class, 'downloadFile'])->where('filename', '.*')->name('report.download');

});

/*
|--------------------------------------------------------------------------
| Routes Google Calendar (OAuth callback — semi-public)
|--------------------------------------------------------------------------
*/
Route::get('/google/auth', function () {
    $client = new Google\Client();
    $client->setClientId(env('GOOGLE_CLIENT_ID'));
    $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
    $client->addScope(Google\Service\Calendar::CALENDAR);
    $client->addScope(Google\Service\Calendar::CALENDAR_READONLY);
    $client->setRedirectUri(env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/ressources-humaines/calendrier/callback'));
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');
    return redirect()->away($client->createAuthUrl());
})->name('google.auth')->middleware('auth');

Route::get('/ressources-humaines/calendrier/callback', function (Request $request) {
    $client = new Google\Client();
    $client->setClientId(env('GOOGLE_CLIENT_ID'));
    $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
    $client->setRedirectUri(env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/ressources-humaines/calendrier/callback'));
    if ($request->has('code')) {
        $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));
        session(['google_calendar_token' => $token]);
    }
    return redirect('/dashboard');
})->middleware('auth');

/*
|--------------------------------------------------------------------------
| Routes Breeze (login, logout, password reset, etc.)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
