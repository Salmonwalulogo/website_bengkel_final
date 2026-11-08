@extends('backend.layouts.admin')

@section('title', 'Transaksi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-receipt me-2"></i>Transaksi</h2>
    <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tambah Transaksi
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                    <tr>
                        <td><strong class="text-primary">{{ $trx->invoice_number }}</strong></td>
                        <td>{{ $trx->customer->name ?? $trx->user->name ?? '-' }}</td>
                        <td class="fw-bold">Rp {{ number_format($trx->total_payment, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($trx->payment_method) }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $trx->status === 'completed' ? 'bg-success' : ($trx->status === 'cancelled' ? 'bg-danger' : 'bg-warning') }}">
                                {{ ucfirst($trx->status) }}
                            </span>
                        </td>
                        <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.transactions.show', $trx) }}" class="btn-action btn-view" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($trx->status === 'pending')
                            <form action="{{ route('admin.transactions.updateStatus', $trx) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn-action btn-edit" title="Konfirmasi pembayaran"><i class="fas fa-check"></i></button>
                            </form>
                            <form action="{{ route('admin.transactions.updateStatus', $trx) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="btn-action btn-delete" title="Batalkan pesanan"><i class="fas fa-times"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-receipt fa-3x mb-3 d-block"></i>
                            Belum ada data transaksi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($transactions->hasPages())
        <div class="mt-3">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>
@endsection