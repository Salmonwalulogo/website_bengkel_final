# UD Rumah Daun

Platform layanan dan usaha berbasis Laravel untuk pelanggan, admin, dan kasir. Alurnya mencakup katalog produk dan layanan, permintaan layanan umum, booking opsional kendaraan, pemesanan, pembayaran, serta laporan.

## Struktur Kode

- `app/`, `routes/`, dan `database/`: backend Laravel, aturan bisnis, API web, dan database.
- `resources/views/frontend/`: halaman publik (`home`, `pages`, dan `public`).
- `resources/views/backend/`: dashboard dan halaman internal role (`admin`, `customer`, `cashier`, `cart`, dan `profile`).
- `resources/views/auth/`, `resources/views/components/`, dan `resources/views/layouts/guest.blade.php`: view shared untuk autentikasi.
- `resources/css/frontend/frontend.css`: asset dasar halaman publik dan pengunjung.
- `resources/css/backend/backend.css`: asset dasar dashboard dan halaman internal role.
- `resources/css/auth.css`: asset login, register, reset password, dan verifikasi email.
- `resources/css/app.css`: asset Laravel/Tailwind umum.
- `resources/js/`: JavaScript frontend bersama dan Alpine.
- `public/build/`: hasil build Vite, dibuat saat deployment dan tidak diedit manual.

Saat menambah tampilan baru, letakkan aturan CSS di file area yang sesuai. Blade sebaiknya berisi struktur HTML, data, dan class, bukan blok CSS panjang.

## Kebutuhan Server

- PHP 8.2 atau lebih baru dengan extension Laravel standar
- MySQL 8+ atau MariaDB yang kompatibel
- Composer 2+
- Node.js 20+ dan npm
- Web server mengarah ke folder `public`, bukan root project

## Menjalankan Lokal

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan storage:link
php artisan serve
```

Isi koneksi database pada `.env` sebelum menjalankan migration.

## Demo Android

- Jalankan Apache dan MySQL XAMPP, lalu pastikan website dapat dibuka di `http://localhost/website-bengkel/public`.
- Gunakan JDK 17 atau 21 (JDK 26 belum didukung oleh versi Gradle/Kotlin project ini). Di PowerShell, gunakan JDK bawaan Android Studio lalu build APK:

```powershell
$env:JAVA_HOME="$env:ProgramFiles\Android\Android Studio\jbr"
$env:PATH="$env:JAVA_HOME\bin;$env:PATH"
.\gradlew.bat :app:assembleDebug
```

- APK debug memakai alamat emulator `10.0.2.2` untuk menjangkau XAMPP di komputer.
- Untuk perangkat fisik, ubah **Alamat API Laravel** pada layar login ke alamat IP komputer yang dapat dijangkau ponsel, misalnya `http://192.168.x.x/website-bengkel/public/api/`. HTTP hanya diizinkan pada APK debug.
- Untuk APK release demo, isi URL HTTPS website saat build: `.\gradlew.bat :app:assembleRelease -PbengkelWebsiteUrl=https://domain-anda.id/website-bengkel/public`. Nilai bawaan release sengaja berupa alamat contoh dan harus diganti.
- Role yang tersedia di website dan aplikasi adalah `admin`, `kasir`, dan `customer`. Masuk dengan akun masing-masing untuk menampilkan menu sesuai role.
- Setelah login dan sinkronisasi pertama saat online, katalog, layanan, kendaraan, booking, transaksi, ringkasan, pengguna, dan pembayaran yang sudah dimuat dapat dibuka dari cache lokal. Penjualan kasir serta perubahan kategori/produk/layanan dan status booking dapat diantrikan offline lalu dikirim saat koneksi kembali. Indikator aplikasi menampilkan status online/offline dan jumlah antrean.
- Akun baru dan perubahan profil tetap memerlukan koneksi; kata sandi tidak disimpan di antrean lokal. Penjualan dengan stok server yang berubah atau perubahan admin yang ditolak server akan tetap tertahan di antrean dan menampilkan kegagalan sinkronisasi, sehingga perlu ditinjau saat online.

## Printer Struk Kasir

- Pada cPanel/Linux, gunakan `ESCPOS_DRIVER=browser`. Kasir membuka struk dari POS atau invoice lalu memilih printer thermal pada dialog cetak browser.
- Cetak langsung via ESC/POS tanpa dialog hanya tersedia jika Laravel berjalan di Windows yang memiliki akses ke printer. Atur `ESCPOS_DRIVER=windows`, bagikan printer Windows, dan isi `ESCPOS_WINDOWS_QUEUE` dengan nama antrean yang tepat.
- Setelah mengubah pengaturan printer, jalankan `php artisan optimize:clear` lalu `php artisan optimize`.

## Deployment Production (cPanel)

