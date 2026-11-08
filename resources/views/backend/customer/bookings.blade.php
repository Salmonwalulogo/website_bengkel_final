@extends('backend.layouts.customer')

@section('title', 'Pesanan Layanan Saya')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0 text-gray-800">Pesanan Layanan Saya</h1><a href="{{ route('customer.booking.create') }}" class="btn btn-success"><i class="fas fa-plus me-2"></i>Pesan Layanan</a></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card shadow"><div class="card-body"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Tanggal diminta</th><th>Layanan</th><th>Kendaraan</th><th>Detail</th><th>Status</th><th>Pembayaran</th></tr></thead><tbody>
    @forelse($bookings as $booking)<tr><td>{{ $booking->booking_date?->format('d/m/Y') }}<br><small>{{ $booking->booking_time }}</small></td><td>{{ $booking->service->name ?? '-' }}<br><small class="text-muted">@php($displayPrice = $booking->transaction?->total_payment ?? $booking->quoted_price ?? $booking->service->harga ?? 0){{ $displayPrice > 0 ? 'Rp ' . number_format($displayPrice, 0, ',', '.') : 'Harga menunggu konfirmasi' }}</small></td><td>{{ $booking->vehicle->plate_number ?? 'Tidak terkait' }}</td><td>{{ Str::limit($booking->request_details ?: $booking->complaint ?: '-', 50) }}</td><td><span class="badge {{ $booking->status_badge }}">{{ $booking->status_label }}</span></td><td>{{ $booking->transaction?->payment?->status === 'paid' ? 'Lunas' : 'Belum dibayar' }} @if(in_array($booking->status, ['pending', 'confirmed']) && !$booking->transaction)<form action="{{ route('customer.bookings.cancel', $booking) }}" method="POST" class="mt-1">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-danger" type="submit">Batalkan</button></form>@endif</td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-4">Belum ada permintaan layanan.</td></tr>@endforelse
    </tbody></table></div>{{ $bookings->links() }}</div></div>
</div>
@endsection
