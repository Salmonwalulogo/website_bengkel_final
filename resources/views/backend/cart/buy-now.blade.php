<!DOCTYPE html>
<html lang="id">
<head>
    @include('components.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Beli {{ $product->name }} - UD Rumah Daun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    @vite('resources/css/backend/index.css')
</head>
<body class="buy-now-page">
<nav class="navbar navbar-dark buy-navbar">
    <div class="container buy-content py-2">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">RD <span class="opacity-75">·</span> RUMAH DAUN</a>
        <a href="{{ route('cart.index') }}" class="btn btn-sm btn-outline-light rounded-pill px-3">
            <i class="bi bi-bag me-1" aria-hidden="true"></i>Keranjang
        </a>
    </div>
</nav>

<main class="container buy-content py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="buy-eyebrow text-uppercase mb-2">PESANAN LANGSUNG</div>
            <h1 class="h2 fw-bold mb-2">Beli Sekarang</h1>
            <p class="text-secondary mb-0">Periksa produk dan detail pembayaran sebelum melanjutkan.</p>
        </div>
        <a href="{{ route('customer.products') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Kembali ke produk
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border-0 rounded-3 mb-4" role="alert">
            <i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i>{{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-0 rounded-3 mb-4" role="alert">
            <i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="row g-4 align-items-start">
        <div class="col-lg-7">
            <section class="buy-panel">
                <div class="product-cover d-flex align-items-center justify-content-center">
                    @if($product->foto && file_exists(public_path('storage/' . $product->foto)))
                        <img src="{{ asset('storage/' . $product->foto) }}" alt="{{ $product->name }}">
                    @else
                        <i class="bi bi-box-seam product-placeholder" aria-hidden="true"></i>
                        <span class="visually-hidden">Tidak ada foto produk</span>
                    @endif
                </div>
                <div class="p-4 p-md-5">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <span class="buy-eyebrow text-uppercase">{{ $product->category->name ?? 'PRODUK PILIHAN' }}</span>
                        <span class="stock-pill px-3 py-2">{{ $product->stok }} tersedia</span>
                    </div>
                    <h2 class="h3 fw-bold mb-2">{{ $product->name }}</h2>
                    @if($product->description)
                        <p class="text-secondary mb-4">{{ $product->description }}</p>
                    @else
                        <p class="text-secondary mb-4">Produk pilihan dari UD Rumah Daun, siap untuk dipesan.</p>
                    @endif
                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                        <span class="text-secondary">Harga per item</span>
                        <strong class="fs-5 text-success">Rp {{ number_format($product->harga, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-5">
            <section class="buy-panel p-4 p-md-4">
                <div class="mb-4">
                    <div class="buy-eyebrow text-uppercase mb-2">DETAIL PEMESANAN</div>
                    <h2 class="h4 fw-bold mb-1">Atur pesanan</h2>
                    <p class="small text-secondary mb-0">Pesanan menunggu konfirmasi admin.</p>
                </div>

                <form action="{{ route('cart.buy-now.checkout', $product) }}" method="POST">
                    @csrf
                    @if($product->isAutomotive())
                        <div class="mb-3">
                            <label for="vehicle_id" class="form-label">Kendaraan</label>
                            <select id="vehicle_id" name="vehicle_id" class="form-select">
                                <option value="">Pilih kendaraan terdaftar</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" @selected((string) old('vehicle_id') === (string) $vehicle->id)>
                                        {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->plate_number }})
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="vehicle_brand" class="form-label">Atau tulis merek dan model</label>
                            <input id="vehicle_brand" name="vehicle_brand" value="{{ old('vehicle_brand') }}" class="form-control" maxlength="255" placeholder="Contoh: Honda Vario 160">
                            <div class="form-text">Pilih kendaraan di atas atau isi merek dan model.</div>
                            @error('vehicle_brand')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Jumlah</label>
                        <input id="quantity" name="quantity" type="number" class="form-control" min="1" max="{{ $product->stok }}" value="{{ old('quantity', 1) }}" data-unit-price="{{ (float) $product->harga }}" required>
                        @error('quantity')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="payment_method" class="form-label">Metode pembayaran</label>
                        <select id="payment_method" name="payment_method" class="form-select" required>
                            <option value="cash" @selected(old('payment_method') === 'cash')>Tunai</option>
                            <option value="qris" @selected(old('payment_method') === 'qris')>QRIS</option>
                            <option value="ewallet" @selected(old('payment_method') === 'ewallet')>E-Wallet</option>
                            <option value="transfer" @selected(old('payment_method') === 'transfer')>Transfer Bank</option>
                        </select>
                        @error('payment_method')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="order-total d-flex justify-content-between align-items-center py-3 mb-3">
                        <span class="fw-semibold">Total pesanan</span>
                        <strong class="fs-5" id="order-total">Rp {{ number_format($product->harga * old('quantity', 1), 0, ',', '.') }}</strong>
                    </div>

                    <button type="submit" class="btn btn-order btn-success text-white w-100">
                        <i class="bi bi-lock me-2" aria-hidden="true"></i>Konfirmasi Pesanan
                    </button>
                    <p class="small text-secondary text-center mt-3 mb-0">
                        <i class="bi bi-shield-check me-1" aria-hidden="true"></i>Pesanan Anda akan diproses dengan aman.
                    </p>
                </form>
            </section>
        </div>
    </div>
</main>

<script>
    const quantityInput = document.getElementById('quantity');
    const totalOutput = document.getElementById('order-total');
    const currencyFormat = new Intl.NumberFormat('id-ID');

    const updateOrderTotal = () => {
        const quantity = Math.max(1, Number(quantityInput.value) || 1);
        const total = quantity * Number(quantityInput.dataset.unitPrice);
        totalOutput.textContent = `Rp ${currencyFormat.format(total)}`;
    };

    quantityInput.addEventListener('input', updateOrderTotal);
    updateOrderTotal();
</script>
</body>
</html>
