<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keranjang - UD Rumah Daun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    @vite('resources/css/backend/index.css')
</head>
<body>
@php($productsRoute = auth()->check() && auth()->user()->role === 'customer' ? route('customer.products') : route('produk'))
<nav class="navbar navbar-dark mb-5"><div class="container"><a class="navbar-brand fw-bold" href="{{ route('home') }}">RD &middot; RUMAH DAUN</a><a href="{{ $productsRoute }}" class="btn btn-sm btn-outline-light">Lanjut Belanja</a></div></nav>
<main class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><small class="text-success fw-bold text-uppercase">Pesanan Anda</small><h1 class="h2 mb-1">Keranjang Belanja</h1><p class="text-muted mb-0">Periksa produk sebelum dikirim ke admin.</p></div></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif
    @if($items->isEmpty())
        <div class="cart-card bg-white p-5 text-center"><i class="bi bi-basket3 display-4 text-success"></i><h3 class="mt-3">Keranjang masih kosong</h3><p class="text-muted">Pilih produk yang Anda butuhkan untuk mulai memesan.</p><a href="{{ $productsRoute }}" class="btn btn-success">Lihat Produk</a></div>
    @else
        <div class="row g-4"><div class="col-lg-8"><div class="cart-card bg-white p-3 p-md-4">
            @foreach($items as $item)<div class="d-flex align-items-center gap-3 py-3 border-bottom"><div class="product-thumb d-flex align-items-center justify-content-center"><i class="bi bi-box-seam text-success"></i></div><div class="flex-grow-1"><h5 class="mb-1">{{ $item['product']->name }}</h5><div class="text-muted small">Rp {{ number_format($item['product']->harga, 0, ',', '.') }} / item</div></div><form action="{{ route('cart.update', $item['product']) }}" method="POST" class="d-flex align-items-center gap-2">@csrf @method('PATCH')<input type="number" min="0" max="{{ $item['product']->stok }}" name="quantity" value="{{ $item['quantity'] }}" class="form-control form-control-sm page-style-1" ><button class="btn btn-sm btn-outline-success" title="Perbarui jumlah"><i class="bi bi-check"></i></button></form><strong class="text-nowrap">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</strong><form action="{{ route('cart.remove', $item['product']) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Hapus produk"><i class="bi bi-trash"></i></button></form></div>@endforeach
        </div></div><div class="col-lg-4"><div class="cart-card bg-white p-4"><h4>Ringkasan</h4><div class="d-flex justify-content-between border-bottom py-3"><span>Total</span><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></div>@auth @if(auth()->user()->role === 'customer')<form action="{{ route('cart.checkout') }}" method="POST" class="mt-4">@csrf<label for="payment_method" class="form-label">Metode Pembayaran</label><select id="payment_method" name="payment_method" class="form-select mb-3" required><option value="cash">Tunai</option><option value="qris">QRIS</option><option value="ewallet">E-Wallet</option><option value="transfer">Transfer Bank</option></select><button class="btn btn-success w-100" type="submit">Kirim Pesanan <i class="bi bi-arrow-right ms-2"></i></button></form><small class="text-muted d-block mt-2">Pesanan menunggu diproses. Pembayaran akan dikonfirmasi admin.</small>@else<div class="alert alert-info mt-3 mb-0">Checkout tersedia untuk akun pelanggan.</div>@endif @else<a href="{{ route('login') }}" class="btn btn-success w-100 mt-4">Login untuk Checkout</a><a href="{{ route('register') }}" class="btn btn-outline-success w-100 mt-2">Daftar akun pelanggan</a>@endauth</div></div></div>
    @endif
</main>
</body>
</html>
