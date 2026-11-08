@extends('backend.layouts.admin')

@section('title', 'Layanan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-concierge-bell me-2"></i>Jasa &amp; Layanan</h2>
    <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tambah Layanan
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Layanan</th>
                        <th>Kategori</th>
                        <th>Deskripsi</th>
                        <th>Harga</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                    <tr>
                        <td><strong>#{{ $service->id }}</strong></td>
                        <td><strong>{{ $service->name }}</strong></td>
                        <td>{{ $service->category ?: 'Lainnya' }}</td>
                        <td>{{ Str::limit($service->description, 50) }}</td>
                        <td class="fw-bold text-success">{{ (float) $service->harga > 0 ? 'Rp ' . number_format($service->harga, 0, ',', '.') : 'Konfirmasi harga' }}</td>
                        <td>{{ $service->duration ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $service->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($service->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.services.edit', $service) }}" class="btn-action btn-edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="d-inline" onsubmit="return confirm('Layanan dengan riwayat pesanan akan dinonaktifkan. Lanjutkan?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-wrench fa-3x mb-3 d-block"></i>
                            Belum ada data layanan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($services->hasPages())
        <div class="mt-3">{{ $services->links() }}</div>
        @endif
    </div>
</div>
@endsection