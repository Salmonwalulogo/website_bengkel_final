@extends('backend.layouts.cashier')

@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-bell me-2"></i>Notifikasi</h1>
            <small class="text-muted">{{ $unreadNotificationCount }} belum dibaca</small>
        </div>
        <form action="{{ route('kasir.notifications.markAllAsRead') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-success" @disabled($unreadNotificationCount === 0)>
                <i class="fas fa-check-double me-2"></i>Tandai semua dibaca
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="list-group list-group-flush">
                @forelse($notifications as $notification)
                    <article class="list-group-item {{ $notification->is_read ? '' : 'list-group-item-warning' }}">
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <strong>{{ $notification->title }}</strong>
                            <small class="text-muted">{{ $notification->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        <p class="mb-2 mt-1">{{ $notification->message }}</p>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info">{{ ucfirst($notification->type ?? 'general') }}</span>
                            @if(!$notification->is_read)
                                <form action="{{ route('kasir.notifications.read', $notification) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Tandai dibaca</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-bell fa-2x mb-3 d-block"></i>
                        Belum ada notifikasi pesanan.
                    </div>
                @endforelse
            </div>
            @if($notifications->hasPages())
                <div class="mt-3">{{ $notifications->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
