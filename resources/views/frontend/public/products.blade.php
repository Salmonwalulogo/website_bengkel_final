<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Produk - Web Bengkel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite('resources/css/frontend/products.css')
</head>
<body data-user-role="{{ auth()->user()?->role ?? 'guest' }}" data-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-tools"></i> Web Bengkel
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('home') }}">Home</a>
                <a class="nav-link active" href="{{ route('public.products') }}">Produk</a>
                <a class="nav-link" href="{{ route('public.services') }}">Layanan</a>
                <a class="nav-link" href="{{ route('about') }}">Tentang</a>
                @auth
                    <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <a class="nav-link" href="{{ route('login') }}">Login</a>
                    <a class="nav-link" href="{{ route('register') }}">Register</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container my-5">
        <h1 class="mb-4">Produk Sparepart</h1>

        <div class="row">
            @forelse($products as $product)
                <div class="col-md-3 mb-4">
                    <div class="card product-card shadow-sm h-100">
                        @if($product->foto)
                            <img src="{{ asset('storage/' . $product->foto) }}" 
                                 class="card-img-top product-img" 
                                 alt="{{ $product->name }}">
                        @else
                            <div class="card-img-top product-img bg-secondary d-flex align-items-center justify-content-center">
                                <i class="fas fa-image fa-3x text-white"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <span class="badge bg-secondary mb-2">{{ $product->category->name }}</span>
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text text-primary fw-bold">
                                Rp {{ number_format($product->harga, 0, ',', '.') }}
                            </p>
                            <p class="card-text small text-muted">
                                <i class="fas fa-box"></i> Stok: {{ $product->stok }}
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="{{ route('public.products.show', $product) }}" class="btn btn-primary w-100">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">Belum ada produk tersedia.</div>
                </div>
            @endforelse
        </div>

        {{ $products->links() }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>