    @vite('resources/css/backend/index.css')
@extends('backend.layouts.admin')
@section('title', 'Produk')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-box me-2"></i>Produk</h2>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Tambah Produk</a>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Foto</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Katalog</th><th class="text-center">Aksi</th></tr></thead>
                <tbody>
                    @forelse($products as $prod)
                    <tr>
                        <td><img src="{{ $prod->foto ? asset('storage/'.$prod->foto) : asset('images/no-image.png') }}"  class="page-style-1"></td>
                        <td><strong>{{ $prod->name }}</strong></td>
                        <td>{{ $prod->category->name ?? '-' }}</td>
                        <td>Rp {{ number_format($prod->harga, 0, ',', '.') }}</td>
                        <td>{{ $prod->stok }}</td>
                        <td><span class="badge {{ $prod->status == 'active' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($prod->status) }}</span></td>
                        <td>{{ ['public' => 'Publik', 'customer' => 'Customer', 'both' => 'Publik dan Customer'][$prod->visibility] ?? 'Publik dan Customer' }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.products.show', $prod) }}" class="btn-action btn-view"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.products.edit', $prod) }}" class="btn-action btn-edit"><i class="fas fa-edit"></i></a>
                            @if($prod->status === 'active')
                                <form action="{{ route('admin.products.destroy', $prod) }}" method="POST" class="d-inline" onsubmit="return confirm('Nonaktifkan produk ini? Data produk dan riwayat penjualan akan tetap tersimpan.');">@csrf @method('DELETE')<button type="submit" class="btn-action btn-delete" title="Nonaktifkan produk" aria-label="Nonaktifkan {{ $prod->name }}"><i class="fas fa-ban"></i></button></form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">Belum ada produk</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())<div class="mt-3">{{ $products->links() }}</div>@endif
    </div>
</div>
@endsection