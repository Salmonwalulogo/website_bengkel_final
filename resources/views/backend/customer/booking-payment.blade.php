@extends('backend.layouts.customer')

@section('title', 'Pembayaran Booking')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pembayaran Booking #{{ $booking->id }}</h1>
        <a href="{{ route('customer.bookings') }}" class="btn btn-outline-secondary">Kembali ke Pesanan</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5>{{ $booking->service->name }}</h5>
                    <p class="text-muted mb-1">Status pesanan: {{ $booking->status_label }}</p>
                    <p class="text-muted">Invoice: {{ $booking->transaction?->invoice_number ?? 'Dibuat setelah bukti dikirim' }}</p>
                    <div class="fs-4 fw-bold">Rp {{ number_format($amount, 0, ',', '.') }}</div>
                    @if($booking->transaction?->payment?->status === 'pending')
                        <div class="alert alert-warning mt-3 mb-0">Bukti pembayaran sudah dikirim dan sedang menunggu verifikasi kasir. Anda dapat mengunggah ulang bukti jika diperlukan.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="mb-3">Cara pembayaran</h5>
                    <div class="mb-3">
                        <strong>Transfer bank</strong>
                        @if(!empty($settings['bank_name']) && !empty($settings['bank_account_number']) && !empty($settings['bank_account_name']))
                            <div>{{ $settings['bank_name'] }} · {{ $settings['bank_account_number'] }}</div>
                            <div>a.n. {{ $settings['bank_account_name'] }}</div>
                        @else
                            <div class="text-muted">Informasi rekening belum tersedia. Silakan hubungi admin.</div>
                        @endif
                    </div>
                    <div class="mb-4">
                        <strong>QRIS</strong>
                        @if(!empty($settings['qris_image']))
                            <div class="mt-2"><img src="{{ asset('storage/' . $settings['qris_image']) }}" alt="QRIS pembayaran" style="max-width: 240px; max-height: 240px"></div>
                        @else
                            <div class="text-muted">QRIS belum tersedia. Silakan hubungi admin.</div>
                        @endif
                    </div>
                    <p class="text-muted">Tunai hanya dibayarkan langsung di bengkel. Untuk membayar dari rumah, pilih transfer atau QRIS lalu unggah bukti pembayaran.</p>

                    <form action="{{ route('customer.bookings.payment.store', $booking) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Metode pembayaran online</label>
                            <select id="payment_method" name="payment_method" class="form-select" required>
                                <option value="">Pilih metode</option>
                                <option value="transfer" @selected(old('payment_method', $booking->transaction?->payment_method) === 'transfer')>Transfer bank</option>
                                <option value="qris" @selected(old('payment_method', $booking->transaction?->payment_method) === 'qris')>QRIS</option>
                            </select>
                            @error('payment_method')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="proof_image" class="form-label">Bukti pembayaran</label>
                            <input id="proof_image" type="file" name="proof_image" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                            <small class="text-muted">JPG, PNG, atau WebP; maksimal 5 MB.</small>
                            @error('proof_image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Kirim bukti untuk diverifikasi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
