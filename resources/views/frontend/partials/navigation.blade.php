<nav class="navbar" id="navbar">
    <div class="container">
        <a href="{{ route('home') }}" class="navbar-brand">
            <img src="{{ $siteLogoUrl }}" alt="UD Rumah Daun" class="frontend-brand-logo">
            <span class="brand-text">
                <strong>UD RUMAH DAUN</strong>
                <small>JASA & PRODUK UMUM</small>
            </span>
        </a>

        <button class="nav-toggle" type="button" aria-label="Buka menu navigasi" aria-expanded="false">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>

        <div class="nav-links">
            <div class="nav-primary-links" role="group" aria-label="Menu utama">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                <a href="{{ route('layanan') }}" class="{{ request()->routeIs('layanan') ? 'active' : '' }}">Layanan</a>
                <a href="{{ route('produk') }}" class="{{ request()->routeIs('produk') ? 'active' : '' }}">Produk</a>
                <div class="nav-dropdown {{ request()->routeIs('tentang', 'kontak') ? 'active' : '' }}">
                    <button
                        class="nav-dropdown-toggle"
                        type="button"
                        aria-expanded="false"
                        aria-controls="nav-more-menu"
                    >
                        Lainnya
                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="nav-dropdown-menu" id="nav-more-menu">
                        <a href="{{ route('tentang') }}" class="{{ request()->routeIs('tentang') ? 'active' : '' }}">Tentang</a>
                        <a href="{{ route('home') }}#lokasi">Lokasi</a>
                        <a href="{{ route('kontak') }}" class="{{ request()->routeIs('kontak') ? 'active' : '' }}">Kontak</a>
                    </div>
                </div>
            </div>
            <div class="nav-actions" role="group" aria-label="Tautan pelanggan">
                <a href="https://wa.me/6285165021473" class="btn-nav btn-whatsapp" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-whatsapp" aria-hidden="true"></i><span>WhatsApp</span>
                </a>
                <a href="{{ route('cart.index') }}" class="cart-nav-link" aria-label="Keranjang belanja, {{ array_sum(session('cart', [])) }} produk">
                    <i class="bi bi-cart3" aria-hidden="true"></i>
                    <span>Keranjang</span>
                    <span class="cart-count">{{ array_sum(session('cart', [])) }}</span>
                </a>
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-nav btn-login">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-nav btn-login">Login</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
