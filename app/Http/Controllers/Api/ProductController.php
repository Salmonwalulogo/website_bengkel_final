<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category:id,name');
        if (! $request->user()?->isAdmin()) {
            $query->where('status', 'active')->where('is_active', true);
            if ($request->user()?->role === 'customer') {
                $query->forCustomerCatalog();
            } else {
                $query->forPublicCatalog();
            }
        }

        $products = $query->latest()->paginate(12);

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(Request $request, Product $product)
    {
        if (! $request->user()?->isAdmin()) {
            abort_unless($product->status === 'active' && $product->is_active, 404, 'Produk tidak ditemukan.');
            $visible = $request->user()?->role === 'customer'
                ? $product->isVisibleToCustomers()
                : $product->isVisibleToPublic();
            abort_unless($visible, 404, 'Produk tidak ditemukan.');
        }

        return response()->json([
            'data' => $product->load('category:id,name'),
        ]);
    }

    public function store(Request $request)
    {
        $product = Product::create($this->validatedProduct($request));

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'data' => $product->load('category:id,name'),
        ], 201);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validatedProduct($request, $product));

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'data' => $product->fresh()->load('category:id,name'),
        ]);
    }

    public function destroy(Product $product)
    {
        $product->update([
            'status' => 'inactive',
            'is_active' => false,
        ]);

        return response()->json(['message' => 'Produk berhasil dinonaktifkan.']);
    }

    private function validatedProduct(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'product_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'product_code')->ignore($product?->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'visibility' => ['sometimes', Rule::in([
                Product::VISIBILITY_PUBLIC,
                Product::VISIBILITY_CUSTOMER,
                Product::VISIBILITY_BOTH,
            ])],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('status', $validated) && !array_key_exists('is_active', $validated)) {
            $validated['is_active'] = $validated['status'] === 'active';
        }

        return $validated;
    }
}
