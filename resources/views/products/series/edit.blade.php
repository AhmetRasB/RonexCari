@extends('layout.layout')

@section('title', 'Seri Düzenle')
@section('subTitle', 'Seri Ürün Bilgilerini Güncelle')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Seri Ürün Düzenle</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('products.series.create', ['parent' => $series->id]) }}" class="btn btn-info">
                        <iconify-icon icon="solar:add-circle-outline" class="me-1"></iconify-icon>
                        Yeni Seri Boyutu Ekle
                    </a>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#quickStockModal">
                        <iconify-icon icon="solar:database-outline" class="me-1"></iconify-icon>
                        Hızlı Stok
                    </button>
                </div>
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

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Barkod</label>
                            <input type="text" class="form-control radius-8 @error('barcode') is-invalid @enderror" 
                                   name="barcode" value="{{ old('barcode', $series->barcode) }}" placeholder="Seri barkodu">
                            @error('barcode')
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
                                @foreach($allowedCategories as $cat)
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

                        <!-- Renk Yönetimi -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3">Renkler</h6>
                            <div class="position-relative">
                                <div id="colorTagsContainer" class="border rounded p-2 min-height-50" style="min-height: 50px; background: #f8f9fa;">
                                    <div id="colorTags" class="d-flex flex-wrap gap-2 mb-2">
                                        @foreach($series->colorVariants as $variant)
                                            <span class="badge bg-primary d-inline-flex align-items-center gap-1" data-color="{{ $variant->color }}">
                                                {{ $variant->color }}
                                                <button type="button" class="btn-close btn-close-white" style="font-size: 0.7em;" onclick="removeColorTag('{{ $variant->color }}')"></button>
                                            </span>
                                        @endforeach
                                    </div>
                                    <input type="text" id="colorInput" class="form-control border-0 bg-transparent" placeholder="Renk yazın ve Enter'a basın..." autocomplete="off" style="box-shadow: none;">
                                </div>
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:palette-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            <small class="text-muted">Her renk için ayrı stok miktarı belirleyebilirsiniz.</small>
                        </div>

                        <!-- Stok Bilgileri -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3">Stok Bilgileri</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Toplam Stok (Adet)</label>
                            <input type="text" class="form-control radius-8" 
                                   value="{{ number_format($series->colorVariants->sum('stock_quantity')) }} Adet" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Toplam Kritik Stok (Adet)</label>
                            <input type="text" class="form-control radius-8" 
                                   value="{{ number_format($series->colorVariants->sum('critical_stock')) }} Adet" readonly>
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
                                                        <th>Mevcut Stok (Adet)</th>
                                                        <th>Kritik Stok (Adet)</th>
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
                                                        <th class="fw-bold">{{ $series->colorVariants->sum('stock_quantity') }} Adet</th>
                                                        <th class="fw-bold">{{ $series->colorVariants->sum('critical_stock') }} Adet</th>
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
<!-- Add Series Size Modal -->
<div class="modal fade" id="addSeriesSizeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Seri Boyutu Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addSeriesSizeForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Seri Boyutu Seçimi -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3">Seri Boyutu Seçimi</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Hangi seri tipine eklemek istiyorsunuz?</label>
                                    <select class="form-select" id="seriesSizeSelect" required>
                                        <option value="">Seri boyutu seçin</option>
                                        <option value="2">2'li Seri</option>
                                        <option value="3">3'lü Seri</option>
                                        <option value="4">4'lü Seri</option>
                                        <option value="5">5'li Seri</option>
                                        <option value="6">6'lı Seri</option>
                                        <option value="7">7'li Seri</option>
                                        <option value="8">8'li Seri</option>
                                        <option value="9">9'lu Seri</option>
                                        <option value="10">10'lu Seri</option>
                                        <option value="custom">Yeni Tip Belirle</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Özel Seri Boyutu</label>
                                    <input type="number" class="form-control" id="customSeriesSize" 
                                           placeholder="Özel seri boyutu" min="2" max="50" disabled>
                                </div>
                            </div>
                        </div>

                        <!-- Renk ve Stok Bilgileri -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3">Renk ve Stok Bilgileri</h6>
                            <div class="mb-3">
                                <label class="form-label">Renk</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="colorInput" 
                                           placeholder="Renk adı yazın ve Enter'a basın">
                                    <button type="button" class="btn btn-outline-secondary" id="addColorBtn">
                                        <iconify-icon icon="solar:add-circle-outline"></iconify-icon>
                                    </button>
                                </div>
                                <div id="colorTagsContainer" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Seri Boyutu Ekle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Color Stock Modal -->
