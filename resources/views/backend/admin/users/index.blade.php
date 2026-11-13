    @vite('resources/css/backend/index.css')
@extends('backend.layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-users me-2"></i>User Management</h2>
        <small class="text-muted">Nonaktifkan akun pelanggan tanpa menghapus riwayat transaksinya.</small>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tambah User
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>No. HP</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td><strong>#{{ $user->id }}</strong></td>
                        <td>
                            @if($user->photo)
                                <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" 
                                     class="rounded-circle page-style-1" >
                            @else
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center page-style-2" 
                                     >
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $user->name }}</strong>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->no_hp ?? '-' }}</td>
                        <td>
                            @php
                                $badgeClass = match($user->role) {
                                    'admin' => 'bg-danger',
                                    'kasir' => 'bg-warning',
                                    'customer' => 'bg-success',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($user->role) }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn-action btn-view" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-action btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($user->isCustomer())
                                <form action="{{ route('admin.users.updateStatus', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Ubah status akun pelanggan ini?');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">
                                    <button
                                        type="submit"
                                        class="btn-action {{ $user->is_active ? 'btn-delete' : 'btn-view' }}"
                                        title="{{ $user->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}"
                                        aria-label="{{ $user->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}"
                                    >
                                        <i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" 
                                      onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            Belum ada data user
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
        <div class="mt-3">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection