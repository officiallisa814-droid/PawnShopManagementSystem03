<!DOCTYPE html>
<html lang="km">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pawn Shop Management System</title>

    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('images/logo.png') }}?v=5">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}?v=5">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}?v=5">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-pawn.png') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css"
        integrity="sha512-QeR2VH+lsBE5LSAe1Q5EnTBbe7XTBubt8dG93Y7gidSgdMCr8nVqKcfKAMyN96SV8KDbZVTDXChatu5G2KQGzg=="
        crossorigin="anonymous" referrerpolicy="no-referrer">

    @stack('styles')

    <style>
        /* ===== Base ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #e9ebee;
        }

        body.no-scroll {
            overflow: hidden;
        }

        .dashboard-container {
            display: flex;
            align-items: flex-start;
            min-height: 100vh;
        }

        /* ===== Sidebar ===== */
        .sidebar {
            width: 260px;
            background-color: #0b2240;
            color: #fff;
            padding: 25px 20px;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .sidebar .logo {
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 6px;
        }

        .sidebar .logo h2 {
            font-size: 22px;
            color: #fff;
        }

        .sidebar .logo p {
            font-size: 12px;
            color: #718096;
        }

        .logo-text {
            min-width: 0;
        }

        .logo-text h2,
        .logo-text p {
            margin: 0;
        }

        .logo-img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .sidebar-close {
            display: none;
        }

        .sidebar .menu-links a {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #a0aec0;
            padding: 12px 15px;
            text-decoration: none;
            margin-bottom: 8px;
            border-radius: 8px;
            font-size: 15px;
            transition: 0.3s;
        }

        .sidebar .menu-links a:hover,
        .sidebar .menu-links a.active {
            background-color: #1e74ff;
            color: #fff;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid #1e293b;
        }

        .sidebar-footer .logout-link {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #f87171;
            padding: 12px 15px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            transition: 0.2s ease;
            background: none;
            border: 0;
            width: 100%;
            cursor: pointer;
            text-align: left;
        }

        .sidebar-footer .logout-link:hover {
            background-color: #dc2626;
            color: #fff;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .5);
            z-index: 998;
        }

        /* ===== Main / header ===== */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .top-header {
            background-color: #fff;
            height: 70px;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 1px #1e74ff;
        }

        .search-bar {
            display: flex;
            align-items: center;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            color: #0b2240;
            cursor: pointer;
        }

        .content-body {
            padding: 30px;
        }

        /* ===== Header user area ===== */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .hdr-icon-btn {
            position: relative;
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 50%;
            background: #f1f5f9;
            color: #475569;
            font-size: 17px;
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .hdr-icon-btn:hover {
            background: #dbeafe;
            color: #1e74ff;
        }

        .hdr-icon-btn:hover i {
            animation: hdr-ring 0.6s ease;
        }

        .hdr-dot {
            position: absolute;
            top: 9px;
            right: 10px;
            width: 9px;
            height: 9px;
            background: #ef4444;
            border: 2px solid #fff;
            border-radius: 50%;
        }

        @keyframes hdr-ring {

            0%,
            100% {
                transform: rotate(0);
            }

            25% {
                transform: rotate(14deg);
            }

            50% {
                transform: rotate(-12deg);
            }

            75% {
                transform: rotate(8deg);
            }
        }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 14px 4px 4px;
            border-radius: 999px;
            text-decoration: none;
            color: inherit;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .user-chip:hover,
        .user-chip.active {
            background: #eff6ff;
            border-color: #bfdbfe;
            box-shadow: 0 2px 8px rgba(30, 116, 255, 0.12);
        }

        .user-avatar-wrap {
            position: relative;
            display: block;
            width: 40px;
            height: 40px;
            flex-shrink: 0;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px #dbeafe;
        }

        .user-avatar-letter {
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #1e74ff, #0b2240);
            color: #fff;
            font-weight: 700;
            font-size: 16px;
        }

        .user-status {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 11px;
            height: 11px;
            background: #22c55e;
            border: 2px solid #fff;
            border-radius: 50%;
        }

        .user-info h4 {
            font-size: 14px;
            line-height: 1.25;
            color: #0f172a;
            font-weight: 700;
        }

        .user-info span {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            color: #64748b;
        }

        .user-info span i {
            font-size: 10px;
            color: #f59e0b;
        }

        .nf-wrap {
            position: relative
        }

        .nf-menu {
            position: absolute;
            right: 0;
            top: 52px;
            width: 320px;
            max-width: calc(100vw - 24px);
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .18);
            z-index: 200;
            overflow: hidden
        }

        .nf-menu[hidden] {
            display: none
        }

        .nf-head {
            padding: 12px 16px;
            font-weight: 700;
            color: #0b2240;
            border-bottom: 1px solid #eef2f7
        }

        .nf-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            text-decoration: none;
            color: #1e293b;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9
        }

        .nf-item:hover {
            background: #f8fafc
        }

        .nf-ic {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            flex-shrink: 0
        }

        .nf-ic.red {
            background: #fee2e2;
            color: #dc2626
        }

        .nf-ic.amber {
            background: #fef3c7;
            color: #d97706
        }

        .nf-ic.blue {
            background: #dbeafe;
            color: #2563eb
        }

        .nf-empty {
            padding: 20px;
            text-align: center;
            color: #94a3b8;
            font-size: 14px
        }

        /* =====================================================
           MOBILE / TABLET  (≤ 900px)
           ===================================================== */
        @media (max-width: 900px) {
            .dashboard-container {
                display: block;
            }

            /* off-canvas sidebar */
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 280px;
                max-width: 85%;
                height: 100vh;
                height: 100dvh;
                z-index: 999;
                transform: translateX(-100%);
                transition: transform .28s ease;
                padding-top: calc(25px + env(safe-area-inset-top, 0px));
                padding-bottom: calc(20px + env(safe-area-inset-bottom, 0px));
                -webkit-overflow-scrolling: touch;
                overscroll-behavior: contain;
            }

            .sidebar.open {
                transform: none;
                box-shadow: 0 0 40px rgba(0, 0, 0, .45);
            }

            .sidebar .logo {
                position: relative;
                padding-right: 36px;
            }

            .sidebar-close {
                display: grid;
                place-items: center;
                position: absolute;
                top: -4px;
                right: -6px;
                width: 36px;
                height: 36px;
                border: 0;
                border-radius: 10px;
                background: rgba(255, 255, 255, .08);
                color: #cbd5e1;
                font-size: 16px;
                cursor: pointer;
            }

            .sidebar-close:active {
                background: rgba(255, 255, 255, .18);
            }

            .sidebar-overlay {
                display: block;
                opacity: 0;
                visibility: hidden;
                transition: opacity .25s ease, visibility .25s ease;
                backdrop-filter: blur(2px);
            }

            .sidebar-overlay.show {
                opacity: 1;
                visibility: visible;
            }

            .sidebar .menu-links a,
            .sidebar-footer .logout-link {
                min-height: 48px;
            }

            /* header */
            .top-header {
                height: 60px;
                padding: 0 12px;
                padding-top: env(safe-area-inset-top, 0px);
                box-shadow: 0 2px 0 #1e74ff;
            }

            .menu-toggle {
                display: inline-grid;
                place-items: center;
                width: 42px;
                height: 42px;
                padding: 0;
                border-radius: 12px;
                background: #f1f5f9;
            }

            .menu-toggle:active {
                background: #dbeafe;
            }

            .main-wrapper {
                width: 100%;
            }

            .content-body {
                padding: 16px 14px;
            }

            .user-profile {
                gap: 8px;
            }

            .user-chip {
                padding: 3px;
                background: transparent;
                border-color: transparent;
            }

            .user-info {
                display: none;
            }

            .hdr-icon-btn {
                width: 40px;
                height: 40px;
            }

            /* dashboard: summary cards 2 columns */
            .top-cards {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                margin-bottom: 12px;
            }

            .top-cards .card {
                padding: 12px;
                gap: 10px;
                min-width: 0;
                flex: none;
            }

            .card-info {
                min-width: 0;
            }

            .card-info span {
                font-size: 0.75rem;
                line-height: 1.2;
            }

            .card-info h3 {
                font-size: 1.05rem;
                word-break: break-word;
            }

            .icon-circle {
                width: 38px;
                height: 38px;
            }

            .icon-circle i {
                font-size: 1rem;
            }

            .dashboard-grid,
            .dashboard-bottom-grid {
                grid-template-columns: 1fr;
                gap: 12px;
                margin-bottom: 12px;
            }

            .grid-card {
                padding: 14px;
                min-width: 0;
            }

            .card-header {
                margin-bottom: 12px;
            }

            .card-header h3 {
                font-size: 1rem;
                margin-bottom: 12px;
            }
        }

        @media (max-width: 480px) {
            .content-body {
                padding: 12px 10px;
            }

            .top-cards .card {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }

            .donut-container {
                flex-direction: column;
                align-items: center;
                gap: 14px;
            }

            .category-legend {
                width: 100%;
            }

            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .pawn-table {
                min-width: 520px;
            }

            .pawn-table th,
            .pawn-table td {
                padding: 10px 12px;
            }

            .view-all-btn {
                padding: 8px 14px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .sidebar,
            .sidebar-overlay {
                transition: none;
            }

            .hdr-icon-btn:hover i {
                animation: none;
            }
        }
    </style>
</head>

<body>

    <div class="dashboard-container">

        <!-- Sidebar overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <div class="logo-text">
                    <h2>{{ $shop['shop_name'] }}</h2>
                    <p><span>{{ $shop['shop_name'] }}</span> Management System</p>
                </div>
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img">
                <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Close menu">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <nav class="menu-links">
                @can('dashboard.view')
                    <a href="{{ route('dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-pie"></i> Dashboard
                    </a>
                @endcan
                @can('customers.view')
                    <a href="{{ route('customer') }}" class="{{ request()->is('customer*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i> Customer
                    </a>
                @endcan
                @can('pawnitems.view')
                    <a href="{{ route('pawnItem') }}" class="{{ request()->is('pawnItem*') ? 'active' : '' }}">
                        <i class="fa-solid fa-box"></i> Pawn Item
                    </a>
                @endcan
                @can('contracts.view')
                    <a href="{{ route('loanContract') }}" class="{{ request()->is('loanContract*') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-contract"></i> Loan Contract
                    </a>
                @endcan
                @can('payments.view')
                    <a href="{{ route('payments') }}" class="{{ request()->is('payments*') ? 'active' : '' }}">
                        <i class="fa-solid fa-credit-card"></i> Payments
                    </a>
                @endcan
                @can('auctions.view')
                    <a href="{{ route('auction-sales') }}" class="{{ request()->is('auction-sales*') ? 'active' : '' }}">
                        <i class="fa-solid fa-gavel"></i> Auction & Sales
                    </a>
                @endcan
                @can('reports.view')
                    <a href="{{ route('reports') }}" class="{{ request()->is('reports*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-column"></i> Reports
                    </a>
                @endcan
                @can('settings.manage')
                    <a href="{{ route('settings') }}" class="{{ request()->is('settings*', 'users*') ? 'active' : '' }}">
                        <i class="fa-solid fa-gear"></i> Settings
                    </a>
                @endcan
            </nav>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-link">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Right side -->
        <div class="main-wrapper">

            <header class="top-header">
                <div class="search-bar">
                    <button type="button" class="menu-toggle" id="menuToggle" aria-label="Open menu"
                        aria-controls="sidebar" aria-expanded="false">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>

                <div class="user-profile">
                    <div class="nf-wrap">
                        <button type="button" class="hdr-icon-btn" id="nfBtn" aria-label="ការជូនដំណឹង"
                            title="ការជូនដំណឹង">
                            <i class="fa-regular fa-bell"></i>
                            @if(count($notifs ?? []))<span class="hdr-dot"></span>@endif
                        </button>
                        <div class="nf-menu" id="nfMenu" hidden>
                            <div class="nf-head">ការជូនដំណឹង</div>
                            @forelse(($notifs ?? []) as $n)
                                <a href="{{ $n['url'] }}" class="nf-item">
                                    <span class="nf-ic {{ $n['color'] }}"><i class="fa-solid {{ $n['icon'] }}"></i></span>
                                    <span>{{ $n['text'] }}</span>
                                </a>
                            @empty
                                <div class="nf-empty">មិនមានការជូនដំណឹងថ្មី</div>
                            @endforelse
                        </div>
                    </div>

                    @php $u = auth()->user(); @endphp
                    <a href="{{ route('profile') }}" class="user-chip {{ request()->is('profile') ? 'active' : '' }}"
                        title="My Profile">
                        <span class="user-avatar-wrap">
                            @if ($u->avatar)
                                <img src="{{ asset('storage/' . $u->avatar) }}" alt="" class="user-avatar">
                            @else
                                <span
                                    class="user-avatar user-avatar-letter">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                            @endif
                            <span class="user-status" title="Online"></span>
                        </span>

                        <div class="user-info">
                            <h4>{{ $u->name }}</h4>
                            <span>
                                <i class="fa-solid {{ ($u->role ?? '') === 'admin' ? 'fa-crown' : 'fa-user-tie' }}"></i>
                                {{ ($u->role ?? '') === 'admin' ? 'System Administrator' : 'Staff' }}
                            </span>
                        </div>
                    </a>
                </div>
            </header>

            <main class="content-body">
                @yield('content')
            </main>

        </div>
    </div>

    @stack('scripts')

    <script>
        (function () {
            var sb = document.getElementById('sidebar');
            var ov = document.getElementById('sidebarOverlay');
            var btn = document.getElementById('menuToggle');
            var closeBtn = document.getElementById('sidebarClose');

            function toggle(open) {
                sb.classList.toggle('open', open);
                ov.classList.toggle('show', open);
                document.body.classList.toggle('no-scroll', open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            btn.addEventListener('click', function () { toggle(!sb.classList.contains('open')); });
            ov.addEventListener('click', function () { toggle(false); });
            closeBtn.addEventListener('click', function () { toggle(false); });
            sb.querySelectorAll('a').forEach(function (a) {
                a.addEventListener('click', function () { toggle(false); });
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') toggle(false);
            });
            window.addEventListener('resize', function () {
                if (window.innerWidth > 900) toggle(false);
            });
        })();

        
        (function () {
            var b = document.getElementById('nfBtn'), m = document.getElementById('nfMenu');
            if (!b || !m) return;
            b.addEventListener('click', function (e) { e.stopPropagation(); m.hidden = !m.hidden });
            document.addEventListener('click', function (e) { if (!m.contains(e.target)) m.hidden = true });
        })();
    </script>
</body>

</html>