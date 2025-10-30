<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    private function currentAccountId()
    {
        return session('current_account_id');
    }

    public function index()
    {
        $accountId = $this->currentAccountId();
        $categories = ProductCategory::when($accountId !== null, function($q) use ($accountId) {
                return $q->where('account_id', $accountId);
            })
            ->orderBy('name')
            ->paginate(20);
        return view('products.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('products.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean'
        ]);

        $validated['account_id'] = $this->currentAccountId();
        $validated['is_active'] = $request->boolean('is_active', true);

        ProductCategory::create($validated);

        return redirect()->route('products.categories.index')->with('success', 'Kategori kaydedildi');
    }

    public function edit(ProductCategory $productCategory)
    {
        // Güvenlik: sadece seçili hesaba ait kategori
        if ($this->currentAccountId() !== null && $productCategory->account_id !== $this->currentAccountId()) {
            abort(404);
        }
        return view('products.categories.edit', ['category' => $productCategory]);
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        if ($this->currentAccountId() !== null && $productCategory->account_id !== $this->currentAccountId()) {
            abort(404);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean'
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $productCategory->update($validated);
        return redirect()->route('products.categories.index')->with('success', 'Kategori güncellendi');
    }

    public function destroy(ProductCategory $productCategory)
    {
        if ($this->currentAccountId() !== null && $productCategory->account_id !== $this->currentAccountId()) {
            abort(404);
        }
        $productCategory->delete();
        return redirect()->route('products.categories.index')->with('success', 'Kategori silindi');
    }
}


