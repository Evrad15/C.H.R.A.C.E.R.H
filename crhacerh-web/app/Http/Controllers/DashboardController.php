<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'employes' => 200,
            'retardataires' => ['val' => 36, 'delta' => -10],
            'a_lheure'     => ['val' => 150, 'delta' => +20],
            'absents'      => ['val' => 14, 'delta' => -24],
        ];

        $graphData = [
            'Lundi'    => 230,
            'Mardi'    => 265,
            'Mercredi' => 245,
            'Jeudi'    => 290,
            'Vendredi' => 210,
            'Samedi'   => 260,
            'Dimanche' => 230,
        ];

        $logs = [
            ['action' => 'Départ',  'nom' => 'Paul Pogba',      'heure' => '17:45'],
            ['action' => 'Départ',  'nom' => 'Mobutu',          'heure' => '15:33'],
            ['action' => 'Arrivée', 'nom' => 'Mobutu',          'heure' => '14:56'],
            ['action' => 'Arrivée', 'nom' => 'Paul Pogba',      'heure' => '08:45'],
            ['action' => 'Arrivée', 'nom' => 'Monkey D Luffy',  'heure' => '08:30'],
        ];

        $navItems = [
            ['icon' => '📊', 'label' => 'Dashboard',  'active' => true, 'url' => route('dashboard')],
            ['icon' => '📅', 'label' => 'Calendrier', 'active' => false, 'url' => '#'],
            ['icon' => '👤', 'label' => 'Employés',   'active' => false, 'url' => '#'],
            ['icon' => '📋', 'label' => 'Rapports',   'active' => false, 'url' => '#'],
            ['icon' => '💬', 'label' => 'Chat',        'active' => false, 'url' => '#'],
            ['icon' => '🌐', 'label' => 'Borne Kiosk', 'active' => false, 'url' => url('/kiosk')],
        ];

        return view('dashboard.index', compact('stats', 'graphData', 'logs', 'navItems'));
    }
}