@extends('backend.layouts.admin')

@section('title', 'Detail Transaksi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-receipt me-2"></i>Detail Transaksi</h2>
    <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Informasi Transaksi
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Nomor Invoice</strong>
                        <p class="text-primary fw-bold mb-0">{{ $transaction->invoice_number }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Customer</strong>
                        <p class="text-muted mb-0">{{ $transaction->customer->name ?? $transaction->user->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Total Pembayaran</strong>
                        <p class="text-success fw-bold mb-0">Rp {{ number_format($transaction->total_payment, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Metode Pembayaran</strong>
                        <p class="text-muted mb-0">{{ ucfirst($transaction->payment_method) }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status</strong>
                        <p class="mb-0">
                            <span class="badge {{ $transaction->status === 'completed' ? 'bg-success' : ($transaction->status === 'cancelled' ? 'bg-danger' : 'bg-warning') }}">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Tanggal</strong>
                        <p class="text-muted mb-0">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-box me-2"></i>Detail Pesanan
            </div>
            <div class="card-body">
                @if($transaction->booking?->service)
                    <div class="alert alert-info mb-0">
                        Layanan: <strong>{{ $transaction->booking->service->name }}</strong>
                        <span class="d-block">Tanggal permintaan: {{ $transaction->booking->booking_date?->format('d/m/Y') }}</span>
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk / Kendaraan</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transaction->details as $detail)
                            <tr>
                                <td>
                                    {{ $detail->product_name ?? $detail->product->name ?? '-' }}
                                    @if($detail->vehicle_info)
                                        <small class="d-block text-muted">Kendaraan: {{ $detail->vehicle_info }}</small>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                <td>{{ $detail->quantity }}</td>
                                <td class="fw-bold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Tidak ada detail produk</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clock me-2"></i>Waktu
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Dibuat</small>
                    <div class="fw-bold">{{ $transaction->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div>
                    <small class="text-muted">Terakhir Update</small>
                    <div class="fw-bold">{{ $transaction->updated_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection