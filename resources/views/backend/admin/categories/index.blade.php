@extends('backend.layouts.admin')
@section('title', 'Kategori')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-tags me-2"></i>Kategori</h2>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Tambah Kategori</a>
</div>
<p class="text-muted">Kelola kategori produk untuk berbagai kebutuhan usaha, seperti otomotif, perlengkapan acara, penyewaan, perawatan pribadi, rumah tangga, dan kategori lainnya.</p>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>ID</th><th>Nama</th><th>Jumlah Produk</th><th>Deskripsi</th><th class="text-center">Aksi</th></tr></thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td>{{ $cat->id }}</td>
                        <td><strong>{{ $cat->name }}</strong></td>
                        <td>{{ $cat->products_count }}</td>
                        <td>{{ Str::limit($cat->description, 50) }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.categories.edit', $cat) }}" class="btn-action btn-edit"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kategori ini?');">@csrf @method('DELETE')<button type="submit" class="btn-action btn-delete"><i class="fas fa-trash"></i></button></form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())<div class="mt-3">{{ $categories->links() }}</div>@endif
    </div>
</div>
@endsection