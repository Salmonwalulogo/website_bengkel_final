@extends('backend.layouts.customer')

@section('title', 'Customer Dashboard')

@section('content')
<div class="container-fluid">
    <div class="dashboard-hero customer-hero mb-4">
        <div>
            <span class="dashboard-eyebrow"><i class="fas fa-hand-sparkles me-2"></i>AREA PELANGGAN</span>
            <h1>Hai, {{ auth()->user()->name }}!</h1>
            <p>Belanja produk, pesan berbagai layanan, dan pantau status permintaan serta pembayaran Anda.</p>
        </div>
        <div class="customer-dashboard-actions">
            <a href="{{ route('customer.services') }}" class="dashboard-hero-action"><i class="fas fa-concierge-bell me-2"></i>Lihat Layanan</a>
            <a href="{{ route('customer.booking.create') }}" class="dashboard-hero-action customer-whatsapp-action"><i class="fas fa-calendar-plus me-2"></i>Pesan Sekarang</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card stat-card-modern h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Transaksi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $transactionCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card stat-card-modern h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Layanan Tersedia</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $serviceCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-concierge-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card stat-card-modern h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Permintaan Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeBookingCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="quick-actions mb-4">
        <a href="{{ route('customer.booking.create') }}"><i class="fas fa-calendar-plus"></i><span>Pesan Layanan</span><small>Pilih jasa dan kirim permintaan</small></a>
        <a href="{{ route('customer.bookings') }}"><i class="fas fa-clipboard-list"></i><span>Pesanan Saya</span><small>Pantau konfirmasi dan pembayaran</small></a>
        <a href="{{ route('customer.products') }}"><i class="fas fa-store"></i><span>Belanja Produk</span><small>Lihat produk yang tersedia</small></a>
        <a href="{{ route('cart.index') }}"><i class="fas fa-shopping-cart"></i><span>Keranjang ({{ $cartCount }})</span><small>Lanjutkan belanja produk</small></a>
        <a href="{{ route('customer.transactions') }}"><i class="fas fa-receipt"></i><span>Transaksi</span><small>Cek riwayat pembayaran</small></a>
    </div>
</div>
@endsection