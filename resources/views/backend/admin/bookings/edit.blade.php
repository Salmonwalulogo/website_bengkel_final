@extends('backend.layouts.admin')

@section('title', 'Edit Permintaan Layanan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Booking #{{ $booking->id }}</h2>
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.bookings.update', $booking) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Customer <span class="text-danger">*</span></label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            <option value="">Pilih Customer</option>
                            @foreach($customers as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $booking->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="vehicle_id" class="form-label">Kendaraan terkait (opsional)</label>
                        <select class="form-select @error('vehicle_id') is-invalid @enderror" id="vehicle_id" name="vehicle_id">
                            <option value="">Tidak terkait kendaraan</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $booking->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->plate_number }} - {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->user->name ?? 'Pelanggan' }})
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="service_id" class="form-label">Layanan <span class="text-danger">*</span></label>
                        <select class="form-select @error('service_id') is-invalid @enderror" id="service_id" name="service_id" required>
                            <option value="">Pilih Layanan</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ old('service_id', $booking->service_id) == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }} (Rp {{ number_format($service->harga, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="booking_date" class="form-label">Tanggal Booking <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('booking_date') is-invalid @enderror" 
                               id="booking_date" name="booking_date" value="{{ old('booking_date', optional($booking->booking_date)->format('Y-m-d')) }}" required>
                        @error('booking_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="booking_time" class="form-label">Waktu Booking <span class="text-danger">*</span></label>
                        <input type="time" class="form-control @error('booking_time') is-invalid @enderror" 
                               id="booking_time" name="booking_time" value="{{ old('booking_time', $booking->booking_time ? substr($booking->booking_time, 0, 5) : '') }}" required>
                        @error('booking_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="pending" {{ old('status', $booking->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ old('status', $booking->status) === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="in_progress" {{ old('status', $booking->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status', $booking->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $booking->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="quoted_price" class="form-label">Harga konfirmasi (opsional)</label>
                        <input type="number" min="0" step="0.01" class="form-control @error('quoted_price') is-invalid @enderror"
                               id="quoted_price" name="quoted_price" value="{{ old('quoted_price', $booking->quoted_price) }}">
                        <div class="form-text">Harga khusus pesanan ini. Kosongkan untuk memakai harga standar layanan.</div>
                        @error('quoted_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="request_details" class="form-label">Detail kebutuhan pelanggan</label>
                        <textarea class="form-control @error('request_details') is-invalid @enderror"
                                  id="request_details" name="request_details" rows="3">{{ old('request_details', $booking->request_details ?: $booking->complaint) }}</textarea>
                        @error('request_details')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan internal admin</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3">{{ old('notes', $booking->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>                    Update Permintaan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection