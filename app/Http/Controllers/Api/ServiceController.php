<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();
        if (! $request->user()?->isAdmin()) {
            $query->where('status', 'active');
        }

        $services = $query->orderBy('category')->orderBy('name')->get();
        return response()->json(['data' => $services]);
    }

    public function store(Request $request)
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Hanya Admin yang dapat menambah layanan'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'harga' => ['required', 'numeric', 'min:0'],
            'duration' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $validated['category'] ??= 'Lainnya';
        $service = Service::create($validated);

        return response()->json(['message' => 'Layanan berhasil ditambahkan', 'data' => $service], 201);
    }

    public function update(Request $request, Service $service)
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Hanya Admin yang dapat mengubah layanan'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'harga' => ['sometimes', 'numeric', 'min:0'],
            'duration' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $service->update($validated);

        return response()->json(['message' => 'Layanan berhasil diperbarui', 'data' => $service]);
    }

    public function destroy(Request $request, Service $service)
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Hanya Admin yang dapat menghapus layanan'], 403);
        }

        if ($service->bookings()->exists()) {
            $service->update(['status' => 'inactive']);

            return response()->json([
                'message' => 'Layanan dinonaktifkan agar riwayat permintaan tetap tersimpan.',
            ]);
        }

        $service->delete();

        return response()->json(['message' => 'Layanan berhasil dihapus']);
    }
}
