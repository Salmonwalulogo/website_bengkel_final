<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan dan Pemesanan - UD Rumah Daun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    @vite('resources/css/frontend/frontend.css')
    @vite('resources/css/frontend/layanan.css')
    @vite('resources/css/frontend/navigation.css')
    @vite('resources/js/frontend.js')
</head>
<body data-user-role="{{ auth()->user()?->role ?? 'guest' }}" data-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
    @include('frontend.partials.navigation')

    <section class="page-header">
        <div class="container">
            <h1>Jasa &amp; Layanan <span>Kami</span></h1>
            <p class="lead">Pilih layanan, kirim permintaan, lalu tunggu konfirmasi ketersediaan dari tim UD Rumah Daun.</p>
        </div>
    </section>

    <section class="services-section">
        <div class="container">
            @forelse($services as $category => $items)
                <h2 class="h4 mb-3">{{ $category }}</h2>
                <div class="row mb-4">
                    @foreach($items as $service)
                        <div class="col-md-4 mb-3">
                            <article class="service-card h-100">
                                <div class="service-icon"><i class="bi bi-stars"></i></div>
                                <h3>{{ $service->name }}</h3>
                                <p>{{ $service->description ?: 'Layanan UD Rumah Daun. Isi kebutuhan Anda saat mengirim permintaan.' }}</p>
                                <p class="fw-semibold mb-3">
                                    @if((float) $service->harga > 0)
                                        Rp {{ number_format($service->harga, 0, ',', '.') }}
                                    @else
                                        Harga dikonfirmasi setelah permintaan
                                    @endif
                                    @if($service->duration)
                                        <small class="d-block text-muted fw-normal">{{ $service->duration }}</small>
                                    @endif
                                </p>
                                @if(auth()->user()?->role === 'customer')
                                    <a class="btn btn-success" href="{{ route('customer.booking.create', ['service' => $service->id]) }}">Pesan layanan</a>
                                @elseif(auth()->guest())
                                    <a class="btn btn-success" href="{{ route('customer.booking.create', ['service' => $service->id]) }}">Login untuk pesan</a>
                                    <a class="btn btn-outline-success ms-1" href="{{ route('register') }}">Daftar</a>
                                @else
                                    <span class="text-muted small">Masuk dengan akun pelanggan untuk memesan.</span>
                                @endif
                            </article>
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="alert alert-info">Belum ada layanan yang aktif. Silakan hubungi admin.</div>
            @endforelse
            <div class="text-center mt-4">
                <p>Pesanan layanan dikonfirmasi terlebih dahulu sebelum pembayaran.</p>
                <a class="btn btn-warning" href="https://wa.me/6285165021473?text=Halo%20UD%20Rumah%20Daun%2C%20saya%20ingin%20bertanya%20tentang%20layanan." target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-whatsapp"></i> Tanya via WhatsApp
                </a>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} UD Rumah Daun. Siap membantu kebutuhan Anda.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>