Gunakan PHP 8.2 atau lebih baru, MySQL 8+ / MariaDB yang kompatibel, Composer 2, serta ekstensi PHP `bcmath`, `ctype`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, dan `curl`. Aktifkan HTTPS dengan AutoSSL sebelum membuka situs untuk pengguna.

1. Buat database MySQL dan user database melalui cPanel, lalu berikan hak akses penuh user tersebut ke database.
2. Simpan source Laravel di direktori privat di luar document root, misalnya `/home/USER/apps/ud-rumah-daun`. Jangan letakkan `.env`, `vendor`, atau source aplikasi di direktori yang dapat diakses web.
3. Jadikan document root domain sebagai direktori `public` proyek. Jika cPanel mengharuskan `public_html`, salin **isi** direktori `public` ke sana dan sesuaikan dua path aplikasi di `index.php` agar mengarah ke direktori privat. Jangan arahkan document root ke root proyek Laravel.
4. Buat `.env` dari `.env.example`, ganti **semua** nilai contoh dengan nilai nyata, dan pastikan file hanya bisa dibaca oleh akun aplikasi:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=awalan_cpanel_nama_database
DB_USERNAME=awalan_cpanel_user_database
DB_PASSWORD=password-database-yang-kuat

MAIL_MAILER=smtp
MAIL_HOST=host-smtp-provider
MAIL_PORT=587
MAIL_SCHEME=smtp
MAIL_USERNAME=user-smtp
MAIL_PASSWORD=password-smtp
MAIL_FROM_ADDRESS=alamat-terverifikasi@domain-anda.com
MAIL_FROM_NAME="UD Rumah Daun"

SESSION_SECURE_COOKIE=true
ESCPOS_DRIVER=browser
```

Jangan gunakan placeholder di atas sebagai nilai production. Simpan salinan `APP_KEY` yang dihasilkan dengan aman dan jangan menggantinya saat deployment berikutnya.

5. Siapkan aset di mesin build yang memiliki Node.js 20+ dengan `npm ci` lalu `npm run build`; unggah direktori hasil build `public/build` bersama source. Di Terminal cPanel, dari direktori aplikasi, jalankan:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan app:create-admin
php artisan optimize:clear
php artisan optimize
```

`app:create-admin` meminta nama, email, username, dan password secara interaktif; password tidak memiliki nilai bawaan dan harus sedikitnya 12 karakter dengan huruf besar/kecil, angka, serta simbol. Buat akun kasir melalui halaman admin setelah login. Jangan salin database lokal atau akun demo ke production.

Migrasi hanya mengarsipkan enam produk demo yang dikenal. Tetap tinjau perubahan migrasi dan ambil backup sebelum menjalankannya pada database berisi data. Jalankan `migrate --force` hanya setelah memastikan koneksi `.env` menunjuk ke database production yang benar.

Pastikan direktori `storage/` dan `bootstrap/cache/` dapat ditulis oleh akun PHP. Jika cPanel tidak mengizinkan symlink storage, gunakan konfigurasi filesystem yang sesuai hosting dan jangan membuka direktori privat.

## Worker Dan Scheduler

Untuk cPanel tanpa service supervisor, proses queue database dapat dijalankan berkala melalui Cron Jobs. Ganti path PHP dan direktori sesuai akun cPanel:

```bash
/usr/local/bin/php /home/USER/apps/ud-rumah-daun/artisan queue:work --stop-when-empty --max-time=50 --tries=3 --timeout=40
```

Atur Cron Jobs tersebut untuk berjalan setiap menit. Jangan jadwalkan worker kedua jika satu proses masih berjalan. Tambahkan scheduler Laravel setiap menit:

```cron
* * * * * cd /home/USER/apps/ud-rumah-daun && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

## Keamanan

- Jangan commit `.env`, password database, atau credential SMTP.
- Gunakan `APP_DEBUG=false` di production.
- Gunakan HTTPS dan cookie secure pada domain production.
- Jalankan backup database terjadwal dan uji restore secara berkala.
- Gunakan password unik yang kuat untuk setiap akun; segera ganti password contoh/demo yang pernah dipakai saat pengembangan.
- Batasi akses database hanya dari host aplikasi.
- Pantau `storage/logs` dan kegagalan queue tanpa menampilkan detail error ke pengguna.
- Setelah mengubah environment, jalankan `php artisan optimize:clear` lalu `php artisan optimize`.

## Pemeriksaan Sebelum Release

Jalankan build frontend dan audit dependency pada mesin build:

```bash
npm ci
npm audit
npm run build
composer validate --strict
composer audit --no-interaction
php artisan test
```

Setelah aplikasi terpasang pada server:

```bash
php artisan route:list --except-vendor
php artisan view:cache
php artisan migrate:status
```

Build dan test yang berhasil tidak menggantikan konfigurasi HTTPS, SMTP, firewall, backup, dan monitoring pada server production.
