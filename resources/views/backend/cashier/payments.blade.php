@extends('backend.layouts.cashier')

@section('title', 'Pembayaran')

@section('content')
<div class="container-fluid cashier-payments-page">
    <header class="payments-heading">
        <div class="payments-heading-main">
            <span class="payments-heading-icon"><i class="fas fa-money-bill-wave" aria-hidden="true"></i></span>
            <div>
                <span class="payments-eyebrow">AREA KASIR <span>/</span> KEUANGAN</span>
                <h1>Pembayaran</h1>
                <p>Pantau transaksi dan konfirmasi pembayaran pelanggan.</p>
            </div>
        </div>
        <span class="payments-live-label"><span></span>Data transaksi</span>
    </header>

    @if(session('success'))
        <div class="alert alert-success payments-alert"><i class="fas fa-circle-check me-2" aria-hidden="true"></i>{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info payments-alert"><i class="fas fa-circle-info me-2" aria-hidden="true"></i>{{ session('info') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger payments-alert"><i class="fas fa-triangle-exclamation me-2" aria-hidden="true"></i>{{ $errors->first() }}</div>
    @endif

    <section class="payments-overview" aria-label="Ringkasan pembayaran">
        <article class="payments-stat-card">
            <span class="payments-stat-icon pending"><i class="fas fa-hourglass-half" aria-hidden="true"></i></span>
            <div><span>Menunggu konfirmasi</span><strong>{{ number_format($pendingPaymentCount) }}</strong></div>
            <span class="payments-stat-accent pending"></span>
        </article>
        <article class="payments-stat-card">
            <span class="payments-stat-icon paid"><i class="fas fa-circle-check" aria-hidden="true"></i></span>
            <div><span>Pembayaran terverifikasi</span><strong>{{ number_format($paidPaymentCount) }}</strong></div>
            <span class="payments-stat-accent paid"></span>
        </article>
        <article class="payments-stat-card">
            <span class="payments-stat-icon total"><i class="fas fa-receipt" aria-hidden="true"></i></span>
            <div><span>Total transaksi</span><strong>{{ number_format($payments->total()) }}</strong></div>
            <span class="payments-stat-accent total"></span>
        </article>
    </section>

    <section class="payments-panel">
        <div class="payments-panel-heading">
            <div>
                <span class="payments-eyebrow">RINGKASAN TRANSAKSI</span>
                <h2>Riwayat pembayaran</h2>
                <p>Pesanan terbaru ditampilkan terlebih dahulu.</p>
            </div>
            <span class="payments-record-count"><i class="fas fa-layer-group" aria-hidden="true"></i>{{ number_format($payments->total()) }} data</span>
        </div>

        <div class="table-responsive payments-table-wrap">
            <table class="table align-middle payments-table">
                <thead>
                    <tr>
                        <th>Kode pembayaran</th>
                        <th>Invoice</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td><span class="payment-code">{{ $payment->payment_code }}</span></td>
                        <td><span class="payment-invoice">{{ $payment->transaction?->invoice_number ?? '-' }}</span></td>
                        <td><strong class="payment-amount">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                        <td>
                            <span class="payment-status-pill payment-status-{{ $payment->status }}">
                                <span></span>{{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td>
                            @if($payment->payment_date)
                                <span class="payment-date"><i class="far fa-calendar" aria-hidden="true"></i>{{ $payment->payment_date->format('d/m/Y H:i') }}</span>
                            @elseif($payment->status === 'pending')
                                <span class="payment-date payment-date-pending">Belum dikonfirmasi</span>
                            @else
                                <span class="payment-date">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($payment->status === 'pending' && $payment->transaction?->status === 'pending')
                                <form action="{{ route('kasir.payments.confirm', $payment) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="payment-confirm-button">
                                        <i class="fas fa-check" aria-hidden="true"></i>Konfirmasi
                                    </button>
                                </form>
                            @elseif($payment->status === 'paid')
                                <span class="payment-confirmed"><i class="fas fa-check" aria-hidden="true"></i>Terverifikasi</span>
                            @else
                                <span class="payment-no-action">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="payments-empty">
                                <span><i class="fas fa-receipt" aria-hidden="true"></i></span>
                                <strong>Belum ada pembayaran</strong>
                                <p>Transaksi pelanggan akan tampil di sini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="payments-pagination">{{ $payments->links() }}</div>
        @endif
    </section>
</div>
@endsection
