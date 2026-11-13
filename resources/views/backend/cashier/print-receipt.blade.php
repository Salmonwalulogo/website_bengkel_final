<!DOCTYPE html>
<html lang="id">
<head>
    @include('components.favicon')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $transaction->receipt_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; color: #111; font: 14px/1.45 Arial, sans-serif; }
        .receipt { width: min(100%, 360px); margin: 0 auto; }
        h1, .center { text-align: center; }
        h1 { margin: 0; font-size: 20px; }
        .muted { color: #555; }
        .details { margin: 16px 0; padding: 12px 0; border-top: 1px dashed #555; border-bottom: 1px dashed #555; }
        .details div, .totals div { display: flex; justify-content: space-between; gap: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 5px 0; text-align: left; vertical-align: top; }
        th:not(:first-child), td:not(:first-child) { text-align: right; }
        .totals { display: grid; gap: 5px; margin-top: 12px; }
        .grand-total { padding-top: 7px; border-top: 1px dashed #555; font-weight: 700; }
        .actions { display: flex; justify-content: center; gap: 10px; margin: 20px auto; }
        .actions button, .actions a { padding: 10px 16px; color: white; background: #17212f; border: 0; border-radius: 6px; font: inherit; text-decoration: none; cursor: pointer; }
        @page { size: 80mm auto; margin: 4mm; }
        @media print {
            body { width: 72mm; padding: 0; font-size: 11px; }
            .receipt { width: 100%; }
            .actions { display: none; }
            h1 { font-size: 16px; }
            th, td { padding: 3px 0; }
        }
    </style>
</head>
<body>
    <main class="receipt">
        <h1>UD RUMAH DAUN</h1>
        <p class="center">Bukti Pembayaran</p>

        <section class="details">
            <div><span>Invoice</span><strong>{{ $transaction->invoice_number }}</strong></div>
            <div><span>No. struk</span><strong>{{ $transaction->receipt_number }}</strong></div>
            <div><span>Tanggal cetak</span><strong id="printed-at">{{ $transaction->created_at->format('d/m/Y H:i') }}</strong></div>
            <div><span>Kasir</span><strong>{{ $transaction->user->name }}</strong></div>
            <div><span>Pelanggan</span><strong>{{ $transaction->customer?->name ?? 'Pelanggan umum' }}</strong></div>
        </section>

        <table>
            <thead>
                <tr><th>Barang</th><th>Qty</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                @foreach($transaction->details as $detail)
                    <tr>
                        <td>{{ $detail->product_name ?? $detail->product?->name ?? 'Produk' }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @if($transaction->booking?->service)
                    <tr>
                        <td>{{ $transaction->booking->service->name }}</td>
                        <td>1</td>
                        <td>Rp {{ number_format($transaction->total_payment, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <section class="totals">
            <div><span>Subtotal</span><span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
            <div><span>Diskon</span><span>Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span></div>
            <div><span>Pajak</span><span>Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span></div>
            <div><span>Biaya lain</span><span>Rp {{ number_format($transaction->other_fee, 0, ',', '.') }}</span></div>
            <div class="grand-total"><span>TOTAL</span><span>Rp {{ number_format($transaction->total_payment, 0, ',', '.') }}</span></div>
            <div><span>Bayar</span><span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span></div>
            <div><span>Kembali</span><span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span></div>
            <div><span>Metode</span><span>{{ $transaction->payment_method_label }}</span></div>
        </section>

        <p class="center">Terima kasih telah menggunakan layanan UD Rumah Daun.</p>
    </main>

    <div class="actions">
        <button type="button" onclick="window.print()">Cetak struk</button>
        <a href="{{ route('kasir.invoices') }}">Kembali ke invoice</a>
    </div>
    <script>
        const updatePrintedAt = () => {
            const now = new Date();
            const pad = value => String(value).padStart(2, '0');
            document.getElementById('printed-at').textContent =
                `${pad(now.getDate())}/${pad(now.getMonth() + 1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}`;
        };

        window.addEventListener('beforeprint', updatePrintedAt);
        window.addEventListener('load', () => {
            updatePrintedAt();
            window.print();
        }, { once: true });
    </script>
</body>
</html>
