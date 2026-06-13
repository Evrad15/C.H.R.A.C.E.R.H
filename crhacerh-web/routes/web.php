<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\KioskController;
use App\Filament\Resources\RapportResource;

// Page d'affichage de la borne (Interface Web)
Route::get('/kiosk', [KioskController::class, 'showView']);

// Redirection globale de la route d'authentification par défaut
Route::get('/login', function () {
    return redirect()->route('filament.ressources-humaines.auth.login');
})->name('login');

// --- Routes d'intégration pour l'API Google Calendar ---
// 1. Route pour envoyer l'utilisateur chez Google
Route::get('/google/auth', function () {
    $client = new Google\Client();
    $client->setClientId(env('GOOGLE_CLIENT_ID'));
    $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
    $client->addScope(Google\Service\Calendar::CALENDAR);

    $redirectUri = env('GOOGLE_REDIRECT_URI') ?? 'http://localhost:8000/ressources-humaines/calendrier/callback';
    $client->setRedirectUri($redirectUri);

    $client->addScope(Google\Service\Calendar::CALENDAR_READONLY);
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    return redirect()->away($client->createAuthUrl());
})->name('google.auth');

// 2. Route de retour appelée par Google
Route::get('/ressources-humaines/calendrier/callback', function (Request $request) {
    $client = new Google\Client();
    $client->setClientId(env('GOOGLE_CLIENT_ID'));
    $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));

    $redirectUri = env('GOOGLE_REDIRECT_URI') ?? 'http://localhost:8000/ressources-humaines/calendrier/callback';
    $client->setRedirectUri($redirectUri);

    if ($request->has('code')) {
        $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));
        session(['google_calendar_token' => $token]);
    }

    return redirect('/ressources-humaines/calendrier');
});

// --- Routes de gestion des rapports (Contourne l'erreur 403 du symlink) ---
Route::get('/reports/open/{filename}', function ($filename) {
    $path = 'reports/' . $filename;
    if (Storage::disk('public')->exists($path)) {
        return response()->file(Storage::disk('public')->path($path));
    }
    abort(404, 'Rapport introuvable');
})->name('report.open');

Route::get('/reports/download/{filename}', function ($filename) {
    $path = 'reports/' . $filename;
    if (Storage::disk('public')->exists($path)) {
        return response()->download(Storage::disk('public')->path($path));
    }
    abort(404, 'Rapport introuvable');
})->name('report.download');
