@extends('backend.layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')

@section('content')
<div class="dashboard-hero admin-hero mb-4">
    <div>
        <span class="dashboard-eyebrow"><i class="fas fa-sparkles me-2"></i>RUMAH DAUN CONTROL CENTER</span>
        <h1>Selamat datang, {{ auth()->user()->name }}!</h1>
        <p>Kelola produk, layanan dan pemesanan, pantau transaksi, serta atur informasi UD Rumah Daun.</p>
    </div>
    <a href="{{ route('admin.reports.index') }}" class="dashboard-hero-action">
        <i class="fas fa-chart-line me-2"></i>Lihat Laporan
    </a>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value">{{ \App\Models\User::count() }}</div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-value">{{ \App\Models\Product::count() }}</div>
            <div class="stat-label">Total Produk</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-value">{{ \App\Models\Booking::whereDate('booking_date', today())->count() }}</div>
            <div class="stat-label">Booking Hari Ini</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-value">Rp {{ number_format(\App\Models\Transaction::whereMonth('created_at', now()->month)->where('status', 'completed')->sum('total_payment'), 0, ',', '.') }}</div>
            <div class="stat-label">Pendapatan Bulan Ini</div>
        </div>
    </div>
</div>

<!-- Welcome Alert -->
<div class="quick-actions mb-4">
    <a href="{{ route('admin.bookings.create') }}"><i class="fas fa-calendar-plus"></i><span>Booking Baru</span><small>Catat pemesanan layanan</small></a>
    <a href="{{ route('admin.products.create') }}"><i class="fas fa-box-open"></i><span>Tambah Produk</span><small>Kelola katalog</small></a>
    <a href="{{ route('admin.users.create') }}"><i class="fas fa-user-plus"></i><span>Tambah User</span><small>Atur akses akun</small></a>
    <a href="{{ route('admin.reports.index') }}"><i class="fas fa-file-export"></i><span>Export Laporan</span><small>Ringkasan bisnis</small></a>
</div>

<!-- Recent Activity -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card dashboard-panel">
            <div class="card-header">
                <i class="fas fa-calendar-check me-2"></i>Booking Terbaru
            </div>
            <div class="card-body">
                @forelse(\App\Models\Booking::with(['user', 'service'])->latest()->take(5)->get() as $booking)
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <div class="fw-bold">{{ $booking->user->name ?? '-' }}</div>
                        <small class="text-muted">{{ $booking->service->name ?? '-' }}</small>
                    </div>
                    <span class="badge {{ $booking->status === 'completed' ? 'bg-success' : ($booking->status === 'pending' ? 'bg-warning' : 'bg-info') }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
                @empty
                <p class="text-muted text-center mb-0">Belum ada booking</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card dashboard-panel">
            <div class="card-header">
                <i class="fas fa-receipt me-2"></i>Transaksi Terbaru
            </div>
            <div class="card-body">
                @forelse(\App\Models\Transaction::with(['user', 'customer', 'booking.service'])->latest()->take(5)->get() as $trx)
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <div class="fw-bold">{{ $trx->invoice_number }}</div>
                        <small class="text-muted">{{ $trx->customer->name ?? $trx->user->name ?? '-' }} · {{ $trx->booking->service->name ?? 'Produk' }}</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">Rp {{ number_format($trx->total_payment, 0, ',', '.') }}</div>
                        <small class="{{ $trx->status === 'completed' ? 'text-success' : 'text-warning' }}">
                            {{ ucfirst($trx->status) }}
                        </small>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center mb-0">Belum ada transaksi</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection