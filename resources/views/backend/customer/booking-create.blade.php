@extends('backend.layouts.customer')

@section('title', 'Pesan Layanan')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <span class="dashboard-eyebrow text-success">AREA PELANGGAN</span>
        <h1 class="h2 mb-1">Pesan Jasa &amp; Layanan</h1>
        <p class="text-muted mb-0">Pilih layanan apa pun, sampaikan detail kebutuhan, dan tunggu admin mengonfirmasi jadwal serta ketersediaan.</p>
    </div>
    <div class="card booking-flow-card"><div class="card-body p-4 p-lg-5">
        @if($services->isEmpty())
            <div class="booking-empty-state">
                <span class="booking-empty-icon"><i class="fas fa-concierge-bell"></i></span>
                <h2 class="h5">Belum ada layanan aktif</h2>
                <p class="text-muted mb-3">Silakan coba lagi nanti atau hubungi tim UD Rumah Daun.</p>
                <a href="{{ route('layanan') }}" class="btn btn-outline-success">Lihat layanan</a>
            </div>
        @else
        <form action="{{ route('customer.booking.store') }}" method="POST">
            @csrf
            <div class="booking-step">
                <div class="booking-step-heading">
                    <span class="booking-step-number">01</span>
                    <div>
                        <h2 class="h5 mb-1">Pilih layanan</h2>
                        <p class="text-muted mb-0">Pilih jasa yang Anda butuhkan.</p>
                    </div>
                </div>
                @error('service_id')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
                @if($services->isNotEmpty())
                    <div class="booking-service-list">
                        @foreach($services as $service)
                            <div class="booking-service-choice">
                                <input
                                    class="booking-service-input"
                                    type="radio"
                                    id="service-{{ $service->id }}"
                                    name="service_id"
                                    value="{{ $service->id }}"
                                    @checked((string) old('service_id', $selectedService) === (string) $service->id)
                                    required
                                >
                                <label for="service-{{ $service->id }}" class="booking-service-option">
                                    <span class="booking-service-main">
                                        <span class="booking-service-name">{{ $service->name }}</span>
                                        @if($service->description)
                                            <span class="booking-service-description">{{ $service->description }}</span>
                                        @endif
                                    </span>
                                    <span class="booking-service-meta">
                                        <strong>{{ (float) $service->harga > 0 ? $service->formatted_harga : 'Harga dikonfirmasi admin' }}</strong>
                                        @if($service->duration)
                                            <small><i class="far fa-clock me-1"></i>{{ $service->duration }}</small>
                                        @endif
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info mb-0">Belum ada layanan aktif untuk dibooking. Silakan coba lagi nanti.</div>
                @endif
            </div>

            <div class="booking-step">
                <div class="booking-step-heading">
                    <span class="booking-step-number">02</span>
                    <div>
                        <h2 class="h5 mb-1">Tentukan jadwal</h2>
                        <p class="text-muted mb-0">Pilih jadwal yang diinginkan. Jadwal dan ketersediaan akan dikonfirmasi admin.</p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="booking_date" class="form-label">Tanggal booking</label>
                        <input id="booking_date" type="date" name="booking_date" min="{{ date('Y-m-d') }}" value="{{ old('booking_date') }}" class="form-control @error('booking_date') is-invalid @enderror" required>
                        @error('booking_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="booking_time" class="form-label">Waktu booking</label>
                        <input id="booking_time" type="time" name="booking_time" value="{{ old('booking_time') }}" class="form-control @error('booking_time') is-invalid @enderror" required>
                        @error('booking_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="vehicle_id" class="form-label">Kendaraan (opsional)</label>
                        <select id="vehicle_id" name="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror">
                            <option value="">Tidak terkait kendaraan</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" @selected((string) old('vehicle_id') === (string) $vehicle->id)>
                                    {{ $vehicle->plate_number }} — {{ $vehicle->brand }} {{ $vehicle->model }}
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="request_details" class="form-label">Detail kebutuhan <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea id="request_details" name="request_details" rows="3" maxlength="3000" class="form-control @error('request_details') is-invalid @enderror" placeholder="Contoh: lama sewa, jumlah barang, alamat pengantaran, atau detail kendaraan">{{ old('request_details') }}</textarea>
                        @error('request_details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-end gap-2 pt-3">
                <a href="{{ route('customer.bookings') }}" class="btn btn-outline-secondary">Pesanan Saya</a>
                <button class="btn btn-success px-4" type="submit" @disabled($services->isEmpty())>
                    <i class="fas fa-paper-plane me-2"></i>Kirim Permintaan
                </button>
            </div>
        </form>
        @endif
    </div></div>
</div>
@endsection
