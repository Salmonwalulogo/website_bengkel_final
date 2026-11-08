@extends('backend.layouts.admin')

@section('title', 'Pembayaran')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Pembayaran</h2>
    <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tambah Pembayaran
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kode</th>
                        <th>Transaksi</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td><strong>#{{ $payment->id }}</strong></td>
                        <td><code>{{ $payment->payment_code }}</code></td>
                        <td>{{ $payment->transaction->invoice_number ?? '-' }}</td>
                        <td class="fw-bold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td>
                            @php
                                $badgeClass = match($payment->status) {
                                    'pending' => 'bg-warning',
                                    'paid' => 'bg-success',
                                    'failed' => 'bg-danger',
                                    'cancelled' => 'bg-secondary',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($payment->status) }}</span>
                        </td>
                        <td>{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y H:i') : '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.payments.edit', $payment) }}" class="btn-action btn-edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-money-bill-wave fa-3x mb-3 d-block"></i>
                            Belum ada data pembayaran
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($payments->hasPages())
        <div class="mt-3">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection