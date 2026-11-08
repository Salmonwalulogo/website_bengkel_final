@extends('backend.layouts.admin')

@section('title', 'Identitas Usaha & Logo Website')
@section('page-title', 'Identitas Usaha & Logo Website')

@section('content')
<div class="card">
    <div class="card-header fw-bold">
        <i class="fas fa-store me-2"></i>Informasi Usaha dan Identitas Website
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <p class="fw-bold mb-1">Pengaturan belum dapat disimpan:</p>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="nama_bengkel">Nama Usaha <span class="text-danger">*</span></label>
                    <input id="nama_bengkel" type="text" name="nama_bengkel" class="form-control" value="{{ old('nama_bengkel', $settings['nama_bengkel']) }}" required maxlength="255">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="telepon">Telepon / WhatsApp</label>
                    <input id="telepon" type="text" name="telepon" class="form-control" value="{{ old('telepon', $settings['telepon']) }}" maxlength="20">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $settings['email']) }}" maxlength="255">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="jam_operasional">Jam Operasional</label>
                    <input id="jam_operasional" type="text" name="jam_operasional" class="form-control" value="{{ old('jam_operasional', $settings['jam_operasional']) }}" placeholder="Contoh: 08:00 - 17:00">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label" for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" class="form-control" rows="3">{{ old('alamat', $settings['alamat']) }}</textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label" for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" rows="4">{{ old('deskripsi', $settings['deskripsi']) }}</textarea>
                </div>
                <div class="col-12 mb-4">
                    <label class="form-label" for="site_logo">Logo Website</label>
                    <div class="site-logo-settings">
                        <img
                            src="{{ $settings['site_logo'] ? asset('storage/' . $settings['site_logo']) : asset('images/logo-rumah-daun.png') }}"
                            alt="Logo UD Rumah Daun saat ini"
                            class="site-logo-preview"
                        >
                        <div class="flex-grow-1">
                            <input id="site_logo" type="file" name="site_logo" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <small class="form-text text-muted">Format JPG, PNG, atau WebP. Maksimal 2 MB. Logo akan diperbarui di navigasi dan footer website.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection