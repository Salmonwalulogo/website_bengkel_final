@extends('backend.layouts.cashier')

@section('title', 'Transaksi Pembayaran')
@section('page-title', 'Transaksi Pembayaran Kasir')

@section('content')
@php($receipt = session('receipt'))
@php($productData = $products->map(fn ($product) => ['id' => $product->id, 'code' => $product->product_code, 'barcode' => $product->barcode, 'name' => $product->name, 'price' => (float) $product->harga, 'stock' => $product->stok])->values())
<div class="pos-page" data-transaction-number="{{ $transactionNumber }}" data-receipt-number="{{ $receiptNumber }}" data-products-url="{{ route('kasir.pos.products') }}" data-products='@json($productData)'>
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><strong>Transaksi belum diproses.</strong> {{ $errors->first() }}</div>
    @endif

    <div class="pos-header">
        <div>
            <span class="pos-eyebrow"><i class="fas fa-store me-2"></i>POINT OF SALE</span>
            <h1>Transaksi Pembayaran</h1>
            <p>Catat penjualan produk, pilih pelanggan, dan proses pembayaran dengan cepat dan akurat.</p>
        </div>
        <div class="pos-meta">
            <span><i class="fas fa-receipt me-2"></i>            <b id="transaction-number">{{ $transactionNumber }}</b></span>
            <span><i class="fas fa-ticket-alt me-2"></i>No. struk: <b>{{ $receiptNumber }}</b></span>
            <span><i class="far fa-clock me-2"></i><b id="live-clock">{{ now()->format('d/m/Y H:i:s') }}</b></span>
        </div>
    </div>

    <form id="pos-form" method="POST" action="{{ route('kasir.pos.store') }}">
        @csrf
        <input type="hidden" name="checkout_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}" id="checkout-token">
        <div class="pos-grid">
            <main class="pos-main">
                <section class="pos-card product-panel">
                    <div class="pos-card-heading"><div><span class="section-kicker">INVENTORI</span><h2>Tambah Barang</h2></div><div class="d-flex align-items-center gap-2"><span id="product-count" class="product-count">{{ $products->count() }} produk aktif</span><span id="product-sync-status" class="product-count" role="status" aria-live="polite">Sinkron otomatis</span></div></div>
                    <div class="search-box"><i class="fas fa-search"></i><input id="product-search" type="search" autocomplete="off" placeholder="Cari nama, kode barang, atau barcode (F2)"><kbd>F2</kbd></div>
                    <div id="product-results" class="product-results"></div>
                </section>

                <section class="pos-card customer-panel">
                    <div class="pos-card-heading"><div><span class="section-kicker">PELANGGAN</span><h2>Data Pelanggan</h2></div><i class="fas fa-user-tag panel-icon"></i></div>
                    <select name="customer_id" class="form-select"><option value="">Pelanggan umum</option>@foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach</select>
                </section>

                <section class="pos-card cart-panel">
                    <div class="pos-card-heading"><div><span class="section-kicker">PENJUALAN</span><h2>Keranjang Belanja</h2></div><span id="cart-badge" class="cart-badge">0 item</span></div>
                    <div id="cart-empty" class="empty-cart"><i class="fas fa-shopping-basket"></i><p>Keranjang masih kosong</p><small>Pilih barang dari daftar di atas untuk memulai transaksi.</small></div>
                    <div class="table-responsive"><table id="cart-table" class="table align-middle mb-0 d-none"><thead><tr><th>Barang</th><th>Harga</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th><th></th></tr></thead><tbody></tbody></table></div>
                </section>
            </main>

            <aside class="pos-card payment-panel">
                <div class="payment-heading"><span class="section-kicker">RINGKASAN</span><h2>Pembayaran</h2><span class="cashier-label"><i class="fas fa-user-circle me-1"></i>{{ auth()->user()->name }}</span></div>
                <div class="summary-list">
                    <div><span>Total item</span><strong id="summary-items">0</strong></div>
                    <div><span>Subtotal</span><strong id="summary-subtotal">Rp 0</strong></div>
                    <div class="input-summary"><label for="discount-percent">Diskon (%)</label><div class="input-group input-group-sm"><input id="discount-percent" name="discount_percent" type="number" min="0" max="100" step="0.01" value="0"><span class="input-group-text">%</span></div></div>
                    <div class="input-summary"><label for="discount-nominal">Diskon nominal</label><input id="discount-nominal" name="discount_nominal" type="number" min="0" step="1" value="0"></div>
                    <div class="input-summary"><label for="tax">Pajak / PPN</label><input id="tax" name="tax" type="number" min="0" step="1" value="0"></div>
                    <div class="input-summary"><label for="other-fee">Biaya tambahan</label><input id="other-fee" name="other_fee" type="number" min="0" step="1" value="0"></div>
                </div>
                <div class="grand-total"><span>Total akhir</span><strong id="grand-total">Rp 0</strong></div>
                <label class="payment-label" for="paid-amount">Uang dibayar <kbd>F4</kbd></label>
                <input id="paid-amount" name="paid_amount" class="paid-input" type="number" min="0" step="1" value="0" required>
                <div class="quick-payments"><button type="button" data-quick-pay="40000">Rp40.000</button><button type="button" data-quick-pay="50000">Rp50.000</button><button type="button" data-quick-pay="100000">Rp100.000</button><button type="button" id="exact-payment">Uang Pas</button></div>
                <label class="payment-label" for="payment-method">Metode pembayaran</label>
                <select id="payment-method" name="payment_method" class="form-select"><option value="cash">Tunai</option><option value="qris">QRIS</option><option value="debit">Debit</option><option value="kredit">Kredit</option><option value="ewallet">E-Wallet</option><option value="transfer">Transfer</option></select>
                <div id="payment-status" class="payment-status neutral"><i class="fas fa-info-circle"></i><span>Masukkan nominal pembayaran.</span></div>
                <div class="payment-actions payment-actions-single"><button type="button" id="cancel-transaction" class="btn btn-outline-danger"><i class="fas fa-times me-1"></i>Batal</button></div>
                <button type="submit" id="pay-button" class="btn btn-pay" disabled><i class="fas fa-print me-2"></i>BAYAR &amp; CETAK</button>
            </aside>
        </div>
        <div id="cart-inputs"></div>
    </form>

    @if($receipt)
        <section id="receipt" class="receipt-card">
            <div class="receipt-actions"><form method="POST" action="{{ route('kasir.pos.print', $receipt) }}">@csrf<button type="submit" class="btn btn-dark"><i class="fas fa-print me-2"></i>{{ config('escpos.driver') === 'windows' ? 'CETAK STRUK' : 'BUKA CETAK STRUK' }}</button></form><a href="{{ route('kasir.pos') }}" class="btn btn-outline-secondary">Transaksi Baru</a></div>
            <div class="receipt-paper"><h2>UD RUMAH DAUN</h2><p class="text-center mb-3">Bukti Pembayaran</p><div class="receipt-info"><span>Nomor transaksi<strong>{{ $receipt->invoice_number }}</strong></span><span>Nomor struk<strong>{{ $receipt->receipt_number }}</strong></span><span>Tanggal<strong>{{ $receipt->created_at->format('d/m/Y H:i') }}</strong></span><span>Kasir<strong>{{ $receipt->user->name }}</strong></span><span>Pelanggan<strong>{{ optional($receipt->customer)->name ?? 'Pelanggan umum' }}</strong></span></div><table><thead><tr><th>Nama</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead><tbody>@foreach($receipt->details as $detail)<tr><td>{{ $detail->product_name ?? optional($detail->product)->name }}</td><td>{{ $detail->quantity }}</td><td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td><td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td></tr>@endforeach @if($receipt->booking?->service)<tr><td>{{ $receipt->booking->service->name }}</td><td>1</td><td>Rp {{ number_format($receipt->total_payment, 0, ',', '.') }}</td><td>Rp {{ number_format($receipt->total_payment, 0, ',', '.') }}</td></tr>@endif</tbody></table><div class="receipt-totals"><span>Subtotal <b>Rp {{ number_format($receipt->subtotal, 0, ',', '.') }}</b></span><span>Diskon <b>Rp {{ number_format($receipt->discount, 0, ',', '.') }}</b></span><span>Pajak <b>Rp {{ number_format($receipt->tax, 0, ',', '.') }}</b></span><span>Biaya lain <b>Rp {{ number_format($receipt->other_fee, 0, ',', '.') }}</b></span><strong>TOTAL <b>Rp {{ number_format($receipt->total_payment, 0, ',', '.') }}</b></strong><span>Bayar <b>Rp {{ number_format($receipt->paid_amount, 0, ',', '.') }}</b></span><span>Kembali <b>Rp {{ number_format($receipt->change_amount, 0, ',', '.') }}</b></span><span>Metode pembayaran <b>{{ $receipt->payment_method_label }}</b></span></div><p class="text-center receipt-thanks">Terima kasih telah menggunakan layanan UD Rumah Daun.</p></div>
        </section>
    @endif
</div>
@endsection

@push('styles')
    @vite('resources/css/backend/pos.css')
@endpush
@push('scripts')
    @vite('resources/js/cashier-pos.js')
@endpush
