<!DOCTYPE html>
<html lang="id">
<head>
    @include('components.favicon')
    <!-- head content -->
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Brand -->
        <div class="sidebar-brand">
            <span>ADMIN PANEL</span>
        </div>
        
        <!-- Menu -->
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('admin.dashboard') }}" 
                   class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('admin.categories.index') }}" 
                   class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i> Kategori
                </a>
            </li>
            <li>
                <a href="{{ route('admin.products.index') }}" 
                   class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Produk
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users.index') }}" 
                   class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> User Management
                </a>
            </li>
            <li>
                <a href="{{ route('admin.services.index') }}" 
                   class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                    <i class="bi bi-wrench"></i> Layanan
                </a>
            </li>
            <li>
                <a href="{{ route('admin.bookings.index') }}" 
                   class="{{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i> Booking
                </a>
            </li>
            <li>
                <a href="{{ route('admin.transactions.index') }}" 
                   class="{{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i> Transaksi
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.index') }}" 
                   class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart"></i> Laporan
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>
</body>
</html>