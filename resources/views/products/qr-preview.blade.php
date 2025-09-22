@extends('layout.layout')

@section('title', 'Ürün Hızlı Önizleme')
@section('subTitle', 'QR Tarama')

@section('content')
<div class="row">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">{{ $product->name }}</h5>
                @if($product->getImageUrlAttribute())
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="height:48px; width:auto; border-radius:6px; object-fit:cover;">
                @endif
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-secondary">SKU</div>
                        <div class="fw-semibold">{{ $product->sku ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary">Barkod</div>
                        <div class="fw-semibold">{{ $product->barcode ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary">Kategori</div>
                        <div class="fw-semibold">{{ $product->category ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary">Marka</div>
                        <div class="fw-semibold">{{ $product->brand ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary">Beden</div>
                        <div class="fw-semibold">{{ $product->size ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary">Renk</div>
                        <div class="fw-semibold">{{ $product->color ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary">Fiyat</div>
                        <div class="fw-bold text-success">{{ number_format((float)$product->price, 2, ',', '.') }} ₺</div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary">Stok</div>
                        <div class="fw-bold {{ ($product->stock_quantity ?? 0) <= ($product->critical_stock ?? -1) ? 'text-danger' : 'text-primary' }}">
                            {{ (int)($product->stock_quantity ?? 0) }}
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="text-secondary">Açıklama</div>
                        <div class="fw-normal">{{ $product->description ?? '-' }}</div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between gap-2">
                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary w-100">
                    <iconify-icon icon="solar:pen-new-square-outline" class="me-1"></iconify-icon>
                    Düzenle
                </a>
                <a href="{{ route('products.edit', $product) }}#stock" class="btn btn-outline-success w-100">
                    <iconify-icon icon="solar:box-outline" class="me-1"></iconify-icon>
                    Stok Güncelle
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

