@extends('backend.layouts.admin')
@section('title', 'Tambah Transaksi')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-receipt me-2"></i>Tambah Transaksi</h2>
    <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.transactions.store') }}" method="POST">
            @csrf
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">Pilih Customer</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}" {{ old('user_id') == $cust->id ? 'selected' : '' }}>{{ $cust->name }}</option>
                            @endforeach
                        </select>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                                <option value="qris" {{ old('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                                <option value="ewallet" {{ old('payment_method') == 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
                                <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        </select>
                        @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <h5 class="mb-3">Daftar Produk</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead><tr><th>Produk</th><th>Harga Satuan</th><th>Stok</th><th>Jumlah</th></tr></thead>
                    <tbody>
                        @foreach($products as $key => $prod)
                        <tr>
                            <td>{{ $prod->name }}</td>
                            <td>Rp {{ number_format($prod->harga, 0, ',', '.') }}</td>
                            <td>{{ $prod->stok }}</td>
                            <td>
                                <input type="hidden" name="products[{{ $key }}][product_id]" value="{{ $prod->id }}">
                                <input type="number" name="products[{{ $key }}][quantity]" class="form-control" min="0" max="{{ $prod->stok }}" value="0">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="text-muted">Transaksi baru berstatus menunggu. Konfirmasi pembayaran dilakukan setelah pembayaran diterima.</p>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Proses Transaksi</button>
            </div>
        </form>
    </div>
</div>
@endsection