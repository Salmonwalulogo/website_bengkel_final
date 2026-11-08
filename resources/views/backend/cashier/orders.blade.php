@extends('backend.layouts.cashier')

@section('title', 'Pesanan Layanan')
@section('page-title', 'Pesanan Layanan')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><i class="fas fa-concierge-bell me-2"></i>Pesanan Layanan</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Detail</th>
                            <th>Layanan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr>
                            <td><strong>#{{ $booking->id }}</strong></td>
                            <td>{{ $booking->user->name ?? '-' }}</td>
                            <td>{{ Str::limit($booking->request_details ?: $booking->complaint ?: ($booking->vehicle->plate_number ?? '—'), 40) }}</td>
                            <td>{{ $booking->service->name ?? '-' }}</td>
                            <td>{{ $booking->booking_date?->format('d/m/Y') ?? '-' }} {{ $booking->booking_time }}</td>
                            <td><span class="badge {{ $booking->status_badge }}">{{ $booking->status_label }}</span></td>
                            <td>
                                @if($booking->transaction?->payment?->status === 'paid')
                                    <span class="badge bg-success">Lunas · {{ $booking->transaction->invoice_number }}</span>
                                @elseif($booking->status === 'confirmed' && (float) ($booking->quoted_price ?? $booking->service->harga ?? 0) > 0)
                                    <form action="{{ route('kasir.orders.pay', $booking) }}" method="POST" class="d-flex flex-wrap gap-2">
                                        @csrf
                                        <select name="payment_method" class="form-select form-select-sm" aria-label="Metode pembayaran">
                                            <option value="cash">Tunai</option>
                                            <option value="qris">QRIS</option>
                                            <option value="transfer">Transfer</option>
                                            <option value="ewallet">E-Wallet</option>
                                            <option value="debit">Debit</option>
                                            <option value="kredit">Kredit</option>
                                        </select>
                                        <button class="btn btn-sm btn-success" type="submit">Bayar Rp {{ number_format($booking->quoted_price ?? $booking->service->harga, 0, ',', '.') }}</button>
                                    </form>
                                @elseif($booking->status === 'confirmed')
                                    <span class="text-muted small">Admin perlu menetapkan harga</span>
                                @else
                                    <span class="text-muted small">Menunggu konfirmasi admin</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada permintaan layanan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
