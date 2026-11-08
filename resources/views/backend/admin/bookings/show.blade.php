@extends('backend.layouts.admin')

@section('title', 'Detail Booking')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Detail Booking #{{ $booking->id }}</h2>
    <div>
        <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Informasi Booking
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Customer</strong>
                        <p class="text-muted mb-0">{{ $booking->user->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Kendaraan terkait (opsional)</strong>
                        <p class="text-muted mb-0">
                            {{ $booking->vehicle->plate_number ?? '-' }} - {{ $booking->vehicle->brand }} {{ $booking->vehicle->model }}
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Layanan</strong>
                        <p class="text-muted mb-0">{{ $booking->service->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Tanggal</strong>
                        <p class="text-muted mb-0">{{ $booking->booking_date->format('d F Y') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Waktu</strong>
                        <p class="text-muted mb-0">{{ $booking->booking_time }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status</strong>
                        <p class="mb-0">
                            @php
                                $badgeClass = match($booking->status) {
                                    'pending' => 'bg-warning',
                                    'confirmed' => 'bg-info',
                                    'in_progress' => 'bg-primary',
                                    'completed' => 'bg-success',
                                    'cancelled' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if($booking->request_details || $booking->complaint)
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-clipboard-list me-2"></i>Detail Permintaan
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $booking->request_details ?: $booking->complaint }}</p>
            </div>
        </div>
        @endif

        @if($booking->notes)
        <div class="card">
            <div class="card-header">
                <i class="fas fa-sticky-note me-2"></i>Catatan Admin/Mekanik
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $booking->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clock me-2"></i>Waktu
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Dibuat</small>
                    <div class="fw-bold">{{ $booking->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div>
                    <small class="text-muted">Terakhir Update</small>
                    <div class="fw-bold">{{ $booking->updated_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection