@extends('layout.layout')

@section('title', 'Seri Düzenle')
@section('subTitle', 'Seri Ürün Bilgilerini Güncelle')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Seri Ürün Düzenle</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('products.series.update', $series) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3">
                        <!-- Temel Bilgiler -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3">Temel Bilgiler</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Seri Adı *</label>
                            <input type="text" class="form-control radius-8 @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name', $series->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">SKU</label>
                            <input type="text" class="form-control radius-8 @error('sku') is-invalid @enderror" 
                                   name="sku" value="{{ old('sku', $series->sku) }}">
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Açıklama</label>
                            <textarea class="form-control radius-8 @error('description') is-invalid @enderror" 
                                      name="description" rows="3">{{ old('description', $series->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kategori ve Marka -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Kategori</label>
                            <select class="form-control radius-8 @error('category') is-invalid @enderror" name="category">
                                <option value="">Kategori seçin</option>
                                <option value="Gömlek" {{ old('category', $series->category) == 'Gömlek' ? 'selected' : '' }}>Gömlek</option>
                                <option value="Ceket" {{ old('category', $series->category) == 'Ceket' ? 'selected' : '' }}>Ceket</option>
                                <option value="Takım" {{ old('category', $series->category) == 'Takım' ? 'selected' : '' }}>Takım</option>
                                <option value="Diğer" {{ old('category', $series->category) == 'Diğer' ? 'selected' : '' }}>Diğer</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Marka</label>
                            <select class="form-control radius-8 @error('brand') is-invalid @enderror" name="brand">
                                <option value="">Marka seçin</option>
                                <option value="Ronex" {{ old('brand', $series->brand) == 'Ronex' ? 'selected' : '' }}>Ronex</option>
                                <option value="Diğer" {{ old('brand', $series->brand) == 'Diğer' ? 'selected' : '' }}>Diğer</option>
                            </select>
                            @error('brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Fiyat Bilgileri -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3">Fiyat Bilgileri</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Maliyet (₺)</label>
                            <input type="number" class="form-control radius-8 @error('cost') is-invalid @enderror" 
                                   name="cost" value="{{ old('cost', $series->cost) }}" step="0.01" min="0" max="999999.99">
                            @error('cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Satış Fiyatı (₺)</label>
                            <input type="number" class="form-control radius-8 @error('price') is-invalid @enderror" 
                                   name="price" value="{{ old('price', $series->price) }}" step="0.01" min="0" max="999999.99">
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Stok Bilgileri -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3">Stok Bilgileri</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Stok Miktarı (Seri) *</label>
                            <input type="number" class="form-control radius-8 @error('stock_quantity') is-invalid @enderror" 
                                   name="stock_quantity" value="{{ old('stock_quantity', $series->stock_quantity) }}" min="0" required>
                            @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Kritik Stok</label>
                            <input type="number" class="form-control radius-8 @error('critical_stock') is-invalid @enderror" 
                                   name="critical_stock" value="{{ old('critical_stock', $series->critical_stock) }}" min="0">
                            @error('critical_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Görsel -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3">Görsel</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Ürün Görseli</label>
                            <input type="file" class="form-control radius-8 @error('image') is-invalid @enderror" 
                                   name="image" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($series->image)
                                <div class="mt-2">
                                    <small class="text-muted">Mevcut görsel:</small>
                                    <img src="{{ asset('storage/' . $series->image) }}?v={{ time() }}" alt="{{ $series->name }}" 
                                         class="img-thumbnail mt-1" style="max-width: 100px;">
                                </div>
                            @endif
                        </div>

                        <!-- Durum -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Durum</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                       id="is_active" {{ old('is_active', $series->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Aktif
                                </label>
                            </div>
                        </div>

                        <!-- Seri İçeriği (Readonly) -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3">Seri İçeriği</h6>
                            <div class="alert alert-info">
                                <i class="ri-information-line me-2"></i>
                                <strong>Not:</strong> Seri içeriği (bedenler ve miktarlar) düzenlenemez. Yeni bir seri oluşturmak için mevcut seriyi silin ve yenisini ekleyin.
                            </div>
                            
                            @if($series->seriesItems->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Beden</th>
                                                <th class="text-center">Seri Başına Adet</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($series->seriesItems as $item)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary">{{ $item->size }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="fw-semibold">{{ $item->quantity_per_series }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="ri-inbox-line text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2 mb-0">Seri içeriği bulunamadı</p>
                                </div>
                            @endif
                        </div>

                        <!-- Butonlar -->
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('products.series.show', $series) }}" class="btn btn-secondary">
                                    <i class="ri-arrow-left-line me-1"></i>İptal
                                </a>
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="ri-save-line me-1"></i>Güncelle
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
