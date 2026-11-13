<div class="modal fade" id="add-to-cart-{{ $product->id }}" tabindex="-1" aria-labelledby="add-to-cart-title-{{ $product->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('cart.add', $product) }}" method="POST" class="modal-content">
            @csrf
            @php($remainingStock = max(0, (int) $product->stok - (int) (session('cart', [])[$product->id] ?? 0)))
            <div class="modal-header">
                <div>
                    <h2 class="modal-title fs-5 fw-bold" id="add-to-cart-title-{{ $product->id }}">Tambah ke Keranjang</h2>
                    <p class="text-muted small mb-0">{{ $product->name }} · Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="quantity-{{ $product->id }}">Jumlah produk</label>
                    <input id="quantity-{{ $product->id }}" name="quantity" type="number" class="form-control" min="1" max="{{ $remainingStock }}" value="1" required>
                    <div class="form-text">Sisa yang dapat ditambahkan: {{ $remainingStock }}</div>
                </div>

                @if($product->isAutomotive())
                    @if(auth()->user()?->role === 'customer' && $vehicles->isNotEmpty())
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="vehicle-{{ $product->id }}">Kendaraan untuk produk ini <span class="text-muted fw-normal">(opsional)</span></label>
                            <select id="vehicle-{{ $product->id }}" name="vehicle_id" class="form-select">
                                <option value="">Pilih kendaraan terdaftar</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->plate_number }})</option>
                                @endforeach
                            </select>
                        </div>
                        <label class="form-label fw-semibold" for="vehicle-brand-{{ $product->id }}">Atau tulis merek dan model</label>
                    @else
                        <label class="form-label fw-semibold" for="vehicle-brand-{{ $product->id }}">Merek dan model kendaraan</label>
                    @endif
                    <input
                        id="vehicle-brand-{{ $product->id }}"
                        name="vehicle_brand"
                        class="form-control"
                        maxlength="255"
                        placeholder="Contoh: Honda Vario 160"
                    >
                    <div class="form-text">Kendaraan bersifat opsional. Jika ingin, pilih kendaraan atau isi merek dan modelnya.</div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">Tambahkan</button>
            </div>
        </form>
    </div>
</div>
