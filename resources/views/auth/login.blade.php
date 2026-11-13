<x-guest-layout>
    <a class="auth-home-link" href="{{ url('/') }}"><span aria-hidden="true">&larr;</span> Kembali ke beranda</a>
<div class="auth-card-header"><div class="auth-kicker">Ruang Login</div><h2>Selamat datang</h2><p>Masuk untuk mengakses akun Anda.</p></div>
<x-auth-session-status class="auth-status" :status="session('status')" />
@if(session('error'))<div class="auth-alert page-style-1">{{ session('error') }}</div>@endif
@if($errors->any())<div class="auth-alert page-style-1" >Periksa kembali email/username dan password Anda.</div>@endif
<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="auth-field"><label for="email">Email atau username</label><div class="auth-input-wrap has-icon"><span class="auth-input-icon" aria-hidden="true">@</span><input id="email" class="auth-input" type="text" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"></div><x-input-error :messages="$errors->get('email')" class="auth-error" /></div>
    <div class="auth-field"><label for="password">Password</label><div class="auth-input-wrap"><input id="password" class="auth-input" type="password" name="password" required autocomplete="current-password"><button type="button" class="auth-toggle-password" data-target="password" onclick="togglePasswordVisibility(this)" aria-label="Tampilkan password" title="Tampilkan password"><span data-eye-icon>&#128065;</span></button></div><x-input-error :messages="$errors->get('password')" class="auth-error" /></div>
    <div class="auth-actions"><label class="auth-check-label"><input class="auth-check" type="checkbox" name="remember"> Ingat saya</label>@if(Route::has('password.request'))<a class="auth-link" href="{{ route('password.request') }}">Lupa password?</a>@endif</div>
    <button class="auth-button page-style-2"  type="submit">Masuk ke akun <span aria-hidden="true">&rarr;</span></button>
</form>
<div class="auth-bottom">Belum punya akun? <a class="auth-link" href="{{ route('register') }}">Buat akun baru</a></div>
</x-guest-layout>
