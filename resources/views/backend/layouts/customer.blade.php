<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('components.favicon')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Customer Dashboard') - UD Rumah Daun</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite('resources/css/backend/backend.css')

    <!-- Custom CSS -->
    
    
    @stack('styles')
    @vite('resources/css/backend/customer.css')
    @vite('resources/css/backend/premium.css')
</head>
<body class="backend-page" data-user-role="{{ auth()->user()->role }}" data-authenticated="true">
    @php($unreadCustomerNotifications = auth()->user()->notifications()->unread()->count())
    <!-- Sidebar -->
    <nav class="sidebar" id="customer-sidebar" aria-label="Menu pelanggan">
        <a href="{{ route('home') }}" class="sidebar-brand">
            <img src="{{ $siteLogoUrl }}" alt="Logo UD Rumah Daun">
            <span><strong>UD RUMAH DAUN</strong><small>AREA PELANGGAN</small></span>
        </a>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('customer.dashboard') }}" class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link dropdown-toggle" aria-expanded="false" aria-controls="customer-shopping" onclick="toggleRoleSubmenu(event, 'customer-shopping')">
                    <i class="fas fa-shopping-bag"></i><span>Belanja</span>
                    <i class="fas fa-chevron-right arrow" id="arrow-customer-shopping"></i>
                </a>
                <ul class="submenu" id="customer-shopping">
                    <li class="nav-item">
                        <a href="{{ route('customer.products') }}" class="nav-link {{ request()->routeIs('customer.products') ? 'active' : '' }}">
                            <i class="fas fa-shopping-cart"></i><span>Produk</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('cart.index') }}" class="nav-link {{ request()->routeIs('cart.*') ? 'active' : '' }}">
                            <i class="fas fa-shopping-basket"></i><span>Keranjang</span>
                            @if(array_sum(session('cart', [])) > 0)
                                <span class="badge rounded-pill bg-warning text-dark ms-1">{{ array_sum(session('cart', [])) }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link dropdown-toggle" aria-expanded="false" aria-controls="customer-services" onclick="toggleRoleSubmenu(event, 'customer-services')">
                    <i class="fas fa-concierge-bell"></i><span>Layanan</span>
                    <i class="fas fa-chevron-right arrow" id="arrow-customer-services"></i>
                </a>
                <ul class="submenu" id="customer-services">
                    <li class="nav-item">
                        <a href="{{ route('customer.services') }}" class="nav-link {{ request()->routeIs('customer.services') ? 'active' : '' }}">
                            <i class="fas fa-list"></i><span>Semua Layanan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('customer.booking.create') }}" class="nav-link {{ request()->routeIs('customer.booking.*') ? 'active' : '' }}">
                            <i class="fas fa-calendar-plus"></i><span>Pesan Layanan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('customer.bookings') }}" class="nav-link {{ request()->routeIs('customer.bookings') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-list"></i><span>Pesanan Saya</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link dropdown-toggle" aria-expanded="false" aria-controls="customer-activity" onclick="toggleRoleSubmenu(event, 'customer-activity')">
                    <i class="fas fa-clipboard-list"></i><span>Aktivitas Saya</span>
                    <i class="fas fa-chevron-right arrow" id="arrow-customer-activity"></i>
                </a>
                <ul class="submenu" id="customer-activity">
                    <li class="nav-item">
                        <a href="{{ route('customer.vehicles') }}" class="nav-link {{ request()->routeIs('customer.vehicles*') ? 'active' : '' }}">
                            <i class="fas fa-car"></i><span>Kendaraan Saya</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('customer.transactions') }}" class="nav-link {{ request()->routeIs('customer.transactions') ? 'active' : '' }}">
                            <i class="fas fa-receipt"></i><span>Transaksi</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('customer.notifications') }}" class="nav-link {{ request()->routeIs('customer.notifications*') ? 'active' : '' }}">
                            <i class="fas fa-bell"></i><span>Notifikasi</span>
                            @if($unreadCustomerNotifications > 0)
                                <span class="badge rounded-pill bg-danger ms-1" aria-label="{{ $unreadCustomerNotifications }} notifikasi belum dibaca">{{ $unreadCustomerNotifications }}</span>
                            @endif
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
    </nav>
    <button type="button" class="sidebar-backdrop" aria-label="Tutup menu pelanggan" onclick="toggleSidebar(false)"></button>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Topbar -->
        <div class="topbar">
            <button type="button" class="mobile-menu-toggle" onclick="toggleSidebar()" aria-controls="customer-sidebar" aria-expanded="false" aria-label="Buka menu pelanggan">
                <i class="fas fa-bars"></i>
            </button>
            <div class="ms-auto d-flex align-items-center">
                <span class="me-3 text-gray-600">Halo, {{ auth()->user()->name }}</span>
                <span class="badge bg-success">{{ ucfirst(auth()->user()->role) }}</span>
                <span class="user-avatar ms-3">
                    @if(auth()->user()->photo)
                        <img src="{{ asset('storage/' . auth()->user()->photo) }}" alt="Foto {{ auth()->user()->name }}">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleRoleSubmenu(event, submenuId) {
            event.preventDefault();
            const submenu = document.getElementById(submenuId);
            const arrow = document.getElementById('arrow-' + submenuId);
            const toggle = document.querySelector('[aria-controls="' + submenuId + '"]');
            const isOpen = submenu.classList.toggle('show');
            if (arrow) arrow.classList.toggle('rotated', isOpen);
            if (toggle) toggle.setAttribute('aria-expanded', String(isOpen));
        }

        function toggleSidebar(open = null) {
            const sidebar = document.getElementById('customer-sidebar');
            const backdrop = document.querySelector('.sidebar-backdrop');
            const toggle = document.querySelector('.mobile-menu-toggle');
            const shouldOpen = open === null ? !sidebar.classList.contains('open') : open;
            sidebar.classList.toggle('open', shouldOpen);
            backdrop.classList.toggle('show', shouldOpen);
            toggle.setAttribute('aria-expanded', String(shouldOpen));
            toggle.setAttribute('aria-label', shouldOpen ? 'Tutup menu pelanggan' : 'Buka menu pelanggan');
            toggle.querySelector('i').classList.toggle('fa-bars', !shouldOpen);
            toggle.querySelector('i').classList.toggle('fa-times', shouldOpen);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const activeLinks = document.querySelectorAll('.submenu .nav-link.active');
            activeLinks.forEach(link => {
                const submenu = link.closest('.submenu');
                if (!submenu) return;
                submenu.classList.add('show');
                document.querySelector('[aria-controls="' + submenu.id + '"]')?.setAttribute('aria-expanded', 'true');
                document.getElementById('arrow-' + submenu.id)?.classList.add('rotated');
            });

            document.querySelectorAll('.sidebar a.nav-link:not(.dropdown-toggle)').forEach(link => {
                link.addEventListener('click', () => toggleSidebar(false));
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') toggleSidebar(false);
        });
    </script>
    
    @stack('scripts')
</body>
</html>