@extends('backend.layouts.customer')

@section('title', 'Produk')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <small class="text-success fw-bold text-uppercase">Area Pelanggan</small>
            <h1 class="h2 mb-1">Katalog Produk</h1>
            <p class="text-muted mb-0">Jelajahi produk UD Rumah Daun untuk berbagai kebutuhan.</p>
        </div>
        <a href="{{ route('cart.index') }}" class="btn btn-outline-success">
            <i class="fas fa-shopping-cart me-2"></i>Keranjang
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        @forelse($products as $product)
            <div class="col-sm-6 col-xl-4">
                <div class="card h-100 shadow-sm">
                    @if($product->foto && file_exists(public_path('storage/' . $product->foto)))
                        <img src="{{ asset('storage/' . $product->foto) }}" class="card-img-top" alt="{{ $product->name }}" style="height: 210px; object-fit: cover;">
                    @else
                        <div class="d-flex align-items-center justify-content-center bg-light text-secondary" style="height: 210px;">
                            <i class="fas fa-box-open fa-3x" aria-hidden="true"></i>
                            <span class="visually-hidden">Tidak ada foto produk</span>
                        </div>
                    @endif
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-secondary align-self-start mb-2">{{ $product->category->name ?? 'Umum' }}</span>
                        <h2 class="h5 card-title">{{ $product->name }}</h2>
                        <p class="h5 text-success fw-bold">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                        <p class="text-muted small mb-3">Stok: {{ $product->stok }}</p>
                        <div class="mt-auto d-grid gap-2">
                            <form action="{{ route('cart.add', $product) }}" method="POST">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button class="btn btn-outline-success w-100" type="submit" @disabled($product->stok < 1)>
                                    <i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang
                                </button>
                            </form>
                            @if($product->stok > 0)
                                <a href="{{ route('cart.buy-now', $product) }}" class="btn btn-success">
                                    <i class="fas fa-bag-shopping me-2"></i>Beli Sekarang
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">Belum ada produk tersedia.</div>
            </div>
        @endforelse
    </div>

    @if($products->hasPages())
        <div class="mt-4">{{ $products->links() }}</div>
    @endif
</div>
@endsection
