@extends('layout.layout')

@section('title', 'Seri Detayları')
@section('subTitle', 'Seri Ürün Bilgileri')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">{{ $series->name }}</h5>
                    <p class="text-muted mb-0">Seri Ürün Detayları</p>
                </div>
                <div>
                    <a href="{{ route('products.series.edit', $series) }}" class="btn btn-outline-primary me-2">
                        <i class="ri-edit-line me-1"></i>Düzenle
                    </a>
                    <a href="{{ route('products.series.index') }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i>Geri Dön
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Sol Kolon - Temel Bilgiler -->
                    <div class="col-md-8">
                        <div class="row g-4">
                            <!-- Seri Bilgileri -->
                            <div class="col-12">
                                <h6 class="fw-semibold text-primary mb-3">Seri Bilgileri</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">Seri Adı</label>
                                        <div class="form-control-plaintext fw-semibold">{{ $series->name }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">SKU</label>
                                        <div class="form-control-plaintext">
                                            @if($series->sku)
                                                <span class="badge bg-secondary">{{ $series->sku }}</span>
                                            @else
                                                <span class="text-muted">SKU Yok</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-muted">Açıklama</label>
                                        <div class="form-control-plaintext">
                                            {{ $series->description ?? 'Açıklama yok' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kategori ve Marka -->
                            <div class="col-12">
                                <h6 class="fw-semibold text-primary mb-3">Kategori ve Marka</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">Kategori</label>
                                        <div class="form-control-plaintext">
                                            @if($series->category)
                                                <span class="badge bg-info">{{ $series->category }}</span>
                                            @else
                                                <span class="text-muted">Kategori yok</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">Marka</label>
                                        <div class="form-control-plaintext">
                                            @if($series->brand)
                                                <span class="badge bg-warning">{{ $series->brand }}</span>
                                            @else
                                                <span class="text-muted">Marka yok</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Fiyat Bilgileri -->
                            <div class="col-12">
                                <h6 class="fw-semibold text-primary mb-3">Fiyat Bilgileri</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">Maliyet</label>
                                        <div class="form-control-plaintext fw-semibold text-danger">
                                            ₺{{ number_format($series->cost, 2) }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">Satış Fiyatı</label>
                                        <div class="form-control-plaintext fw-semibold text-success">
                                            ₺{{ number_format($series->price, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Seri Detayları -->
                            <div class="col-12">
                                <h6 class="fw-semibold text-primary mb-3">Seri Detayları</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-muted">Seri Tipi</label>
                                        <div class="form-control-plaintext">
                                            @if($series->series_type === 'fixed')
                                                <span class="badge bg-primary">Sabit Seri</span>
                                            @else
                                                <span class="badge bg-success">Özel Seri</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-muted">Seri Boyutu</label>
                                        <div class="form-control-plaintext fw-semibold">
                                            {{ $series->series_size }}'li Seri
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-muted">Durum</label>
                                        <div class="form-control-plaintext">
                                            @if($series->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Pasif</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stok Bilgileri -->
                            <div class="col-12">
                                <h6 class="fw-semibold text-primary mb-3">Stok Bilgileri</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-muted">Stok (Seri)</label>
                                        <div class="form-control-plaintext fw-semibold">
                                            {{ number_format($series->stock_quantity) }} Seri
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-muted">Toplam Ürün</label>
                                        <div class="form-control-plaintext fw-semibold text-success">
                                            {{ number_format($series->total_product_count) }} Adet
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-muted">Kritik Stok</label>
                                        <div class="form-control-plaintext">
                                            @if($series->critical_stock > 0)
                                                {{ number_format($series->critical_stock) }} Seri
                                                @if($series->stock_quantity <= $series->critical_stock)
                                                    <i class="ri-alert-line text-danger ms-1" title="Kritik Stok!"></i>
                                                @endif
                                            @else
                                                <span class="text-muted">Belirlenmemiş</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sağ Kolon - Görsel ve Seri İçeriği -->
                    <div class="col-md-4">
                        <!-- Görsel -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Ürün Görseli</h6>
                            </div>
                            <div class="card-body text-center">
                                @if($series->image)
                                    <img src="{{ asset('storage/' . $series->image) }}?v={{ time() }}" 
                                         alt="{{ $series->name }}" 
                                         class="img-fluid rounded" 
                                         style="max-height: 200px;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <div class="text-center">
                                            <i class="ri-image-line text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2 mb-0">Görsel yok</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Seri İçeriği -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Seri İçeriği</h6>
                            </div>
                            <div class="card-body">
                                @if($series->seriesItems->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Beden</th>
                                                    <th class="text-center">Adet</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($series->seriesItems as $item)
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-light text-dark">{{ $item->size }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="fw-semibold">{{ $item->quantity_per_series }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <small class="text-muted">
                                            Toplam {{ $series->seriesItems->count() }} beden
                                        </small>
                                    </div>
                                @else
                                    <div class="text-center py-3">
                                        <i class="ri-inbox-line text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2 mb-0">Seri içeriği bulunamadı</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
