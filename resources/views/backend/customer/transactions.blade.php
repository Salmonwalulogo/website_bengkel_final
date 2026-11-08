@extends('backend.layouts.customer')

@section('title', 'Transaksi Saya')

@section('content')
<div class="container-fluid"><h1 class="h3 mb-4 text-gray-800">Transaksi Saya</h1><div class="card shadow"><div class="card-body"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Invoice</th><th>Pesanan</th><th>Total</th><th>Metode</th><th>Pembayaran</th><th>Status</th><th>Tanggal</th></tr></thead><tbody>
@forelse($transactions as $transaction)<tr><td><strong>{{ $transaction->invoice_number }}</strong><br><small>No. struk: {{ $transaction->receipt_number }}</small></td><td>{{ $transaction->booking?->service?->name ?? 'Produk' }}</td><td>Rp {{ number_format($transaction->total_payment, 0, ',', '.') }}</td><td>{{ $transaction->payment_method_label }}</td><td>{{ $transaction->payment?->status ? ucfirst($transaction->payment->status) : 'Belum ada' }}</td><td><span class="badge {{ $transaction->status_badge }}">{{ $transaction->status_label }}</span></td><td>{{ $transaction->created_at?->format('d/m/Y H:i') }}</td></tr>
@empty<tr><td colspan="7" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>@endforelse
</tbody></table></div>{{ $transactions->links() }}</div></div></div>
@endsection
