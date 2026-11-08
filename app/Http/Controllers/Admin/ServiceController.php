<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::latest()->paginate(10);
        return view('backend.admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('backend.admin.services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'duration' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        Service::create($validated);

        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Service $service)
    {
        return view('backend.admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'duration' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service->update($validated);

        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        if ($service->bookings()->exists()) {
            $service->update(['status' => 'inactive']);

            return redirect()->route('admin.services.index')
                ->with('success', 'Layanan dinonaktifkan agar riwayat pesanan tetap tersimpan.');
        }

        $service->delete();
        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil dihapus.');
    }
}