<div class="modal fade" id="colorStockModal" tabindex="-1" aria-labelledby="colorStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="colorStockModalLabel">Renk Stok Ayarları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Renk: <span id="selectedColorName" class="badge bg-primary"></span></label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Stok Miktarı <span class="text-danger">*</span></label>
                    <input type="number" id="colorStockQuantity" class="form-control" placeholder="Stok miktarı" min="0" required>
                    <small class="text-muted">Bu renk için stok miktarı (adet)</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kritik Stok Miktarı</label>
                    <input type="number" id="colorCriticalStock" class="form-control" placeholder="Kritik stok miktarı" min="0">
                    <small class="text-muted">Bu miktarın altına düşünce uyarı gönderilir</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" id="saveColorStock">Kaydet</button>
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
                    <label class="form-label">Mevcut Stok (Adet)</label>
                    <input type="text" class="form-control" value="{{ number_format($series->colorVariants->sum('stock_quantity')) }} Adet" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ekle (Adet)</label>
                    <input type="number" class="form-control" id="qsAddStock" min="0" step="1" placeholder="0">
                </div>
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
document.addEventListener('DOMContentLoaded', function() {
    // Renk sistemi - Create sayfasındaki gibi
    let colorTags = []; // Only NEW colors added by user
    let currentColorIndex = -1;
    let colorStocks = {}; // Renk stok bilgilerini sakla
    let existingColorNames = []; // Track existing colors from the series
    
    // Initialize existing colors list
    const tableRows = document.querySelectorAll('table tbody tr');
    tableRows.forEach(row => {
        const colorCell = row.querySelector('td:first-child .badge');
        if (colorCell) {
            const color = colorCell.textContent.trim();
            if (color && !existingColorNames.includes(color)) {
                existingColorNames.push(color);
            }
        }
    });
    
    // Renk input event listeners
    $('#colorInput').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const colorName = $(this).val().trim();
            if (colorName && !colorTags.includes(colorName)) {
                addColorTag(colorName);
                $(this).val('');
            }
        }
    });
    
    // Renk tag ekleme
    function addColorTag(colorName) {
        // Eğer bu renk zaten varsa (yeni veya mevcut), ekleme
        if (colorTags.includes(colorName) || existingColorNames.includes(colorName)) {
            alert('Bu renk zaten ekli!');
            return;
        }
        
        colorTags.push(colorName);
        const tagHtml = `
            <span class="badge bg-primary d-inline-flex align-items-center gap-1" data-color="${colorName}">
                ${colorName}
                <button type="button" class="btn-close btn-close-white" style="font-size: 0.7em;" onclick="removeColorTag('${colorName}')"></button>
            </span>
        `;
        $('#colorTags').append(tagHtml);
        
        // Modal aç
        openColorStockModal(colorName);
    }
    
    // Renk tag silme
    window.removeColorTag = function(colorName) {
        colorTags = colorTags.filter(color => color !== colorName);
        delete colorStocks[colorName]; // Stok bilgilerini de sil
        $(`[data-color="${colorName}"]`).remove();
        updateColorInputs();
    }
    
    // Renk stok modalını aç
    function openColorStockModal(colorName) {
        $('#selectedColorName').text(colorName);
        $('#colorStockQuantity').val('');
        $('#colorCriticalStock').val('');
        currentColorIndex = colorTags.indexOf(colorName);
        
        const modal = new bootstrap.Modal(document.getElementById('colorStockModal'));
        modal.show();
    }
    
    // Renk stok kaydet
    $('#saveColorStock').on('click', function() {
        const colorName = $('#selectedColorName').text();
        const stockQuantity = $('#colorStockQuantity').val();
        const criticalStock = $('#colorCriticalStock').val();
        
        if (!stockQuantity) {
            alert('Stok miktarı gereklidir!');
            return;
        }
        
        // Stok bilgilerini sakla
        colorStocks[colorName] = {
            stock: stockQuantity,
            critical: criticalStock || '0'
        };
        
        // Tag'i güncelle
        const tagElement = $(`[data-color="${colorName}"]`);
        tagElement.html(`
            ${colorName} (${stockQuantity})
            <button type="button" class="btn-close btn-close-white" style="font-size: 0.7em;" onclick="removeColorTag('${colorName}')"></button>
        `);
        
        // Modal'ı kapat
        const modal = bootstrap.Modal.getInstance(document.getElementById('colorStockModal'));
        if (modal) modal.hide();
        
        // Input'ları güncelle
        updateColorInputs();
    });
    
    // Hidden input'ları güncelle
    function updateColorInputs() {
        // Mevcut hidden input'ları kaldır (sadece yeni eklenenler)
        $('input[name^="color_variants"][name*="[color]"]').each(function() {
            const name = $(this).attr('name');
            if (name && name.includes('new_')) {
                $(this).remove();
                // İlgili stock_quantity ve critical_stock input'larını da kaldır
                const index = name.match(/\[new_(\d+)\]/);
                if (index) {
                    $(`input[name="color_variants[new_${index[1]}][stock_quantity]"]`).remove();
                    $(`input[name="color_variants[new_${index[1]}][critical_stock]"]`).remove();
                }
            }
        });
        
        // Yeni input'ları ekle
        colorTags.forEach((colorName, index) => {
            const stockData = colorStocks[colorName] || { stock: '0', critical: '0' };
            const stockQuantity = stockData.stock || '0';
            const criticalStock = stockData.critical || '0';
            
            // Hidden input'lar ekle
            $('<input>').attr({
                type: 'hidden',
                name: `color_variants[new_${index}][color]`,
                value: colorName
            }).appendTo('#colorTagsContainer');
            
            $('<input>').attr({
                type: 'hidden',
                name: `color_variants[new_${index}][stock_quantity]`,
                value: stockQuantity
            }).appendTo('#colorTagsContainer');
            
            $('<input>').attr({
                type: 'hidden',
                name: `color_variants[new_${index}][critical_stock]`,
                value: criticalStock || '0'
            }).appendTo('#colorTagsContainer');
        });
    }
    
    // Form submit öncesi hidden inputs'ları güncelle
    $('form').on('submit', function(e) {
        updateColorInputs();
    });

    // Quick stock functionality
    $('#qsSaveBtn').on('click', function(){
        const addStock = parseInt($('#qsAddStock').val() || '0', 10);
        
        if(addStock <= 0) {
            alert('Lütfen eklenecek stok miktarını giriniz.');
            return;
        }
        
        fetch('{{ url('/products/series/' . $series->id . '/quick-stock') }}', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({ add_stock: addStock })
        })
        .then(r => r.json())
        .then(resp => {
            if (resp.success) {
                alert('Stok güncellendi! Yeni stok: ' + resp.data.stock_quantity + ' adet');
                location.reload();
            } else {
                alert('Güncelleme başarısız: ' + (resp.message || 'Bilinmeyen hata'));
            }
        })
        .catch(err => {
            alert('Hata oluştu: ' + err.message);
        });
    });

    // Series size selection functionality
    $('#seriesSizeSelect').on('change', function() {
        const customInput = $('#customSeriesSize');
        if ($(this).val() === 'custom') {
            customInput.prop('disabled', false).prop('required', true);
        } else {
            customInput.prop('disabled', true).prop('required', false).val('');
        }
    });

    // Add series size form submission
    $('#addSeriesSizeForm').on('submit', function(e) {
        e.preventDefault();
        
        const seriesSizeSelect = $('#seriesSizeSelect');
        const customSeriesSize = $('#customSeriesSize');
        
        let seriesSize;
        if (seriesSizeSelect.val() === 'custom') {
            seriesSize = parseInt(customSeriesSize.val());
        } else {
            seriesSize = parseInt(seriesSizeSelect.val());
        }
        
        if (!seriesSize || seriesSize < 2) {
            alert('Lütfen geçerli bir seri boyutu seçin.');
            return;
        }
        
        if (colorTags.length === 0) {
            alert('Lütfen en az bir renk ekleyin.');
            return;
        }
        
        // Prepare form data
        const formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('series_size', seriesSize);
        
        // Add color variants
        colorTags.forEach((color, index) => {
            const stockData = colorStocks[color] || { stock: '0', critical: '0' };
            formData.append(`color_variants[${index}][color]`, color);
            formData.append(`color_variants[${index}][stock_quantity]`, stockData.stock || '0');
            formData.append(`color_variants[${index}][critical_stock]`, stockData.critical || '0');
        });
        
        // Submit to add series size endpoint
        fetch('{{ route("products.series.add-size", $series) }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Seri boyutu başarıyla eklendi!');
                location.reload();
            } else {
                alert('Hata: ' + (data.message || 'Bilinmeyen hata'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu.');
        });
    });
});
</script>
@endpush

@endsection
