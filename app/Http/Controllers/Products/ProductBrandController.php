<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\ProductBrand;
use Illuminate\Http\Request;

class ProductBrandController extends Controller
{
    private function currentAccountId()
    {
        return session('current_account_id');
    }

    public function index()
    {
        $accountId = $this->currentAccountId();
        $brands = ProductBrand::when($accountId !== null, function($q) use ($accountId) {
                return $q->where('account_id', $accountId);
            })
            ->orderBy('name')
            ->paginate(20);
        return view('products.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('products.brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean'
        ]);

        $validated['account_id'] = $this->currentAccountId();
        $validated['is_active'] = $request->boolean('is_active', true);

        ProductBrand::create($validated);

        return redirect()->route('products.brands.index')->with('success', 'Marka kaydedildi');
    }

    public function edit(ProductBrand $productBrand)
    {
        if ($this->currentAccountId() !== null && $productBrand->account_id !== $this->currentAccountId()) {
            abort(404);
        }
        return view('products.brands.edit', ['brand' => $productBrand]);
    }

    public function update(Request $request, ProductBrand $productBrand)
    {
        if ($this->currentAccountId() !== null && $productBrand->account_id !== $this->currentAccountId()) {
            abort(404);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean'
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $productBrand->update($validated);
        return redirect()->route('products.brands.index')->with('success', 'Marka güncellendi');
    }

    public function destroy(ProductBrand $productBrand)
    {
        if ($this->currentAccountId() !== null && $productBrand->account_id !== $this->currentAccountId()) {
            abort(404);
        }
        $productBrand->delete();
        return redirect()->route('products.brands.index')->with('success', 'Marka silindi');
    }

    public function search(Request $request)
    {
        $q = (string) $request->get('q', '');
        $accountId = $this->currentAccountId();
        if (strlen($q) < 1) {
            return response()->json([]);
        }
        $items = ProductBrand::when($accountId !== null, function($q2) use ($accountId){
                $q2->where('account_id', $accountId);
            })
            ->where('name', 'like', "%{$q}%")
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(10)
            ->get(['id','name']);
        return response()->json($items);
    }
}


