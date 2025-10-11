@extends('layout.layout')

@section('title', 'Seri Düzenle')
@section('subTitle', 'Seri Ürün Bilgilerini Güncelle')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Seri Ürün Düzenle</h5>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#quickStockModal">
                    <iconify-icon icon="solar:database-outline" class="me-1"></iconify-icon>
                    Hızlı Stok
                </button>
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
                                @php
                                    $allCategories = ['Gömlek','Ceket','Takım Elbise','Pantalon'];
                                    $options = isset($allowedCategories) && is_array($allowedCategories) && count($allowedCategories) > 0 ? $allowedCategories : $allCategories;
                                @endphp
                                @foreach($options as $cat)
                                    <option value="{{ $cat }}" {{ old('category', $series->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
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

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Maliyet</label>
                            <input type="number" class="form-control radius-8 @error('cost') is-invalid @enderror" 
                                   name="cost" value="{{ old('cost', $series->cost) }}" step="0.01" min="0" max="999999.99">
                            @error('cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Döviz</label>
                            <select name="cost_currency" class="form-control radius-8">
                                <option value="TRY" {{ (old('cost_currency', $series->cost_currency ?? 'TRY')) == 'TRY' ? 'selected' : '' }}>TRY</option>
                                <option value="USD" {{ (old('cost_currency', $series->cost_currency ?? 'TRY')) == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ (old('cost_currency', $series->cost_currency ?? 'TRY')) == 'EUR' ? 'selected' : '' }}>EUR</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Satış Fiyatı</label>
                            <input type="number" class="form-control radius-8 @error('price') is-invalid @enderror" 
                                   name="price" value="{{ old('price', $series->price) }}" step="0.01" min="0" max="999999.99">
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Döviz</label>
                            <select name="price_currency" class="form-control radius-8">
                                <option value="TRY" {{ (old('price_currency', $series->price_currency ?? 'TRY')) == 'TRY' ? 'selected' : '' }}>TRY</option>
                                <option value="USD" {{ (old('price_currency', $series->price_currency ?? 'TRY')) == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ (old('price_currency', $series->price_currency ?? 'TRY')) == 'EUR' ? 'selected' : '' }}>EUR</option>
                            </select>
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

                        @if($series->colorVariants && $series->colorVariants->count() > 0)
                            <!-- Multi-Color Series Stock Management -->
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-3 text-primary">
                                            <iconify-icon icon="solar:palette-outline" class="me-2"></iconify-icon>
                                            Renk Bazlı Stok Yönetimi
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Renk</th>
                                                        <th>Mevcut Stok (Seri)</th>
                                                        <th>Kritik Stok (Seri)</th>
                                                        <th>Durum</th>
                                                        <th>İşlemler</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($series->colorVariants as $index => $variant)
                                                        <tr class="{{ ($variant->critical_stock > 0 && $variant->stock_quantity <= $variant->critical_stock) ? 'table-warning' : '' }}">
                                                            <td>
                                                                <span class="badge" style="background:#e9f7ef; color:#198754; border:1px solid #c3e6cb;">
                                                                    {{ $variant->color }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <input type="number" 
                                                                       name="color_variants[{{ $variant->id }}][stock_quantity]" 
                                                                       class="form-control form-control-sm" 
                                                                       value="{{ $variant->stock_quantity }}" 
                                                                       min="0" 
                                                                       style="width: 80px;">
                                                            </td>
                                                            <td>
                                                                <input type="number" 
                                                                       name="color_variants[{{ $variant->id }}][critical_stock]" 
                                                                       class="form-control form-control-sm" 
                                                                       value="{{ $variant->critical_stock }}" 
                                                                       min="0" 
                                                                       style="width: 80px;">
                                                            </td>
                                                            <td>
                                                                @if($variant->critical_stock > 0 && $variant->stock_quantity <= $variant->critical_stock)
                                                                    <span class="badge bg-danger">Kritik</span>
                                                                @else
                                                                    <span class="badge bg-success">Normal</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="form-check form-switch">
                                                                    <input type="hidden" name="color_variants[{{ $variant->id }}][is_active]" value="0">
                                                                    <input class="form-check-input" 
                                                                           type="checkbox" 
                                                                           name="color_variants[{{ $variant->id }}][is_active]" 
                                                                           value="1" 
                                                                           {{ $variant->is_active ? 'checked' : '' }}>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th>Toplam</th>
                                                        <th class="fw-bold">{{ $series->colorVariants->sum('stock_quantity') }} Seri</th>
                                                        <th class="fw-bold">{{ $series->colorVariants->sum('critical_stock') }} Seri</th>
                                                        <th>
                                                            @php
                                                                $hasSeriesCritical = $series->colorVariants->filter(function($v){ return $v->critical_stock > 0 && $v->stock_quantity <= $v->critical_stock; })->count() > 0;
                                                            @endphp
                                                            @if($hasSeriesCritical)
                                                                <span class="badge bg-warning">Dikkat</span>
                                                            @else
                                                                <span class="badge bg-success">İyi</span>
                                                            @endif
                                                        </th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <iconify-icon icon="solar:info-circle-outline" class="me-1"></iconify-icon>
                                                Her renk için ayrı stok takibi yapılır. Ana seri stoku otomatik olarak güncellenir.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

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
                                    <img src="{{ $series->image_url }}?v={{ time() }}" alt="{{ $series->name }}" 
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
<!-- Quick Stock Modal -->
<div class="modal fade" id="quickStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hızlı Stok Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Mevcut Stok (Seri)</label>
                    <input type="text" class="form-control" value="{{ $series->stock_quantity ?? 0 }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ekle (Seri Adedi)</label>
                    <input type="number" class="form-control" id="qsAddStock" min="0" step="1" placeholder="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Kritik Stok</label>
                    <input type="number" class="form-control" id="qsCritical" min="0" step="1" value="{{ $series->critical_stock ?? 0 }}">
                </div>
                <div class="small text-muted">Eski değerler görüntülenir, yeni değerleri kaydetmek için güncelleyin.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <button type="button" class="btn btn-primary" id="qsSaveBtn">Kaydet</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('qsSaveBtn')?.addEventListener('click', function(){
    const addStock = parseInt(document.getElementById('qsAddStock').value || '0', 10);
    const critical = parseInt(document.getElementById('qsCritical').value || '0', 10);
    fetch('{{ url('/products/series/' . $series->id . '/quick-stock') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ add_stock: addStock, critical_stock: critical })
    }).then(r => r.json()).then(resp => {
        if (resp.success) {
            alert('Güncellendi: Yeni stok ' + resp.data.stock_quantity + ' seri, kritik ' + resp.data.critical_stock);
            location.reload();
        } else {
            alert('Güncelleme başarısız');
        }
    }).catch(()=>alert('Hata oluştu'));
});
</script>
@endpush

@endsection
