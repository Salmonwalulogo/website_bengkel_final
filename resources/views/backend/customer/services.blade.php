@extends('backend.layouts.customer')

@section('title', 'Semua Layanan')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <span class="dashboard-eyebrow text-success">AREA PELANGGAN</span>
            <h1 class="h2 mb-1">Semua Layanan</h1>
            <p class="text-muted mb-0">Jelajahi layanan UD Rumah Daun dan pilih yang sesuai kebutuhan Anda.</p>
        </div>
        <a href="{{ route('customer.bookings') }}" class="btn btn-outline-success">
            <i class="fas fa-clipboard-list me-2" aria-hidden="true"></i>Pesanan Saya
        </a>
    </div>

    @forelse($services as $category => $items)
        <section class="mb-4" aria-labelledby="service-category-{{ $loop->index }}">
            <h2 class="h5 fw-bold mb-3" id="service-category-{{ $loop->index }}">{{ $category }}</h2>
            <div class="row g-3">
                @foreach($items as $service)
                    <div class="col-md-6 col-xl-4">
                        <article class="card h-100 border-0 shadow-sm rounded-4">
                            <div class="card-body d-flex flex-column p-4">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success p-3 mb-3">
                                    <i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i>
                                </span>
                                <h3 class="h5 fw-bold">{{ $service->name }}</h3>
                                <p class="text-muted flex-grow-1 mb-3">{{ $service->description ?: 'Layanan UD Rumah Daun. Sampaikan kebutuhan Anda saat memesan.' }}</p>
                                <div class="d-flex justify-content-between align-items-end gap-2 border-top pt-3 mb-3">
                                    <strong class="text-success">
                                        {{ (float) $service->harga > 0 ? $service->formatted_harga : 'Harga dikonfirmasi admin' }}
                                    </strong>
                                    @if($service->duration)
                                        <small class="text-muted text-nowrap"><i class="far fa-clock me-1" aria-hidden="true"></i>{{ $service->duration }}</small>
                                    @endif
                                </div>
                                <a href="{{ route('customer.booking.create', ['service' => $service->id]) }}" class="btn btn-success">
                                    <i class="fas fa-calendar-plus me-2" aria-hidden="true"></i>Pesan layanan
                                </a>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>
    @empty
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-concierge-bell fa-2x text-success mb-3" aria-hidden="true"></i>
                <h2 class="h5">Belum ada layanan aktif</h2>
                <p class="text-muted mb-0">Silakan kembali lagi nanti untuk melihat layanan yang tersedia.</p>
            </div>
        </div>
    @endforelse
</div>
@endsection
