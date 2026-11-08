@extends('backend.layouts.admin')
@section('title', 'Tambah Booking')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Tambah Permintaan Layanan</h2>
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.bookings.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="user_id" id="userSelect" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">Pilih Customer</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}" {{ old('user_id') == $cust->id ? 'selected' : '' }}>{{ $cust->name }}</option>
                            @endforeach
                        </select>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kendaraan terkait (opsional)</label>
                        <select name="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror">
                            <option value="">Tidak terkait kendaraan</option>
                            @foreach($vehicles as $veh)
                                <option value="{{ $veh->id }}" {{ old('vehicle_id') == $veh->id ? 'selected' : '' }}>{{ $veh->plate_number }} - {{ $veh->brand }} {{ $veh->model }} ({{ $veh->user->name ?? 'Pelanggan' }})</option>
                            @endforeach
                        </select>
                        @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Layanan <span class="text-danger">*</span></label>
                        <select name="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
                            <option value="">Pilih Layanan</option>
                            @foreach($services as $serv)
                                <option value="{{ $serv->id }}" {{ old('service_id') == $serv->id ? 'selected' : '' }}>{{ $serv->name }} (Rp {{ number_format($serv->harga, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                        @error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Booking <span class="text-danger">*</span></label>
                        <input type="date" name="booking_date" class="form-control @error('booking_date') is-invalid @enderror" value="{{ old('booking_date') }}" required>
                        @error('booking_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Waktu Booking <span class="text-danger">*</span></label>
                        <input type="time" name="booking_time" class="form-control @error('booking_time') is-invalid @enderror" value="{{ old('booking_time') }}" required>
                        @error('booking_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga konfirmasi (opsional)</label>
                        <input type="number" min="0" step="0.01" name="quoted_price" class="form-control @error('quoted_price') is-invalid @enderror" value="{{ old('quoted_price') }}">
                        <div class="form-text">Isi harga yang disepakati untuk permintaan ini; jika kosong, harga standar layanan digunakan.</div>
                        @error('quoted_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Detail kebutuhan pelanggan</label>
                        <textarea name="request_details" class="form-control @error('request_details') is-invalid @enderror" rows="3">{{ old('request_details') }}</textarea>
                        @error('request_details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan internal admin</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan Permintaan</button>
            </div>
        </form>
    </div>
</div>
@endsection