<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('components.favicon')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Kasir Dashboard') - UD Rumah Daun</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite('resources/css/backend/backend.css')

    
    
    @stack('styles')
    @vite('resources/css/backend/cashier.css')
    @vite('resources/css/backend/premium.css')
</head>
<body class="backend-page" data-user-role="{{ auth()->user()->role }}" data-authenticated="true">
    <div class="sidebar" id="cashier-sidebar">
        @php($pendingPaymentsCount = \App\Models\Payment::query()->where('status', 'pending')->count())
        <a href="{{ route('home') }}" class="sidebar-brand">
            <img src="{{ $siteLogoUrl }}" alt="Logo UD Rumah Daun">
            <span><strong>UD RUMAH DAUN</strong><small>AREA KASIR</small></span>
        </a>
        <ul class="sidebar-nav" aria-label="Menu kasir">
            <li class="nav-item">
                <a href="{{ route('kasir.dashboard') }}" class="nav-link {{ request()->routeIs('kasir.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link dropdown-toggle" aria-expanded="false" aria-controls="cashier-operations" onclick="toggleRoleSubmenu(event, 'cashier-operations')">
                    <i class="fas fa-wrench"></i><span>Operasional</span>
                    <i class="fas fa-chevron-right arrow" id="arrow-cashier-operations"></i>
                </a>
                <ul class="submenu" id="cashier-operations">
                    <li class="nav-item">
                        <a href="{{ route('kasir.orders') }}" class="nav-link {{ request()->routeIs('kasir.orders') ? 'active' : '' }}">
                            <i class="fas fa-calendar-check"></i><span>Pesanan Layanan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('kasir.pos') }}" class="nav-link {{ request()->routeIs('kasir.pos*') ? 'active' : '' }}">
                            <i class="fas fa-box-open"></i><span>Penjualan Produk</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('kasir.payments') }}" class="nav-link {{ request()->routeIs('kasir.payments') ? 'active' : '' }}">
                            <i class="fas fa-money-bill-wave"></i><span>Pembayaran</span>
                            @if($pendingPaymentsCount > 0)
                                <span class="badge rounded-pill bg-warning text-dark ms-1" aria-label="{{ $pendingPaymentsCount }} pembayaran menunggu konfirmasi">{{ $pendingPaymentsCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('kasir.invoices') }}" class="nav-link {{ request()->routeIs('kasir.invoices') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice"></i><span>Invoice</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item mt-3">
                <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i><span>Pengaturan</span>
                </a>
            </li>
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent">
                        <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
    <button type="button" class="sidebar-backdrop" aria-label="Tutup menu kasir" onclick="toggleCashierSidebar(false)"></button>

    <div class="content-wrapper">
        <div class="topbar">
            <button
                type="button"
                class="mobile-menu-toggle"
                aria-label="Buka menu kasir"
                aria-controls="cashier-sidebar"
                aria-expanded="false"
                onclick="toggleCashierSidebar()"
            >
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-title"><i class="fas fa-store me-2 text-warning"></i>@yield('page-title', 'Dashboard Kasir')</div>
            <div class="ms-auto d-flex align-items-center">
                <div class="user-pill">
                    <div class="user-avatar">
                        @if(auth()->user()->photo)
                            <img src="{{ asset('storage/' . auth()->user()->photo) }}" alt="Foto {{ auth()->user()->name }}">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <div class="fw-bold">{{ auth()->user()->name }}</div>
                        <small class="text-muted role-label">Kasir</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="main-content">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleCashierSidebar(open = null) {
            const sidebar = document.getElementById('cashier-sidebar');
            const backdrop = document.querySelector('.sidebar-backdrop');
            const toggle = document.querySelector('.mobile-menu-toggle');
            const shouldOpen = open === null
                ? !sidebar.classList.contains('open')
                : open;

            sidebar.classList.toggle('open', shouldOpen);
            backdrop.classList.toggle('show', shouldOpen);
            toggle.setAttribute('aria-expanded', String(shouldOpen));
            toggle.setAttribute('aria-label', shouldOpen ? 'Tutup menu kasir' : 'Buka menu kasir');
            toggle.querySelector('i').classList.toggle('fa-bars', !shouldOpen);
            toggle.querySelector('i').classList.toggle('fa-times', shouldOpen);
        }

        function toggleRoleSubmenu(event, submenuId) {
            event.preventDefault();
            const submenu = document.getElementById(submenuId);
            const arrow = document.getElementById('arrow-' + submenuId);
            const toggle = document.querySelector('[aria-controls="' + submenuId + '"]');
            const isOpen = submenu.classList.toggle('show');
            if (arrow) arrow.classList.toggle('rotated', isOpen);
            if (toggle) toggle.setAttribute('aria-expanded', String(isOpen));
        }

        document.addEventListener('DOMContentLoaded', () => {
            const activeLink = document.querySelector('.submenu .nav-link.active');
            const submenu = activeLink?.closest('.submenu');
            if (submenu) {
                submenu.classList.add('show');
                document.querySelector('[aria-controls="' + submenu.id + '"]')?.setAttribute('aria-expanded', 'true');
                document.getElementById('arrow-' + submenu.id)?.classList.add('rotated');
            }

            document.querySelectorAll('.sidebar a.nav-link:not(.dropdown-toggle)').forEach(link => {
                link.addEventListener('click', () => toggleCashierSidebar(false));
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                toggleCashierSidebar(false);
            }
        });
    </script>
    @stack('scripts')
</body>
</html>