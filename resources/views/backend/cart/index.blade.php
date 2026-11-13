<!DOCTYPE html>
<html lang="id">
<head>
    @include('components.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keranjang Belanja - UD Rumah Daun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    @vite('resources/css/backend/index.css')
</head>
<body class="store-cart-page">
@php
    $productsRoute = auth()->check() && auth()->user()->role === 'customer' ? route('customer.products') : route('produk');
    $cartQuantity = $items->sum('quantity');
@endphp
<header class="store-header">
    <nav class="store-nav" aria-label="Navigasi keranjang">
        <div class="store-nav-inner">
            <a class="store-brand" href="{{ route('home') }}" aria-label="UD Rumah Daun - Beranda">
                <span class="store-brand-mark">RD</span>
                <span class="store-brand-name">RUMAH DAUN<small>OTO &amp; LIFESTYLE</small></span>
            </a>
            <div class="store-nav-actions">
                <a href="{{ auth()->user()?->role === 'customer' ? route('customer.dashboard') : route('home') }}" class="store-nav-link">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i><span>Kembali</span>
                </a>
                <a href="{{ $productsRoute }}" class="store-nav-continue">
                    <span>Lanjut belanja</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </nav>
</header>

<main class="cart-shell">
    <section class="cart-heading">
        <div>
            <div class="cart-eyebrow"><span></span> PESANAN ANDA</div>
            <h1>Keranjang <span>belanja</span></h1>
            <p>Periksa pilihan Anda sebelum mengirim pesanan ke admin.</p>
        </div>
        <div class="cart-progress" aria-label="Langkah pemesanan">
            <div class="cart-progress-step is-current"><span>01</span><strong>Keranjang</strong></div>
            <span class="cart-progress-line" aria-hidden="true"></span>
            <div class="cart-progress-step"><span>02</span><strong>Konfirmasi</strong></div>
        </div>
    </section>

    @if(session('success'))
        <div class="cart-alert cart-alert-success" role="status"><i class="bi bi-check-circle-fill" aria-hidden="true"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="cart-alert cart-alert-error" role="alert"><i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>{{ session('error') }}</div>
    @endif
    @if(session('warning'))
        <div class="cart-alert cart-alert-warning" role="alert"><i class="bi bi-info-circle-fill" aria-hidden="true"></i>{{ session('warning') }}</div>
    @endif

    @if($items->isEmpty())
        <section class="cart-empty">
            <div class="cart-empty-icon"><i class="bi bi-bag" aria-hidden="true"></i></div>
            <span class="cart-empty-kicker">MULAI DARI SINI</span>
            <h2>Keranjang Anda masih kosong</h2>
            <p>Temukan produk pilihan untuk kebutuhan kendaraan dan keseharian Anda.</p>
            <a href="{{ $productsRoute }}" class="cart-primary-button">
                Jelajahi produk <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </section>
    @else
        <div class="cart-layout">
            <section class="cart-items-panel" aria-label="Produk di keranjang">
                <div class="cart-panel-heading">
                    <div>
                        <span class="cart-panel-kicker">PILIHAN ANDA</span>
                        <h2>Produk dalam keranjang</h2>
                    </div>
                    <span class="cart-count">{{ $cartQuantity }} produk</span>
                </div>

                @foreach($items as $item)
                    @php($productId = $item['product']->id)
                    <article class="cart-item">
                        <div class="cart-item-main">
                            <div class="cart-product-image">
                                @if($item['product']->foto && file_exists(public_path('storage/' . $item['product']->foto)))
                                    <img src="{{ asset('storage/' . $item['product']->foto) }}" alt="{{ $item['product']->name }}">
                                @else
                                    <i class="bi bi-box-seam" aria-hidden="true"></i>
                                @endif
                            </div>
                            <div class="cart-product-info">
                                <span class="cart-product-category">{{ $item['product']->category->name ?? 'Produk pilihan' }}</span>
                                <h3>{{ $item['product']->name }}</h3>
                                <p>Rp {{ number_format($item['product']->harga, 0, ',', '.') }} <span>/ item</span></p>
                            </div>
                            <div class="cart-product-controls">
                                <form action="{{ route('cart.update', $item['product']) }}" method="POST" class="cart-quantity-form">
                                    @csrf
                                    @method('PATCH')
                                    <label for="quantity-{{ $productId }}" class="visually-hidden">Jumlah {{ $item['product']->name }}</label>
                                    <input id="quantity-{{ $productId }}" type="number" min="0" max="{{ $item['product']->stok }}" name="quantity" value="{{ $item['quantity'] }}" inputmode="numeric">
                                    <button type="submit" aria-label="Perbarui jumlah {{ $item['product']->name }}" title="Perbarui jumlah">
                                        <i class="bi bi-check2" aria-hidden="true"></i>
                                    </button>
                                </form>
                                <div class="cart-subtotal">
                                    <span>Subtotal</span>
                                    <strong>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</strong>
                                </div>
                                <form action="{{ route('cart.remove', $item['product']) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="cart-remove-button" aria-label="Hapus {{ $item['product']->name }} dari keranjang" title="Hapus produk">
                                        <i class="bi bi-trash3" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if($item['is_automotive'] && auth()->user()?->role === 'customer')
                            <div class="cart-vehicle-fields">
                                <div class="cart-vehicle-heading">
                                    <i class="bi bi-car-front" aria-hidden="true"></i>
                                    <span>Kendaraan <small>opsional</small></span>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="vehicle-{{ $productId }}">Pilih kendaraan terdaftar</label>
                                        <select id="vehicle-{{ $productId }}" form="cart-checkout-form" name="vehicle_id[{{ $productId }}]">
                                            <option value="">Tidak dikaitkan</option>
                                            @foreach($vehicles as $vehicle)
                                                <option value="{{ $vehicle->id }}" @selected((string) old("vehicle_id.{$productId}") === (string) $vehicle->id)>
                                                    {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->plate_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("vehicle_id.{$productId}")<small class="cart-field-error">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="vehicle-brand-{{ $productId }}">Atau tulis merek dan model</label>
                                        <input id="vehicle-brand-{{ $productId }}" form="cart-checkout-form" name="vehicle_brand[{{ $productId }}]" value="{{ old("vehicle_brand.{$productId}", $item['vehicle_info']) }}" maxlength="255" placeholder="Contoh: Honda Vario 160">
                                        @error("vehicle_brand.{$productId}")<small class="cart-field-error">{{ $message }}</small>@enderror
                                    </div>
                                </div>
                                <p class="cart-vehicle-note"><i class="bi bi-info-circle" aria-hidden="true"></i> Isi hanya jika Anda ingin mengaitkan produk dengan kendaraan tertentu.</p>
                            </div>
                        @endif
                    </article>
                @endforeach

                <div class="cart-items-note">
                    <i class="bi bi-chat-left-text" aria-hidden="true"></i>
                    <span>Admin akan meninjau pesanan Anda dan mengonfirmasi langkah selanjutnya.</span>
                </div>
            </section>

            <aside class="cart-summary-panel" aria-label="Ringkasan pesanan">
                <div class="summary-title">
                    <span class="summary-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span>
                    <div><span>RINGKASAN</span><h2>Pesanan Anda</h2></div>
                </div>
                <div class="summary-row">
                    <span>Jumlah produk</span><strong>{{ $cartQuantity }} item</strong>
                </div>
                <div class="summary-row">
                    <span>Subtotal</span><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                </div>
                <div class="summary-total">
                    <span>Total pesanan</span>
                    <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                </div>

                @auth
                    @if(auth()->user()->role === 'customer')
                        <form id="cart-checkout-form" action="{{ route('cart.checkout') }}" method="POST" class="cart-checkout-form">
                            @csrf
                            <label for="payment_method">Metode pembayaran</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="cash" @selected(old('payment_method') === 'cash')>Tunai</option>
                                <option value="qris" @selected(old('payment_method') === 'qris')>QRIS</option>
                                <option value="ewallet" @selected(old('payment_method') === 'ewallet')>E-Wallet</option>
                                <option value="transfer" @selected(old('payment_method') === 'transfer')>Transfer Bank</option>
                            </select>
                            <button class="cart-primary-button cart-checkout-button" type="submit">
                                Kirim pesanan <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </button>
                        </form>
                        <p class="cart-checkout-note"><i class="bi bi-info-circle" aria-hidden="true"></i> Pembayaran akan dikonfirmasi admin setelah pesanan ditinjau.</p>
                    @else
                        <div class="cart-role-notice"><i class="bi bi-person-lock" aria-hidden="true"></i><span>Checkout hanya tersedia untuk akun pelanggan.</span></div>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="cart-primary-button cart-checkout-button">Login untuk checkout <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                @endauth

                <a href="{{ $productsRoute }}" class="cart-summary-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Lanjut memilih produk</a>
            </aside>
        </div>
    @endif
</main>
<footer class="cart-footer">
    <span>RD · RUMAH DAUN</span>
    <span>Terima kasih telah berbelanja bersama kami.</span>
</footer>
</body>
</html>
