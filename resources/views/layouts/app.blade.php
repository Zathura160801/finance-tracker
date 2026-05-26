<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SakuPremium - Catatan Keuangan Masa Kini')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- Lucide Icons via CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Flatpickr CSS & JS CDN for Sleek Datepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        /* Responsive Layout styles */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .app-sidebar {
            width: 280px;
            background: rgba(12, 12, 24, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid var(--border-color);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 2.5rem;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            transition: var(--transition-smooth);
        }

        .app-content {
            flex-grow: 1;
            margin-left: 280px;
            padding: 2.5rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            gap: 2rem;
            transition: var(--transition-smooth);
            padding-bottom: 5rem; /* Space for mobile navbar */
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .sidebar-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.85rem 1.25rem;
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: var(--transition-smooth);
            border: 1px solid transparent;
        }

        .sidebar-item a:hover {
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-primary);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .sidebar-item.active a {
            background: rgba(139, 92, 246, 0.12);
            color: var(--primary-light);
            border-color: rgba(139, 92, 246, 0.2);
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.05);
        }

        /* Mobile Bottom Navbar Style */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 1.25rem;
            left: 1.25rem;
            right: 1.25rem;
            background: rgba(18, 18, 29, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 999px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), var(--shadow-glow);
            z-index: 999;
            padding: 0.5rem 1rem;
            justify-content: space-around;
            align-items: center;
            transition: var(--transition-smooth);
        }

        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 0.65rem;
            font-weight: 600;
            gap: 4px;
            padding: 0.5rem 0.75rem;
            border-radius: 999px;
            transition: var(--transition-smooth);
        }

        .mobile-nav-item i {
            width: 20px;
            height: 20px;
        }

        .mobile-nav-item:hover {
            color: var(--text-primary);
        }

        .mobile-nav-item.active {
            color: var(--primary-light);
            background: rgba(139, 92, 246, 0.15);
        }

        /* Mobile Adjustments */
        @media (max-width: 1024px) {
            .app-sidebar {
                transform: translateX(-100%);
                pointer-events: none;
            }

            .app-content {
                margin-left: 0;
                padding: 1.5rem;
                padding-bottom: 6.5rem;
            }

            .mobile-bottom-nav {
                display: flex;
            }
        }
    </style>
</head>

<body>

    <div class="app-wrapper">
        <!-- SIDEBAR DESKTOP -->
        <aside class="app-sidebar">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                <div class="logo-icon">
                    <i data-lucide="wallet" style="color: white; width: 22px; height: 22px;"></i>
                </div>
                <div class="logo-text">SakuPremium</div>
            </a>

            <ul class="sidebar-menu">
                <li class="sidebar-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i data-lucide="grid"></i> Ringkasan
                    </a>
                </li>
                <li class="sidebar-item {{ Request::routeIs('accounts.index') ? 'active' : '' }}">
                    <a href="{{ route('accounts.index') }}">
                        <i data-lucide="credit-card"></i> Rekening Saya
                    </a>
                </li>
                <li class="sidebar-item {{ Request::routeIs('transactions.index') ? 'active' : '' }}">
                    <a href="{{ route('transactions.index') }}">
                        <i data-lucide="receipt"></i> Riwayat Transaksi
                    </a>
                </li>
                <li class="sidebar-item {{ Request::routeIs('debts.index') ? 'active' : '' }}">
                    <a href="{{ route('debts.index') }}">
                        <i data-lucide="users"></i> Hutang & Piutang
                    </a>
                </li>
                <li class="sidebar-item {{ Request::routeIs('deposits.index') ? 'active' : '' }}">
                    <a href="{{ route('deposits.index') }}">
                        <i data-lucide="shield-check"></i> Uang Deposit
                    </a>
                </li>
            </ul>

            <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-color); font-size: 0.75rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 4px;">
                <div>SakuPremium v2.0</div>
                <div>Premium Personal Finance</div>
            </div>
        </aside>

        <!-- MAIN APP CONTENT -->
        <main class="app-content">
            <!-- MOBILE BRAND HEADER -->
            <div class="glass-panel" style="display: none; justify-content: space-between; align-items: center; padding: 0.85rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 0.5rem;" id="mobile-top-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div class="logo-icon" style="width: 32px; height: 32px; border-radius: 8px;">
                        <i data-lucide="wallet" style="color: white; width: 16px; height: 16px;"></i>
                    </div>
                    <span style="font-size: 1.15rem; font-weight: 700; font-family: var(--font-heading); color: var(--text-primary);">SakuPremium</span>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-secondary); background: rgba(255,255,255,0.06); padding: 4px 10px; border-radius: 20px; font-weight: 600;">
                    Live Tracker
                </div>
            </div>
            
            <style>
                @media (max-width: 1024px) {
                    #mobile-top-header {
                        display: flex !important;
                    }
                }
            </style>

            <!-- ALERTS NOTIFIKASI GLOBAL -->
            @if (session('success'))
                <div class="alert alert-success">
                    <i data-lucide="check-circle-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    <i data-lucide="alert-triangle"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            <!-- CONTENT SLOT -->
            @yield('content')
        </main>
    </div>

    <!-- FLOATING BOTTOM NAV BAR (MOBILE ONLY) -->
    <nav class="mobile-bottom-nav">
        <a href="{{ route('dashboard') }}" class="mobile-nav-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="grid"></i>
            <span>Ringkasan</span>
        </a>
        <a href="{{ route('accounts.index') }}" class="mobile-nav-item {{ Request::routeIs('accounts.index') ? 'active' : '' }}">
            <i data-lucide="credit-card"></i>
            <span>Rekening</span>
        </a>
        <a href="{{ route('transactions.index') }}" class="mobile-nav-item {{ Request::routeIs('transactions.index') ? 'active' : '' }}">
            <i data-lucide="receipt"></i>
            <span>Transaksi</span>
        </a>
        <a href="{{ route('debts.index') }}" class="mobile-nav-item {{ Request::routeIs('debts.index') ? 'active' : '' }}">
            <i data-lucide="users"></i>
            <span>Hutang</span>
        </a>
        <a href="{{ route('deposits.index') }}" class="mobile-nav-item {{ Request::routeIs('deposits.index') ? 'active' : '' }}">
            <i data-lucide="shield-check"></i>
            <span>Deposit</span>
        </a>
    </nav>

    <!-- INITIALIZE LUCIDE ICONS -->
    <script>
        lucide.createIcons();

        // Global Modal helpers
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
            }
        }

        function closeModalOnOverlay(event, modalId) {
            if (event.target.id === modalId) {
                closeModal(modalId);
            }
        }

        // Initialize Flatpickr datepickers globally
        document.addEventListener("DOMContentLoaded", function() {
            flatpickr(".datepicker-date", {
                theme: "dark",
                dateFormat: "Y-m-d",
                allowInput: true
            });

            flatpickr(".datepicker-datetime", {
                theme: "dark",
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: true,
                allowInput: true
            });
        });
    </script>
    @yield('scripts')
</body>

</html>
