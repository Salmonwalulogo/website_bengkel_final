<!DOCTYPE html>
<html lang="id">
<head>
    @include('components.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - UD Rumah Daun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite('resources/css/frontend/frontend.css')
    @vite('resources/css/frontend/tentang.css')
    @vite('resources/css/frontend/navigation.css')
    @vite('resources/js/frontend.js')
</head>
<body data-user-role="{{ auth()->user()?->role ?? 'guest' }}" data-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
    @include('frontend.partials.navigation')

    <section class="page-header">
        <div class="container">
            <h1>Tentang <span>Kami</span></h1>
            <p class="lead">Mengenal layanan dan cara kerja UD Rumah Daun</p>
        </div>
    </section>

    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <h2>Mengapa Memilih <span>UD Rumah Daun?</span></h2>
                <p>
                    UD Rumah Daun menyediakan jasa perawatan pribadi, layanan otomotif, penyewaan perlengkapan, transportasi,
                    dan produk untuk kebutuhan sehari-hari.
                </p>
                <p>
                    Pelanggan dapat masuk atau membuat akun, memilih layanan, lalu mengirim tanggal dan detail kebutuhan.
                    Admin mengonfirmasi jadwal, ketersediaan, serta harga sebelum kasir memproses pembayaran.
                </p>
                <p>
                    Kami berupaya memberikan komunikasi yang jelas dan pelayanan yang ramah pada setiap tahapan,
                    mulai dari pertanyaan awal hingga layanan selesai.
                </p>

                <div class="stats">
                    <div class="stat-item">
                        <h3>Jasa</h3>
                        <p>Perawatan pribadi</p>
                    </div>
                    <div class="stat-item">
                        <h3>Otomotif</h3>
                        <p>Cuci, tambal ban, dan ganti oli</p>
                    </div>
                    <div class="stat-item">
                        <h3>Sewa & Rental</h3>
                        <p>Tenda, lampu, dan pickup</p>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a class="btn btn-warning" href="{{ route('layanan') }}">
                        <i class="bi bi-grid"></i> Jelajahi Layanan
                    </a>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} UD Rumah Daun. Dikelola dengan penuh perhatian dan semangat pelayanan.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>