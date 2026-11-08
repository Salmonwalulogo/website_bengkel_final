<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - UD Rumah Daun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    @vite('resources/css/frontend/frontend.css')
    @vite('resources/css/frontend/kontak.css')
    @vite('resources/css/frontend/navigation.css')
    @vite('resources/js/frontend.js')
</head>
<body data-user-role="{{ auth()->user()?->role ?? 'guest' }}" data-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
    @include('frontend.partials.navigation')

    <section class="page-header">
        <div class="container">
            <h1>Hubungi <span>Kami</span></h1>
            <p class="lead">Kami siap membantu kebutuhan jasa, produk, dan konsultasi Anda</p>
        </div>
    </section>

    <section class="contact-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-5">
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div>
                                <h3>Alamat</h3>
                                <p>Desa Menia<br>Kecamatan Sabu Barat, Kabupaten Sabu Raijua<br>Nusa Tenggara Timur, Indonesia</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <div>
                                <h3>Telepon / WhatsApp</h3>
                                <p><a href="https://wa.me/6285165021473" target="_blank" rel="noopener noreferrer">+62 851-6502-1473</a><br>Senin - Sabtu: 08:00 - 17:00</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-instagram"></i>
                            </div>
                            <div>
                                <h3>Instagram</h3>
                                <p><a href="https://www.instagram.com/ud.r.daun" target="_blank" rel="noopener noreferrer">@ud.r.daun</a></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-facebook"></i>
                            </div>
                            <div>
                                <h3>Facebook</h3>
                                <p><a href="https://www.facebook.com/udrumahdaun" target="_blank" rel="noopener noreferrer">Fb Ud R Daun</a></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div>
                                <h3>Jam Operasional</h3>
                                <p>Senin - Sabtu: 08:00 - 17:00<br>Minggu: Tutup</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="contact-form">
                        <h2 class="contact-form-title">Kirim Pesan</h2>
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('kontak.store') }}">
                            @csrf
                            <div class="form-group">
                                <label for="contact-name">Nama Lengkap</label>
                                <input id="contact-name" name="name" type="text" value="{{ old('name') }}" placeholder="Masukkan nama Anda" required maxlength="255">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact-email">Email</label>
                                        <input id="contact-email" name="email" type="email" value="{{ old('email') }}" placeholder="Masukkan email Anda" required maxlength="255">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact-phone">No. Telepon</label>
                                        <input id="contact-phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="Masukkan nomor telepon" required maxlength="20">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="contact-message">Pesan</label>
                                <textarea id="contact-message" name="message" rows="5" placeholder="Tulis pesan Anda..." required maxlength="5000">{{ old('message') }}</textarea>
                            </div>
                            <button type="submit" class="btn-submit">
                                <i class="bi bi-send me-2"></i>Kirim Pesan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="contact-footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} UD Rumah Daun. Hubungi kami untuk kebutuhan jasa, produk, dan konsultasi.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>