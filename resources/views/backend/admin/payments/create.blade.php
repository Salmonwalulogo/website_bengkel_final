@extends('backend.layouts.admin')
@section('title', 'Tambah Pembayaran')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Tambah Pembayaran</h2>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.payments.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Transaksi <span class="text-danger">*</span></label>
                        <select name="transaction_id" class="form-select @error('transaction_id') is-invalid @enderror" required>
                            <option value="">Pilih Transaksi</option>
                            @foreach($transactions as $trx)
                                <option value="{{ $trx->id }}" {{ old('transaction_id') == $trx->id ? 'selected' : '' }}>{{ $trx->invoice_number }} - Rp {{ number_format($trx->total_payment, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                        @error('transaction_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Bayar (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pembayaran <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ old('payment_date') }}" required>
                        @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <input type="hidden" name="status" value="paid">
                    <div class="mb-3">
                        <label class="form-label">Bukti Pembayaran</label>
                        <input type="file" name="proof_image" class="form-control @error('proof_image') is-invalid @enderror" accept="image/*">
                        @error('proof_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan Pembayaran</button>
            </div>
        </form>
    </div>
</div>
@endsection