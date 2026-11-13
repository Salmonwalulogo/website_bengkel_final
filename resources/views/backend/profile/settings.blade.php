<!DOCTYPE html>
<html lang="id">
<head>
    @include('components.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengaturan Akun - Web Bengkel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @vite('resources/css/backend/backend.css')
    
    @vite('resources/css/backend/settings.css')
</head>
<body class="backend-page">
<div class="settings-shell">
    <div class="settings-top">
        <div><a href="{{ route('dashboard') }}" class="brand"><i class="fas fa-tools me-2"></i>RUMAH DAUN</a><h1 class="h2 mt-3 mb-1">Pengaturan Akun</h1><p class="muted mb-0">Kelola identitas dan keamanan akun Anda.</p></div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
    </div>

    @if(session('status') === 'profile-updated' || session('status') === 'password-updated')<div class="alert alert-success">Perubahan berhasil disimpan.</div>@endif
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">Periksa kembali data yang dimasukkan.</div>@endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card settings-card"><div class="card-body p-4 p-md-5">
                <h2 class="h5 section-title">Informasi Profil</h2><p class="muted">Nama, email, nomor telepon, dan foto yang tampil di aplikasi.</p>
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-4">
                    @csrf @method('PATCH')
                    <div class="d-flex align-items-center gap-3 mb-4">
                        @if($user->photo)<img src="{{ asset('storage/' . $user->photo) }}" class="avatar" alt="Foto profil">@else<div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>@endif
                        <div><label for="photo" class="form-label mb-1">Foto profil</label><input id="photo" type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp"><small class="muted">JPG, PNG, atau WEBP. Maksimal 2 MB.</small>@error('photo')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                    </div>
                    <div class="mb-3"><label for="name" class="form-label">Nama lengkap</label><input id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>@error('name')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                    <div class="mb-3"><label for="email" class="form-label">Email</label><input id="email" type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>@error('email')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                    <div class="mb-3"><label for="no_hp" class="form-label">Nomor telepon</label><input id="no_hp" name="no_hp" class="form-control" value="{{ old('no_hp', $user->no_hp) }}">@error('no_hp')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save me-2"></i>Simpan Profil</button>
                </form>
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="card settings-card"><div class="card-body p-4 p-md-5">
                <h2 class="h5 section-title">Ganti Password</h2><p class="muted">Gunakan password yang panjang dan tidak dipakai di akun lain.</p>
                <form method="POST" action="{{ route('password.update') }}" class="mt-4">
                    @csrf @method('PUT')
                    <div class="mb-3"><label for="current_password" class="form-label">Password saat ini</label><input id="current_password" type="password" name="current_password" class="form-control" required autocomplete="current-password">@if($errors->updatePassword->has('current_password'))<div class="text-danger small">{{ $errors->updatePassword->first('current_password') }}</div>@endif</div>
                    <div class="mb-3"><label for="password" class="form-label">Password baru</label><input id="password" type="password" name="password" class="form-control" required autocomplete="new-password">@if($errors->updatePassword->has('password'))<div class="text-danger small">{{ $errors->updatePassword->first('password') }}</div>@endif</div>
                    <div class="mb-3"><label for="password_confirmation" class="form-label">Ulangi password baru</label><input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password"></div>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-lock me-2"></i>Perbarui Password</button>
                </form>
            </div></div>
        </div>
    </div>
</div>
</body>
</html>
