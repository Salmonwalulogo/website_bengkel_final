<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UD Rumah Daun | Beragam Jasa dan Layanan</title>
    <meta name="description" content="UD Rumah Daun menyediakan beragam jasa, layanan, produk, penyewaan, dan transportasi. Pesan layanan melalui website.">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    @vite('resources/css/frontend/frontend.css')
    @vite('resources/css/frontend/home.css')
    @vite('resources/css/frontend/navigation.css')
    @vite('resources/js/frontend.js')
</head>
<body class="frontend-page" data-user-role="{{ auth()->user()?->role ?? 'guest' }}" data-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
    @include('frontend.partials.navigation')

    <section class="hero" id="beranda">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content" data-aos="fade-right">
                        <div class="hero-badge">
                            <i class="bi bi-star-fill"></i>
                            Beragam jasa untuk kebutuhan Anda
                        </div>
                        <h1>
                            UD Rumah Daun
                            <span>                            Beragam jasa, produk, sewa, dan layanan untuk Anda</span>
                        </h1>
                        <p>
                            Jelajahi berbagai jasa dan layanan UD Rumah Daun. Kirim permintaan melalui website dan pantau konfirmasinya dari akun pelanggan Anda.
                        </p>
                        <div class="hero-buttons">
                            <a href="{{ route('layanan') }}" class="btn-hero btn-primary-hero">
                                <i class="bi bi-calendar-check"></i>
                                Pesan Layanan
                            </a>
                            <a href="{{ route('produk') }}" class="btn-hero btn-primary-hero">
                                <i class="bi bi-cart3"></i>
                                Lihat Produk
                            </a>
                            <a href="{{ route('layanan') }}" class="btn-hero btn-secondary-hero">
                                <i class="bi bi-tools"></i>
                                Layanan Kami
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="hero-console">
                        <div class="console-top"><span>STATUS USAHA</span><span class="console-live"><i class="bi bi-circle-fill me-1"></i> Siap melayani</span></div>
                        <div class="service-showcase" aria-label="Layanan UD Rumah Daun">
                            @forelse($services->take(5) as $service)
                                <div class="service-showcase-item"><i class="bi bi-stars"></i><span>{{ $service->name }}</span></div>
                            @empty
                                <div class="service-showcase-item"><i class="bi bi-shop"></i><span>Produk dan layanan</span></div>
                            @endforelse
                        </div>
                        <div class="console-status"><span class="console-check"><i class="bi bi-check2"></i></span>                        <div><strong>Permintaan terpantau</strong><small>Admin akan mengonfirmasi ketersediaan sebelum pembayaran diproses.</small></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="layanan">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-tag">
                    <i class="bi bi-grid"></i> Layanan Kami
                </span>
                <h2>Beragam Layanan <span>UD Rumah Daun</span></h2>
                <p>Kirim permintaan layanan secara online. Harga dan jadwal dikonfirmasi sebelum pembayaran.</p>
            </div>
            <div class="services-grid">
                @forelse($services as $index => $service)
                    <article class="service-card" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                        <div class="service-icon"><i class="bi bi-stars"></i></div>
                        <small>{{ $service->category }}</small>
                        <h3>{{ $service->name }}</h3>
                        <p>{{ $service->description ?: 'Hubungi kami melalui permintaan layanan untuk detail.' }}</p>
                        <strong>{{ (float) $service->harga > 0 ? $service->formatted_harga : 'Harga dikonfirmasi admin' }}</strong>
                    </article>
                @empty
                    <p class="text-muted">Layanan akan segera tersedia.</p>
                @endforelse
            </div>
            <div class="services-action">
                <a href="{{ route('layanan') }}" class="btn-hero btn-primary-hero">Lihat semua layanan</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="tentang">
        <div class="container">
            <div class="about-content">
                <div class="about-image" data-aos="fade-right">
                    <div class="about-illustration">
                        <i class="bi bi-shop about-illustration-icon"></i>
                    </div>
                </div>
                <div class="about-text" data-aos="fade-left">
                    <span class="section-tag">
                        <i class="bi bi-info-circle"></i> Tentang Kami
                    </span>
                    <h2>Mengapa Memilih <span>UD Rumah Daun?</span></h2>
                    <p>UD Rumah Daun menyediakan beberapa layanan untuk kebutuhan pribadi, keluarga, acara, dan angkutan.</p>
                    <p>Pelanggan dapat menghubungi kami untuk menanyakan layanan yang dibutuhkan, memastikan jadwal serta ketersediaan, lalu menyepakati detail sebelum datang atau menggunakan layanan.</p>
                    <div class="about-features">
                        <div class="about-feature-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>
                                <strong>Layanan Cepat</strong>
                                <p class="about-feature-note">Responsif dan efisien</p>
                            </div>
                        </div>
                        <div class="about-feature-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>
                                <strong>Beragam Layanan</strong>
                                <p class="about-feature-note">Untuk kebutuhan pribadi dan acara</p>
                            </div>
                        </div>
                        <div class="about-feature-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>
                                <strong>Harga Transparan</strong>
                                <p class="about-feature-note">Jelas dan bersaing</p>
                            </div>
                        </div>
                        <div class="about-feature-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>
                                <strong>Pelayanan Ramah</strong>
                                <p class="about-feature-note">Mendengar kebutuhan pelanggan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Service Process Section -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-tag testimonial-tag">
                    <i class="bi bi-arrow-left-right"></i> Alur Layanan
                </span>
                <h2>Pesan Layanan <span class="testimonial-highlight">dengan Mudah</span></h2>
                <p>Alur sederhana untuk memastikan layanan sesuai kebutuhan dan jadwal Anda.</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-rating"><i class="bi bi-1-circle-fill"></i></div>
                    <p class="testimonial-text">Pilih jasa, kebutuhan otomotif, penyewaan, atau transportasi dari katalog layanan UD Rumah Daun.</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar"><i class="bi bi-ui-checks"></i></div>
                        <div class="testimonial-author-info">
                            <h4>Pilih Layanan</h4>
                            <p>Tentukan kebutuhan Anda</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-rating"><i class="bi bi-2-circle-fill"></i></div>
                    <p class="testimonial-text">Masuk atau daftar akun pelanggan, lalu kirim permintaan dengan jadwal dan detail yang dibutuhkan.</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar"><i class="bi bi-whatsapp"></i></div>
                        <div class="testimonial-author-info">
                            <h4>Tunggu Konfirmasi</h4>
                            <p>Admin mengecek jadwal dan ketersediaan</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-rating"><i class="bi bi-3-circle-fill"></i></div>
                    <p class="testimonial-text">Setelah admin menyetujui permintaan dan harga, kasir mencatat pembayaran dan pesanan siap diproses.</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar"><i class="bi bi-calendar-check"></i></div>
                        <div class="testimonial-author-info">
                            <h4>Selesaikan Pesanan</h4>
                            <p>Ikuti jadwal yang disepakati</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Location Section -->
    <section class="location-section" id="lokasi">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-tag">
                    <i class="bi bi-geo-alt-fill"></i> Lokasi Kami
                </span>
                <h2>Temukan <span>UD Rumah Daun</span></h2>
                <p>Kunjungi kami untuk kebutuhan produk, layanan, dan konsultasi yang cepat serta terpercaya.</p>
            </div>
            <div class="location-content">
                <div class="location-info" data-aos="fade-right">
                    <div class="location-icon">
                        <i class="bi bi-pin-map-fill"></i>
                    </div>
                    <h3>UD Rumah Daun</h3>
                    <p class="location-address">
                        <i class="bi bi-geo-alt-fill"></i>
                        Desa Menia, Kecamatan Sabu Barat, Kabupaten Sabu Raijua, Nusa Tenggara Timur, Indonesia
                    </p>
                    <p class="location-hours">
                        <i class="bi bi-clock-fill"></i>
                        Senin - Sabtu, 08:00 - 17:00
                    </p>
                    <a href="https://www.google.com/maps/search/?api=1&query=Desa+Menia%2C+Kecamatan+Sabu+Barat%2C+Kabupaten+Sabu+Raijua%2C+Nusa+Tenggara+Timur%2C+Indonesia"
                       class="btn-location" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-sign-turn-right-fill"></i>
                        Buka di Google Maps
                    </a>
                </div>
                <div class="location-map" data-aos="fade-left">
                    <iframe
                        src="https://www.google.com/maps?q=Desa+Menia%2C+Kecamatan+Sabu+Barat%2C+Kabupaten+Sabu+Raijua%2C+Nusa+Tenggara+Timur%2C+Indonesia&output=embed"
                        title="Peta lokasi UD Rumah Daun"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div data-aos="zoom-in">
                <h2>Siap Untuk Kebutuhan Anda?</h2>
                <p>Hubungi UD Rumah Daun untuk kebutuhan jasa, produk, dan konsultasi yang cepat, aman, dan sesuai kebutuhan Anda.</p>
                <a href="https://wa.me/6285165021473" class="btn-cta" target="_blank">
                    <i class="bi bi-whatsapp"></i>
                    Hubungi Kami Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content" data-aos="fade-up">
                <div class="footer-logo">
                    <img src="{{ $siteLogoUrl }}" alt="UD Rumah Daun">
                </div>
                <h3>UD RUMAH DAUN</h3>
                <p>Jasa & Produk Umum untuk kebutuhan rumah, usaha, dan harian Anda</p>
                <div class="social-links">
                    <a href="https://www.facebook.com/udrumahdaun" target="_blank" rel="noopener noreferrer" title="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com/ud.r.daun" target="_blank" rel="noopener noreferrer" title="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://wa.me/6285165021473" target="_blank" rel="noopener noreferrer" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                </div>
                <div class="footer-bottom">
                    <p>&copy; {{ date('Y') }} UD Rumah Daun. Dikelola dengan semangat pelayanan dan kepercayaan pelanggan.</p>
                    <p class="contact-note">
                        <i class="bi bi-geo-alt me-2"></i>Jl. Trans Seba-Bolou, Menia, Kec. Sabu Bar., Kabupaten Sabu Raijua, Nusa Tenggara Tim. 85391, Indonesia
                        <span class="mx-3">|</span>
                        <i class="bi bi-telephone me-2"></i>+62 851-6502-1473
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS Animation
        AOS.init({
            once: true,
            offset: 100,
            duration: 800,
            easing: 'ease-out-cubic',
        });

        // Navbar Scroll Effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>