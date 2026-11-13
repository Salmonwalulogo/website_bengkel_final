<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('components.favicon')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') - UD Rumah Daun</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite('resources/css/backend/backend.css')

    
    
    @stack('styles')
    @vite('resources/css/backend/admin.css')
    @vite('resources/css/backend/premium.css')
</head>
<body class="backend-page" data-user-role="{{ auth()->user()->role }}" data-authenticated="true">
    <!-- Sidebar -->
    <div class="sidebar" id="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand" aria-label="UD Rumah Daun - Dashboard Admin">
            <img src="{{ $siteLogoUrl }}" alt="Logo UD Rumah Daun">
            <span><strong>UD RUMAH DAUN</strong><small>ADMIN PANEL</small></span>
        </a>
        <ul class="sidebar-nav">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>

            <!-- Data usaha -->
            <li class="nav-item">
                <a href="#" class="nav-link dropdown-toggle" aria-expanded="false" aria-controls="submenu-master" onclick="toggleSubmenu(event, 'submenu-master')">
                    <i class="fas fa-database"></i><span>Data Usaha</span>
                    <i class="fas fa-chevron-right arrow" id="arrow-master"></i>
                </a>
                <ul class="submenu" id="submenu-master">
                    <li class="nav-item">
                        <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                            <i class="fas fa-tags"></i> Kategori
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <i class="fas fa-box"></i> Produk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.services.index') }}" class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                            <i class="fas fa-wrench"></i> Layanan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.vehicles.index') }}" class="nav-link {{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
                            <i class="fas fa-motorcycle"></i> Kendaraan
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Operasional -->
            <li class="nav-item">
                <a href="#" class="nav-link dropdown-toggle" aria-expanded="false" aria-controls="submenu-operasional" onclick="toggleSubmenu(event, 'submenu-operasional')">
                    <i class="fas fa-clipboard-check"></i><span>Operasional</span>
                    <i class="fas fa-chevron-right arrow" id="arrow-operasional"></i>
                </a>
                <ul class="submenu" id="submenu-operasional">
                    <li class="nav-item">
                        <a href="{{ route('admin.bookings.index') }}" class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                            <i class="fas fa-calendar-check"></i> Booking
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Keuangan -->
            <li class="nav-item">
                <a href="#" class="nav-link dropdown-toggle" aria-expanded="false" aria-controls="submenu-keuangan" onclick="toggleSubmenu(event, 'submenu-keuangan')">
                    <i class="fas fa-money-bill-wave"></i><span>Keuangan</span>
                    <i class="fas fa-chevron-right arrow" id="arrow-keuangan"></i>
                </a>
                <ul class="submenu" id="submenu-keuangan">
                    <li class="nav-item">
                        <a href="{{ route('admin.transactions.index') }}" class="nav-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                            <i class="fas fa-receipt"></i> Transaksi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                            <i class="fas fa-credit-card"></i> Pembayaran
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Pengelolaan -->
            <li class="nav-item">
                <a href="#" class="nav-link dropdown-toggle" aria-expanded="false" aria-controls="submenu-manajemen" onclick="toggleSubmenu(event, 'submenu-manajemen')">
                    <i class="fas fa-cogs"></i><span>Pengelolaan</span>
                    <i class="fas fa-chevron-right arrow" id="arrow-manajemen"></i>
                </a>
                <ul class="submenu" id="submenu-manajemen">
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i> User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                            <i class="fas fa-bell"></i> Notifikasi
                            @php($unreadNotifications = auth()->user()->notifications()->unread()->count())
                            @if($unreadNotifications > 0)
                                <span class="badge rounded-pill bg-danger ms-1" aria-label="{{ $unreadNotifications }} notifikasi belum dibaca">{{ $unreadNotifications }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.contact-messages.index') }}" class="nav-link {{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}">
                            <i class="fas fa-envelope"></i> Pesan Kontak
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar"></i> Laporan
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link dropdown-toggle" aria-expanded="false" aria-controls="submenu-pengaturan" onclick="toggleSubmenu(event, 'submenu-pengaturan')">
                    <i class="fas fa-cog"></i><span>Pengaturan</span>
                    <i class="fas fa-chevron-right arrow" id="arrow-pengaturan"></i>
                </a>
                <ul class="submenu" id="submenu-pengaturan">
                    <li class="nav-item">
                        <a href="{{ route('admin.settings.profile') }}" class="nav-link {{ request()->routeIs('admin.settings.profile') ? 'active' : '' }}">
                            <i class="fas fa-user-circle"></i> Profil Saya
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.settings.account') }}" class="nav-link {{ request()->routeIs('admin.settings.account') ? 'active' : '' }}">
                            <i class="fas fa-key"></i> Akun & Keamanan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.settings.general') }}" class="nav-link {{ request()->routeIs('admin.settings.general') ? 'active' : '' }}">
                            <i class="fas fa-image"></i> Identitas & Logo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.settings.notification') }}" class="nav-link {{ request()->routeIs('admin.settings.notification') ? 'active' : '' }}">
                            <i class="fas fa-bell"></i> Notifikasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.settings.backup') }}" class="nav-link {{ request()->routeIs('admin.settings.backup') ? 'active' : '' }}">
                            <i class="fas fa-database"></i> Backup & Restore
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Logout -->
            <li class="nav-item mt-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link page-style-1" >
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
    <button type="button" class="sidebar-backdrop" aria-label="Tutup menu admin" onclick="toggleSidebar(false)"></button>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Topbar -->
        <div class="topbar">
            <button type="button" class="mobile-menu-toggle" aria-label="Buka menu admin" aria-controls="admin-sidebar" aria-expanded="false" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="d-flex align-items-center">
                <!-- Tombol Kembali ke Dashboard -->
                @if(!request()->routeIs('admin.dashboard'))
                <a href="{{ route('admin.dashboard') }}" class="btn-back-dashboard me-3">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Dashboard
                </a>
                @endif
                
                <!-- Page Title -->
                <h4 class="mb-0 fw-bold">@yield('page-title', 'Dashboard')</h4>
            </div>
            
            <div class="ms-auto d-flex align-items-center">
                <span class="me-3">Halo, <strong>{{ auth()->user()->name }}</strong></span>
                <span class="badge bg-primary badge-role">{{ ucfirst(auth()->user()->role) }}</span>
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
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Submenu
        function toggleSubmenu(event, submenuId) {
            event.preventDefault();
            const submenu = document.getElementById(submenuId);
            const arrow = document.getElementById('arrow-' + submenuId.replace('submenu-', ''));
            const toggle = document.querySelector('[aria-controls="' + submenuId + '"]');
            const isOpen = submenu.classList.toggle('show');

            if (arrow) arrow.classList.toggle('rotated', isOpen);
            if (toggle) toggle.setAttribute('aria-expanded', String(isOpen));
        }

        function toggleSidebar(open = null) {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.querySelector('.sidebar-backdrop');
            const toggle = document.querySelector('.mobile-menu-toggle');
            const shouldOpen = open === null ? !sidebar.classList.contains('open') : open;

            sidebar.classList.toggle('open', shouldOpen);
            backdrop.classList.toggle('show', shouldOpen);
            toggle.setAttribute('aria-expanded', String(shouldOpen));
            toggle.setAttribute('aria-label', shouldOpen ? 'Tutup menu admin' : 'Buka menu admin');
            toggle.querySelector('i').classList.toggle('fa-bars', !shouldOpen);
            toggle.querySelector('i').classList.toggle('fa-times', shouldOpen);
        }

        // Auto-open submenu if active
        document.addEventListener('DOMContentLoaded', function() {
            const activeLinks = document.querySelectorAll('.submenu .nav-link.active');
            activeLinks.forEach(link => {
                const submenu = link.closest('.submenu');
                if (submenu) {
                    submenu.classList.add('show');
                    const arrowId = 'arrow-' + submenu.id.replace('submenu-', '');
                    const arrow = document.getElementById(arrowId);
                    if (arrow) {
                        arrow.classList.add('rotated');
                    }
                    const toggle = document.querySelector('[aria-controls="' + submenu.id + '"]');
                    if (toggle) toggle.setAttribute('aria-expanded', 'true');
                }
            });

            document.querySelectorAll('.sidebar a.nav-link:not(.dropdown-toggle)').forEach(link => {
                link.addEventListener('click', () => toggleSidebar(false));
            });
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') toggleSidebar(false);
        });
    </script>
    @stack('scripts')
</body>
</html>