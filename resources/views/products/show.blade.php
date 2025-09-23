@extends('layout.layout')

@section('title', 'Ürün Detayı')
@section('subTitle', 'Ürün Bilgileri')

@push('styles')
<style>
    .product-image-large {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .product-image-large:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 16px rgba(0,0,0,0.3);
    }
    .info-card {
        border-left: 4px solid;
    }
    .info-card.primary { border-left-color: #0d6efd; }
    .info-card.success { border-left-color: #198754; }
    .info-card.info { border-left-color: #0dcaf0; }
    .info-card.warning { border-left-color: #ffc107; }
    .info-card.secondary { border-left-color: #6c757d; }
</style>
@endpush

@section('content')
<!-- Üst Başlık ve Aksiyonlar -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">{{ $product->name }}</h4>
                <p class="text-muted mb-0">
                    <span class="badge bg-primary me-2">{{ $product->sku ?? 'Kod yok' }}</span>
                    <span class="badge bg-info me-2">{{ $product->category ?? 'Kategori yok' }}</span>
                    @if($product->brand)
                        <span class="badge bg-warning">{{ $product->brand }}</span>
                    @endif
                </p>
            </div>
            <div>
                <a href="{{ route('products.edit', $product) }}" class="btn btn-success me-2">
                    <iconify-icon icon="solar:pen-outline" class="me-1"></iconify-icon>
                    Düzenle
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    <iconify-icon icon="solar:arrow-left-outline" class="me-1"></iconify-icon>
                    Geri Dön
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sol Kolon - Görsel ve Temel Bilgiler -->
    <div class="col-lg-4 mb-4">
        <!-- Ürün Görseli -->
        @if($product->image)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center p-4">
                <h6 class="fw-semibold mb-3 text-primary">
                    <iconify-icon icon="solar:gallery-outline" class="me-2"></iconify-icon>
                    Ürün Görseli
                </h6>
                <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded shadow cursor-pointer product-image-large" style="max-width: 100%; max-height: 300px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageModal">
            </div>
        </div>
        @endif

        <!-- Temel Bilgiler -->
        <div class="card border-0 shadow-sm info-card primary">
            <div class="card-header bg-primary text-white">
                <h6 class="fw-semibold mb-0">
                    <iconify-icon icon="solar:info-circle-outline" class="me-2"></iconify-icon>
                    Temel Bilgiler
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Ürün Adı</label>
                    <div class="fw-semibold text-dark">{{ $product->name }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label text-muted small">Ürün Kodu</label>
                        <div>
                            @if($product->sku)
                                <span class="badge bg-primary">{{ $product->sku }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small">Kategori</label>
                        <div>
                            @if($product->category)
                                <span class="badge bg-info">{{ $product->category }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label text-muted small">Marka</label>
                        <div>
                            @if($product->brand)
                                <span class="badge bg-warning">{{ $product->brand }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small">Beden</label>
                        <div>
                            @if($product->size)
                                <span class="badge bg-success">{{ $product->size }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label text-muted small">Renk</label>
                    <div>
                        @if($product->color)
                            <span class="badge bg-secondary">{{ $product->color }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sağ Kolon - Detaylı Bilgiler -->
    <div class="col-lg-8">
        <!-- Fiyat ve Stok Bilgileri -->
        <div class="card border-0 shadow-sm mb-4 info-card success">
            <div class="card-header bg-success text-white">
                <h6 class="fw-semibold mb-0">
                    <iconify-icon icon="solar:dollar-outline" class="me-2"></iconify-icon>
                    Fiyat ve Stok Bilgileri
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Alış Fiyatı</label>
                        <div class="fw-semibold text-info fs-5">
                            @if($product->cost)
                                {{ number_format($product->cost, 2) }} TL
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Satış Fiyatı</label>
                        <div class="fw-semibold text-success fs-5">
                            @if($product->price)
                                {{ number_format($product->price, 2) }} TL
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Birim</label>
                        <div class="fw-semibold">
                            @if($product->unit)
                                {{ $product->unit }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Para Birimi</label>
                        <div class="fw-semibold">
                            TL
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Başlangıç Stok</label>
                        <div class="fw-semibold text-primary fs-5">
                            @if($product->initial_stock)
                                {{ $product->initial_stock }} Adet
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Kritik Stok</label>
                        <div class="fw-semibold text-warning fs-5">
                            @if($product->critical_stock)
                                {{ $product->critical_stock }} Adet
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Durum ve Ek Bilgiler -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm info-card info">
                    <div class="card-header bg-info text-white">
                        <h6 class="fw-semibold mb-0">
                            <iconify-icon icon="solar:settings-outline" class="me-2"></iconify-icon>
                            Durum Bilgileri
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Durum</label>
                            <div>
                                @if($product->is_active)
                                    <span class="badge bg-success fs-6">Aktif</span>
                                @else
                                    <span class="badge bg-danger fs-6">Pasif</span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label text-muted small">Oluşturulma</label>
                            <div class="fw-semibold">{{ $product->created_at->format('d.m.Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm info-card warning">
                    <div class="card-header bg-warning text-white">
                        <h6 class="fw-semibold mb-0">
                            <iconify-icon icon="solar:code-outline" class="me-2"></iconify-icon>
                            Kod Bilgileri
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Barkod</label>
                            <div class="fw-semibold font-monospace">
                                @if($product->barcode)
                                    {{ $product->barcode }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6 text-center">
                                <small class="text-muted d-block">Kalıcı Barkod</small>
                                @if($product->barcode_svg_path)
                                    <img src="/{{ $product->barcode_svg_path }}" alt="Barcode" class="img-fluid" />
                                    <div class="small mt-1">{{ $product->permanent_barcode }}</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                            <div class="col-6 text-center">
                                <small class="text-muted d-block">QR Kod</small>
                                @if($product->qr_svg_path)
                                    <img src="/{{ $product->qr_svg_path }}" alt="QR" class="img-fluid" style="max-width:140px" />
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Açıklama -->
        @if($product->description)
        <div class="card border-0 shadow-sm info-card secondary">
            <div class="card-header bg-secondary text-white">
                <h6 class="fw-semibold mb-0">
                    <iconify-icon icon="solar:document-text-outline" class="me-2"></iconify-icon>
                    Açıklama
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $product->description }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Image Modal -->
@if($product->image)
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">{{ $product->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded shadow" style="max-width: 100%; max-height: 80vh;">
                <div class="mt-3">
                    <small class="text-muted">{{ $product->sku ?? 'Kod yok' }} • {{ $product->category ?? 'Kategori yok' }} • {{ $product->brand ?? 'Marka yok' }}</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">Düzenle</a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection