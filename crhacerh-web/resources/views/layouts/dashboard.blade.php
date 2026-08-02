<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tableau de bord | C.H.R.A.C.E.R.H')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bg-main: #F6F6FB;
            --sidebar-bg: #FFFFFF;
            --card-bg: #FFFFFF;
            --text-dark: #1F384C;
            --text-muted: #737B8B;
            --primary: #5A6ACF;
            --primary-light: #DEE1F4;
            --accent-green: #16DE42;
            --accent-orange: #FF9B43;
            --accent-red: #F2383A;
            --border-color: #E2E7E7;
            --shadow-subtle: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        body::-webkit-scrollbar,
        *::-webkit-scrollbar {
            display: none;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 1.5rem 1rem;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }

        .brand-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.5rem 1.5rem 0.5rem;
        }

        .brand-logo {
            max-height: 82px;
            max-width: 100%;
            height: auto;
            object-fit: contain;
        }

        .brand-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.5px;
        }

        .nav-menu {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            list-style: none;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .nav-item a i {
            font-size: 1.15rem;
            color: var(--text-muted);
            transition: all 0.2s ease;
        }

        .nav-item.active a {
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
        }

        .nav-item.active a i {
            color: var(--primary);
        }

        .nav-item a:hover:not(.active) {
            background-color: #F1F2F7;
            color: var(--text-dark);
        }

        /* Main Area */
        .main-wrapper {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Header */
        .top-header {
            height: 70px;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2.5rem;
        }

        .search-box {
            position: relative;
            width: 320px;
        }

        .search-box input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.4rem;
            background: #F1F2F7;
            border: 1px solid transparent;
            border-radius: 20px;
            font-size: 0.9rem;
            color: var(--text-dark);
            outline: none;
            transition: all 0.2s ease;
        }

        .search-box input:focus {
            background: #FFFFFF;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(90, 106, 207, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9AA1B0;
            font-size: 0.9rem;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            cursor: pointer;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            transition: background 0.2s;
        }

        .user-profile:hover {
            background: rgba(0, 0, 0, 0.03);
        }

        .avatar-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #E8ECF5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
            font-size: 1rem;
        }

        .user-name {
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        /* Content Container */
        .content-body {
            padding: 1.5rem 2.5rem 2.5rem 2.5rem;
            flex: 1;
        }

        @yield('custom_styles')
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="brand-header">
            <img src="/images/logo_chracerh-removebg-preview.png" alt="C.H.R.A.C.E.R.H" class="brand-logo">
        </div>
        <ul class="nav-menu">
            <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>Tableau de Bord</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('employes.index') ? 'active' : '' }}">
                <a href="{{ route('employes.index') }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Employés</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/ressources-humaines/rendez-vouses">
                    <i class="bi bi-calendar-event"></i>
                    <span>Rendez-vous</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/ressources-humaines/conges">
                    <i class="bi bi-file-earmark-check-fill"></i>
                    <span>Congés et permissions</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/ressources-humaines/rapports">
                    <i class="bi bi-journal-text"></i>
                    <span>Rapports</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <header class="top-header">
            <form action="{{ route('employes.index') }}" method="GET" class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un employé par Nom ou Prénom...">
                @if(request('department_id'))
                    <input type="hidden" name="department_id" value="{{ request('department_id') }}">
                @endif
            </form>
            <div class="user-profile">
                <div class="avatar-icon">
                    <i class="bi bi-person-fill"></i>
                </div>
                <span class="user-name">Admin</span>
                <i class="bi bi-chevron-down" style="font-size: 0.75rem; color: var(--text-muted);"></i>
            </div>
        </header>

        <main class="content-body">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @yield('scripts')
</body>
</html>
