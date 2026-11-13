@extends('backend.layouts.admin')

@section('title', 'Notifikasi')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="mb-1"><i class="fas fa-bell me-2"></i>Notifikasi</h2>
        <small class="text-muted">{{ $unreadNotificationCount }} belum dibaca</small>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <form action="{{ route('admin.notifications.markAllAsRead') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-success" @disabled($unreadNotificationCount === 0)>
                <i class="fas fa-check-double me-2" aria-hidden="true"></i>Tandai semua dibaca
            </button>
        </form>
        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Kirim Notifikasi
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Pesan</th>
                        <th>Tujuan</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notif)
                    <tr class="{{ $notif->is_read ? '' : 'table-warning' }}">
                        <td><strong>#{{ $notif->id }}</strong></td>
                        <td><strong>{{ $notif->title }}</strong></td>
                        <td>{{ Str::limit($notif->message, 50) }}</td>
                        <td>{{ $notif->user->name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($notif->type ?? 'general') }}</span>
                        </td>
                        <td>
                            @if($notif->is_read)
                                <span class="badge bg-success">Dibaca</span>
                            @else
                                <span class="badge bg-warning">Belum Dibaca</span>
                            @endif
                        </td>
                        <td>{{ $notif->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            @if(!$notif->is_read)
                            <form action="{{ route('admin.notifications.markAsRead', $notif->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn-action btn-view" title="Tandai Dibaca">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('admin.notifications.destroy', $notif->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus notifikasi ini?');">
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
                            <i class="fas fa-bell fa-3x mb-3 d-block"></i>
                            Belum ada notifikasi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($notifications->hasPages())
        <div class="mt-3">{{ $notifications->links() }}</div>
        @endif
    </div>
</div>
@endsection