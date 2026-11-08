    @vite('resources/css/backend/edit.css')
@extends('backend.layouts.admin')

@section('title', 'Edit Pembayaran')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Pembayaran</h2>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.payments.update', $payment) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Jumlah Bayar (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                               id="amount" name="amount" value="{{ old('amount', $payment->amount) }}" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Tanggal Pembayaran <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('payment_date') is-invalid @enderror" 
                               id="payment_date" name="payment_date" 
                               value="{{ old('payment_date', $payment->payment_date ? $payment->payment_date->format('Y-m-d\TH:i') : '') }}" required>
                        @error('payment_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            @if($payment->transaction?->status === 'pending')
                                <option value="pending" {{ old('status', $payment->status) === 'pending' ? 'selected' : '' }}>Menunggu</option>
                                <option value="paid" {{ old('status', $payment->status) === 'paid' ? 'selected' : '' }}>Lunas</option>
                                <option value="failed" {{ old('status', $payment->status) === 'failed' ? 'selected' : '' }}>Gagal</option>
                                <option value="cancelled" {{ old('status', $payment->status) === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            @else
                                <option value="{{ $payment->status }}" selected>{{ ucfirst($payment->status) }}</option>
                            @endif
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="proof_image" class="form-label">Bukti Pembayaran</label>
                        @if($payment->proof_image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $payment->proof_image) }}" alt="Bukti" 
                                      class="page-style-1">
                            </div>
                        @endif
                        <input type="file" class="form-control @error('proof_image') is-invalid @enderror" 
                               id="proof_image" name="proof_image" accept="image/*">
                        @error('proof_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>
@endsection