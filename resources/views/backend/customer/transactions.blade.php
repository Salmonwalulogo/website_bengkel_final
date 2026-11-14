@extends('backend.layouts.customer')

@section('title', 'Transaksi Saya')

@section('content')
<div class="container-fluid"><h1 class="h3 mb-4 text-gray-800">Transaksi Saya</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card shadow"><div class="card-body"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Invoice</th><th>Pesanan</th><th>Total</th><th>Metode</th><th>Pembayaran</th><th>Status</th><th>Tanggal</th></tr></thead><tbody>
@forelse($transactions as $transaction)<tr><td><strong>{{ $transaction->invoice_number }}</strong><br><small>No. struk: {{ $transaction->receipt_number }}</small></td><td>{{ $transaction->booking?->service?->name ?? 'Produk' }}@foreach($transaction->details as $detail)<small class="d-block">{{ $detail->product_name ?? $detail->product?->name ?? 'Produk' }}@if($detail->vehicle_info) · Kendaraan: {{ $detail->vehicle_info }}@endif</small>@endforeach</td><td>Rp {{ number_format($transaction->total_payment, 0, ',', '.') }}</td><td>{{ $transaction->payment_method_label }}</td><td>
    @if($transaction->payment?->status === 'paid')
        <span class="badge bg-success">Lunas</span>
    @elseif($transaction->payment?->status === 'pending')
        <span class="badge bg-warning text-dark">Menunggu verifikasi</span>
        @if($transaction->status === 'pending')
            @if($transaction->booking_id)
                <a href="{{ route('customer.bookings.payment', $transaction->booking_id) }}" class="btn btn-sm btn-primary d-block mt-1">Lihat / Perbarui Bukti</a>
            @else
                <a href="{{ route('customer.transactions.payment', $transaction) }}" class="btn btn-sm btn-primary d-block mt-1">Bayar / Unggah Bukti</a>
            @endif
        @endif
    @else
        {{ $transaction->payment?->status ? ucfirst($transaction->payment->status) : 'Belum ada' }}
    @endif
</td><td><span class="badge {{ $transaction->status_badge }}">{{ $transaction->status_label }}</span></td><td>{{ $transaction->created_at?->format('d/m/Y H:i') }}</td></tr>
@empty<tr><td colspan="7" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>@endforelse
</tbody></table></div>{{ $transactions->links() }}</div></div></div>
@endsection
