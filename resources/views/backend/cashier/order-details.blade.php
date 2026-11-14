@extends('backend.layouts.cashier')

@section('title', 'Detail Pesanan Layanan')
@section('page-title', 'Detail Pesanan Layanan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Booking #{{ $booking->id }}</h1>
        <a href="{{ route('kasir.orders') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Pesanan
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">Informasi Booking</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Pelanggan</strong>
                            <div>{{ $booking->user?->name ?? '-' }}</div>
                            <small class="text-muted">{{ $booking->user?->no_hp ?? '' }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Layanan</strong>
                            <div>{{ $booking->service?->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Kendaraan</strong>
                            @if($booking->vehicle)
                                <div>{{ $booking->vehicle->plate_number }} · {{ $booking->vehicle->brand }} {{ $booking->vehicle->model }}</div>
                            @else
                                <div class="text-muted">Tidak terkait kendaraan</div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Jadwal</strong>
                            <div>{{ $booking->booking_date?->format('d/m/Y') ?? '-' }} · {{ $booking->booking_time }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Status</strong>
                            <div><span class="badge {{ $booking->status_badge }}">{{ $booking->status_label }}</span></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Harga</strong>
                            <div>
                                @php($price = $booking->quoted_price ?? $booking->service?->harga)
                                {{ $price !== null ? 'Rp ' . number_format($price, 0, ',', '.') : 'Menunggu konfirmasi admin' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header fw-bold">Detail Permintaan</div>
                <div class="card-body">
                    {{ $booking->request_details ?: $booking->complaint ?: 'Tidak ada detail tambahan.' }}
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">Informasi Pembayaran</div>
                <div class="card-body">
                    @if($booking->transaction?->payment?->status === 'paid')
                        <span class="badge bg-success">Lunas</span>
                        <div class="mt-2">Invoice: {{ $booking->transaction->invoice_number }}</div>
                        <div>Metode: {{ $booking->transaction->payment_method_label }}</div>
                    @elseif($booking->transaction?->payment?->status === 'pending')
                        <span class="badge bg-warning text-dark">Menunggu verifikasi</span>
                        <div class="mt-2">Invoice: {{ $booking->transaction->invoice_number }}</div>
                        <div>Metode: {{ $booking->transaction->payment_method_label }}</div>
                        <a href="{{ route('kasir.payments') }}" class="btn btn-primary btn-sm mt-3">Buka pembayaran</a>
                    @else
                        <span class="text-muted">Belum ada pembayaran.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
