<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
                          ->latest()
                          ->paginate(10);
        return view('backend.admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::query()->orderBy('name')->get();
        return view('backend.admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => 'required|in:active,inactive',
            'visibility' => 'sometimes|required|in:public,customer,both',
        ]);

        $validated['is_active'] = $validated['status'] === 'active';
        $validated['visibility'] ??= Product::VISIBILITY_PUBLIC;

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('products', 'public');
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load('category');
        return view('backend.admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::query()->orderBy('name')->get();
        return view('backend.admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => 'required|in:active,inactive',
            'visibility' => 'sometimes|required|in:public,customer,both',
        ]);

        $validated['is_active'] = $validated['status'] === 'active';

        if ($request->hasFile('foto')) {
            if ($product->foto) {
                Storage::disk('public')->delete($product->foto);
            }
            $validated['foto'] = $request->file('foto')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->update([
            'status' => 'inactive',
            'is_active' => false,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk dinonaktifkan. Riwayat penjualan dan data produk tetap tersimpan.');
    }
}