<!DOCTYPE html>
<html lang="id">
<head>
    @include('components.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - UD Rumah Daun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite('resources/css/frontend/frontend.css')
    @vite('resources/css/frontend/produk.css')
    @vite('resources/css/frontend/navigation.css')
    @vite('resources/js/frontend.js')
</head>
<body data-user-role="{{ auth()->user()?->role ?? 'guest' }}" data-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
    @include('frontend.partials.navigation')

    <section class="page-header">
        <div class="container">
            <h1>Produk <span>Kami</span></h1>
            <p class="lead">Berbagai produk berkualitas untuk kebutuhan rumah, usaha, dan harian Anda</p>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif
            <div class="row">
                @forelse($products as $product)
                @php($remainingStock = max(0, (int) $product->stok - (int) (session('cart', [])[$product->id] ?? 0)))
                <div class="col-md-4">
                    <div class="product-card">
                        <div class="product-image">
                            @if($product->foto && file_exists(public_path('storage/' . $product->foto)))
                                <img src="{{ asset('storage/' . $product->foto) }}" alt="{{ $product->name }}">
                            @else
                                <i class="bi bi-box-seam page-style-3" ></i>
                            @endif
                            <span class="product-badge">{{ $product->stok > 0 ? 'Tersedia' : 'Habis' }}</span>
                        </div>
                        <div class="product-info">
                            <span class="product-category">{{ $product->category->name ?? 'Umum' }}</span>
                            <h3 class="product-name">{{ $product->name }}</h3>
                            <div class="product-price">
                                Rp {{ number_format($product->harga, 0, ',', '.') }}
                            </div>
                            <div class="d-grid gap-2">
                                <a class="btn-order text-center text-decoration-none" target="_blank" rel="noopener noreferrer" href="https://wa.me/6285165021473?text={{ urlencode('Halo UD Rumah Daun, saya ingin bertanya tentang ' . $product->name) }}">
                                    <i class="bi bi-whatsapp me-2"></i>Tanya Produk
                                </a>
                                <button class="btn btn-outline-dark w-100" type="button" data-bs-toggle="modal" data-bs-target="#add-to-cart-{{ $product->id }}" @disabled($remainingStock < 1)>
                                    <i class="bi bi-cart-plus me-2"></i>Tambah ke Keranjang
                                </button>
                                @if($product->stok > 0)
                                    @auth
                                        @if(auth()->user()->role === 'customer')
                                            <a href="{{ route('cart.buy-now', $product) }}" class="btn btn-success w-100"><i class="bi bi-bag-check me-2"></i>Beli Sekarang</a>
                                        @endif
                                    @else
                                        <a href="{{ route('cart.buy-now', $product) }}" class="btn btn-success w-100"><i class="bi bi-bag-check me-2"></i>Beli Sekarang</a>
                                    @endauth
                                @endif
                            </div>
                        </div>
                    </div>
                    @include('frontend.partials.add-to-cart-modal', ['product' => $product, 'vehicles' => $vehicles])
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-box-seam page-style-4" ></i>
                    <h3 class="mt-3 page-style-5" >Belum Ada Produk</h3>
                </div>
                @endforelse
            </div>
            
            @if($products->hasPages())
            <div class="mt-4">
                {{ $products->links() }}
            </div>
            @endif
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} UD Rumah Daun. Produk berkualitas untuk kebutuhan sehari-hari dan usaha Anda.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>