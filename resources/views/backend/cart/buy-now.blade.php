<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Beli Sekarang - UD Rumah Daun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    @vite('resources/css/backend/index.css')
</head>
<body>
<nav class="navbar navbar-dark mb-5">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">RD &middot; RUMAH DAUN</a>
        <a href="{{ route('cart.index') }}" class="btn btn-sm btn-outline-light"><i class="bi bi-cart3 me-1"></i> Keranjang</a>
    </div>
</nav>
<main class="container pb-5">
    <div class="mb-4">
        <small class="text-success fw-bold text-uppercase">Konfirmasi Pesanan</small>
        <h1 class="h2 mb-1">Beli Sekarang</h1>
        <p class="text-muted mb-0">Pesanan ini langsung diproses tanpa mengubah isi keranjang Anda.</p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="cart-card bg-white p-4">
                <h2 class="h5">{{ $product->name }}</h2>
                <p class="text-muted">Rp {{ number_format($product->harga, 0, ',', '.') }} per item</p>
                <p class="mb-0">Stok tersedia: {{ $product->stok }}</p>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="cart-card bg-white p-4">
                <form action="{{ route('cart.buy-now.checkout', $product) }}" method="POST">
                    @csrf
                    <label for="quantity" class="form-label">Jumlah</label>
                    <input id="quantity" name="quantity" type="number" class="form-control mb-3" min="1" max="{{ $product->stok }}" value="{{ old('quantity', 1) }}" required>
                    @error('quantity')
                        <div class="text-danger small mb-3">{{ $message }}</div>
                    @enderror

                    <label for="payment_method" class="form-label">Metode Pembayaran</label>
                    <select id="payment_method" name="payment_method" class="form-select mb-3" required>
                        <option value="cash" @selected(old('payment_method') === 'cash')>Tunai</option>
                        <option value="qris" @selected(old('payment_method') === 'qris')>QRIS</option>
                        <option value="ewallet" @selected(old('payment_method') === 'ewallet')>E-Wallet</option>
                        <option value="transfer" @selected(old('payment_method') === 'transfer')>Transfer Bank</option>
                    </select>
                    @error('payment_method')
                        <div class="text-danger small mb-3">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-bag-check me-2"></i>Konfirmasi Pesanan
                    </button>
                </form>
                <small class="text-muted d-block mt-3">Pesanan akan berstatus menunggu sampai diproses admin.</small>
            </div>
        </div>
    </div>
</main>
</body>
</html>
