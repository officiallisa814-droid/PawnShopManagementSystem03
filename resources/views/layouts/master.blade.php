<!DOCTYPE html>
<html lang="km">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pawn Shop Management System</title>

    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('images/logo.png') }}?v=5">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}?v=5">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}?v=5">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-pawn.png') }}">
    <!-- លីងត្រង់ទៅកាន់ CSS ទាំងពីរ -->
    <!-- <link rel="stylesheet" href="{{ asset('css/layout.css') }}"> -->
    <!-- <link rel="stylesheet" href="{{ asset('css/admin_css/dashboard.css') }}"> -->
    <!-- <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}?v=4">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}?v=4"> -->
    @stack('styles')
    <!-- លីងត្រង់ទៅកាន់ Icon Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css"
        integrity="sha512-QeR2VH+lsBE5LSAe1Q5EnTBbe7XTBubt8dG93Y7gidSgdMCr8nVqKcfKAMyN96SV8KDbZVTDXChatu5G2KQGzg=="
        crossorigin="anonymous" referrerpolicy="no-referrer">

</head>
<style>
    /* កំណត់ទម្រង់ដើមដំបូង */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background-color: #e9ebee;
    }

    /* រៀបចំប្រអប់ធំឱ្យចេញជា ២ ជួរឈរ (Sidebar ឆ្វេង និង Content ស្ដាំ) */
    /* រៀបចំប្រអប់ធំឱ្យចេញជា ២ ជួរឈរ (Sidebar ឆ្វេង និង Content ស្ដាំ) */
    .dashboard-container {
        display: flex;
        align-items: flex-start;
        /* Crucial: prevents the sidebar from stretching infinitely down */
        min-height: 100vh;
    }

    /* តុបតែង Sidebar ខាងឆ្វេង - Sticky Update */
    .sidebar {
        width: 260px;
        background-color: #0b2240;
        /* ពណ៌ខៀវចាស់ */
        color: #fff;
        padding: 25px 20px;
        display: flex;
        flex-direction: column;

        /* This combination locks the sidebar on scroll */
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto;
        /* Adds an internal scrollbar if your menu has too many items later */
    }


    .sidebar .logo {
        margin-bottom: 35px;
    }

    .sidebar .logo h2 {
        font-size: 22px;
        color: #fff;
    }

    .sidebar .logo p {
        font-size: 12px;
        color: #718096;
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

    /* Pushes the footer div to the absolute bottom of the 100vh flexbox container */
    .sidebar-footer {
        margin-top: auto;
        padding-top: 20px;
        border-top: 1px solid #1e293b;
        /* Subtle separation line */
    }

    /* Base styles for the logout action row */
    .sidebar-footer .logout-link {
        display: flex;
        align-items: center;
        gap: 15px;
        color: #f87171;
        /* Soft light red color text */
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

    /* Attention color changes on layout interaction */
    .sidebar-footer .logout-link:hover {
        background-color: #dc2626;
        /* Strong red alert background background */
        color: #ffffff;
    }






    /* តុបតែងផ្នែកខាងស្ដាំ */
    .main-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
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
        box-shadow: 0px 2px 1px #1e74ff;
    }

    .search-bar input {
        padding: 8px 15px;
        width: 300px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        outline: none;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .content-body {
        padding: 30px;
    }

    .logo {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        /* ប្តូរពី space-between */
        gap: 6px;
        /* គម្លាតរវាងអក្សរ និងឡូហ្គោ (កាត់បន្ថយបានទៀត) */
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


    /* ===== Responsive ===== */
    .menu-toggle {
        display: none;
        background: none;
        border: none;
        font-size: 22px;
        color: #0b2240;
        cursor: pointer;
        padding: 8px 12px;
    }

    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .5);
        z-index: 998;
    }

    .main-wrapper {
        min-width: 0;
    }

    /* =======================profile user ===================== */
    .user-profile {
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
        transition: background 0.15s ease, color 0.15s ease, transform 0.15s ease;
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

    @media (max-width: 900px) {
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
    }

    @media (max-width: 480px) {
        .sidebar {
            width: 80%;
        }

        .content-body {
            padding: 10px;
        }
    }

    /* ===== Mobile Dashboard ===== */
    @media (max-width: 900px) {

        /* កាតសរុប: ២ ជួរឈរ */
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

        /* grid ធំៗ: ១ ជួរឈរ */
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

        .card-header h3 {
            font-size: 1rem;
            margin-bottom: 12px;
        }

        .card-header {
            margin-bottom: 12px;
        }
    }

    @media (max-width: 480px) {
        .top-cards .card {
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }

        /* Donut ធំល្មម នៅកណ្តាល ហើយ legend ក្រោម */
        .donut-container {
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }

        .category-legend {
            width: 100%;
        }

        /* តារាងអូសឆ្វេងស្តាំក្នុងកាត */
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
</style>


<body>

    <div class="dashboard-container">

        <!-- ផ្នែក MENU / SIDEBAR (ខាងឆ្វេង) -->
        <div class="sidebar-overlay" id="sidebarOverlay"> </div>
        <aside class="sidebar">
            <div class="logo">
                <div class="logo-text">
                    <h2>{{ $shop['shop_name'] }}</h2>
                    <p><span>{{ $shop['shop_name'] }}</span> Management System</p>
                </div>
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img">
            </div>
            <nav class="menu-links">
                <a href="{{ route('dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i> Dashboard
                </a>

                <a href="{{ route('customer') }}" class="{{ request()->is('customer') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i> Customer
                </a>

                <a href="{{ route('pawnItem') }}" class="{{ request()->is('pawnItem') ? 'active' : '' }}">
                    <i class="fa-solid fa-box"></i> Pawn Item
                </a>

                <a href="{{ route('loanContract') }}" class="{{ request()->is('loanContract') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-contract"></i> Loan Contract
                </a>

                <a href="{{ route('payments') }}" class="{{ request()->is('payments') ? 'active' : '' }}">
                    <i class="fa-solid fa-credit-card"></i> Payments
                </a>

                <a href="{{ route('auction-sales') }}" class="{{ request()->is('auction-sales') ? 'active' : '' }}">
                    <i class="fa-solid fa-gavel"></i></i> Auction & Sales
                </a>

                <a href="{{ route('reports') }}" class="{{ request()->is('reports') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-column"></i> Reports
                </a>

                <a href="{{ route('settings') }}" class="{{ request()->is('settings') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>
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


        <!-- ផ្នែកខាងស្ដាំ -->
        <div class="main-wrapper">

            <!-- ផ្នែក HEADER (របារខាងលើ) -->
            <header class="top-header">
                <div class="search-bar">
                    <button type="button" class="menu-toggle" id="menuToggle" aria-label="Menu">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
                <div class="user-profile">
                    <button type="button" class="hdr-icon-btn" aria-label="ការជូនដំណឹង" title="ការជូនដំណឹង">
                        <i class="fa-regular fa-bell"></i>
                        <span class="hdr-dot"></span>
                    </button>

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

            <!-- ផ្នែក MAIN CONTENT -->
            <main class="content-body">
                @yield('content')
            </main>

        </div>
    </div>
    @stack('scripts')
    <script>
        (function () {
            var sb = document.querySelector('.sidebar');
            var ov = document.getElementById('sidebarOverlay');
            var btn = document.getElementById('menuToggle');
            function toggle(open) {
                sb.classList.toggle('open', open);
                ov.classList.toggle('show', open);
            }
            btn.addEventListener('click', function () { toggle(!sb.classList.contains('open')); });
            ov.addEventListener('click', function () { toggle(false); });
            sb.querySelectorAll('a').forEach(function (a) {
                a.addEventListener('click', function () { toggle(false); });
            });
        })();
    </script>
</body>

</html>