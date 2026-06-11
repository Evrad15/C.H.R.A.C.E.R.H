<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        
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