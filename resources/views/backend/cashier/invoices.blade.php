@extends('backend.layouts.cashier')

@section('title', 'Invoice')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><i class="fas fa-file-invoice me-2"></i>Invoice</h1>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nomor Invoice</th>
                            <th>Customer</th>
                            <th>Pesanan</th>
                            <th>Total</th>
                            <th>Metode Pembayaran</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td><strong class="text-primary">{{ $transaction->invoice_number }}</strong><br><small>No. struk: {{ $transaction->receipt_number }}</small></td>
                            <td>{{ $transaction->customer->name ?? $transaction->user->name ?? '-' }}</td>
                            <td>{{ $transaction->booking->service->name ?? 'Produk' }}</td>
                            <td class="fw-bold">Rp {{ number_format($transaction->total_payment, 0, ',', '.') }}</td>
                            <td>{{ $transaction->payment_method_label }}</td>
                            <td><span class="badge {{ $transaction->status_badge }}">{{ $transaction->status_label }}</span></td>
                            <td>{{ $transaction->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('kasir.pos.print', $transaction) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-print me-1"></i>{{ config('escpos.driver') === 'windows' ? 'Cetak' : 'Buka struk' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada invoice.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
