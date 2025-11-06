@extends('layout.layout')

@section('title', 'Seri Ürün Ekle')
@section('subTitle', 'Yeni Seri Ürün Oluştur')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Seri Ürün Bilgileri</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('products.series.store') }}" method="POST" enctype="multipart/form-data" id="seriesForm">
                    @csrf
                    @if(isset($parentSeries) && $parentSeries)
                        <input type="hidden" name="parent_series_id" value="{{ $parentSeries->id }}">
                    @endif
                    
                    <div class="row g-3">
                        <!-- Temel Bilgiler -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3">Temel Bilgiler</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Seri Adı *</label>
                            <input type="text" class="form-control radius-8 @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name', ($parentSeries->name ?? '')) }}" placeholder="Seri adını girin" required {{ isset($parentSeries) ? 'readonly' : '' }}>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(isset($parentSeries))
                                <small class="text-muted">Bu alan üst seriden devralındı ve değiştirilemez.</small>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">SKU</label>
                            <input type="text" class="form-control radius-8 @error('sku') is-invalid @enderror" 
                                   name="sku" id="seriesSku" value="{{ old('sku', ($parentSeries->sku ?? '')) }}" placeholder="Seri SKU'su" {{ isset($parentSeries) ? 'readonly' : '' }}>
                            <small class="text-secondary-light">Otomatik oluşturulur, isterseniz değiştirebilirsiniz</small>
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Barkod</label>
                            <input type="text" class="form-control radius-8 @error('barcode') is-invalid @enderror" 
                                   name="barcode" id="seriesBarcode" value="{{ old('barcode', ($parentSeries->barcode ?? '')) }}" placeholder="Kendi seri barkodunuzu girin" {{ isset($parentSeries) ? 'readonly' : '' }}>
                            <small class="text-secondary-light">Kendi barkodunuzu girin veya otomatik oluşturulsun</small>
                            @error('barcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Açıklama</label>
                            <textarea class="form-control radius-8 @error('description') is-invalid @enderror" 
                                      name="description" rows="3" placeholder="Seri açıklaması">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kategori ve Marka -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Kategori</label>
                            <select class="form-control radius-8 @error('category') is-invalid @enderror" 
                                    name="category" id="category" required {{ isset($parentSeries) ? 'disabled' : '' }}>
                                <option value="">Kategori Seçin</option>
                                @foreach($allowedCategories as $cat)
                                    <option value="{{ $cat }}" {{ old('category', ($parentSeries->category ?? '')) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                            @if(isset($parentSeries))
                                <input type="hidden" name="category" value="{{ $parentSeries->category }}">
                            @endif
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Marka</label>
                            <div class="position-relative">
                                <input type="text" class="form-control radius-8 @error('brand') is-invalid @enderror" 
                                       name="brand" id="brandInput" value="{{ old('brand', ($parentSeries->brand ?? '')) }}" 
                                       placeholder="Marka yazın veya seçin..." autocomplete="off" {{ isset($parentSeries) ? 'readonly' : '' }}>
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:star-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                                <div id="brandDropdown" class="dropdown-menu w-100" style="display: none; max-height: 200px; overflow-y: auto;">
                                    <!-- Brand suggestions will be populated here -->
                                </div>
                            </div>
                            @error('brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Fiyat Bilgileri -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3 mt-4">Fiyat Bilgileri</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Maliyet</label>
                            <input type="number" class="form-control radius-8 @error('cost') is-invalid @enderror" 
                                   name="cost" value="{{ old('cost') }}" step="0.01" min="0" max="999999.99" placeholder="0.00">
                            @error('cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Döviz</label>
                            <select name="cost_currency" class="form-control radius-8">
                                <option value="TRY" {{ old('cost_currency') == 'TRY' ? 'selected' : '' }}>TRY</option>
                                <option value="USD" {{ old('cost_currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ old('cost_currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Satış Fiyatı</label>
                            <input type="number" class="form-control radius-8 @error('price') is-invalid @enderror" 
                                   name="price" value="{{ old('price') }}" step="0.01" min="0" max="999999.99" placeholder="0.00">
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Döviz</label>
                            <select name="price_currency" class="form-control radius-8">
                                <option value="TRY" {{ old('price_currency', 'TRY') == 'TRY' ? 'selected' : '' }}>TRY</option>
                                <option value="USD" {{ old('price_currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ old('price_currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                            </select>
                        </div>

                        <!-- Renk Seçimi -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3 mt-4">Renkler</h6>
                            <div class="position-relative">
                                <div id="colorTagsContainer" class="border rounded p-2 min-height-50" style="min-height: 50px; background: #f8f9fa;">
                                    <div id="colorTags" class="d-flex flex-wrap gap-2 mb-2"></div>
                                    <input type="text" id="colorInput" class="form-control border-0 bg-transparent" placeholder="Renk yazın ve Enter'a basın..." autocomplete="off" style="box-shadow: none;">
                                </div>
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:palette-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            <small class="text-muted">Her renk için ayrı stok miktarı belirleyebilirsiniz.</small>
                        </div>

                        <!-- Seri İçeriği -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3 mt-4">Seri İçeriği *</h6>
                            <div class="alert alert-info mb-3">
                                <i class="ri-information-line me-2"></i>
                                <strong>Sabit Seri:</strong> Önceden tanımlı bedenler (XS, S, M, L, XL, XXL, XXXL) otomatik gelir, her bedenden 1'er adet olur.<br>
                                <strong>Özel Seri:</strong> İstediğiniz bedenleri seçin ve miktarlarını ayarlayın.
                            </div>
                            <div id="series-content">
                                <div class="alert alert-info">
                                    <i class="ri-information-line me-2"></i>
                                    Önce seri tipini seçin, bedenler otomatik olarak eklenecektir.
                                </div>
                            </div>
                        </div>

                        <!-- Görsel -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3 mt-4">Görsel</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Ürün Görseli</label>
                            <input type="file" class="form-control radius-8 @error('image') is-invalid @enderror" 
                                   name="image" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Durum -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Durum</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label">Aktif</label>
                            </div>
                        </div>
                    </div>


                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('products.series.index') }}" class="btn btn-secondary">İptal</a>
                        <button type="submit" class="btn btn-primary" id="submit-btn">Seri Oluştur</button>
                    </div>
                </form>
            </div>
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

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Otomatik SKU ve Barkod oluştur
    generateSeriesCodes();
    
    // Beden seçenekleri
    const sizeOptions = [
        'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL',
        '28', '30', '32', '34', '36', '38', '40', '42', '44', '46', '48', '50'
    ];

    // Element'leri bul
    const seriesContent = document.getElementById('series-content');

    // Element'lerin varlığını kontrol et
    if (!seriesContent) {
        console.error('Required elements not found:', {
            seriesContent: !!seriesContent
        });
        return;
    }

    // Sayfa yüklendiğinde bedenleri göster
    loadCustomSeries();

    function loadCustomSeries() {
        // Giyim bedenleri (XS'den 8XL'a kadar)
        const clothingSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', '4XL', '5XL', '6XL', '7XL', '8XL'];
        
        // Pantolon bedenleri (28'den 50'ye kadar)
        const pantsSizes = [];
        for (let i = 28; i <= 50; i += 2) {
            pantsSizes.push(i.toString());
        }
        
        let html = '<div class="row">';
        html += '<div class="col-md-6">';
        html += '<div class="card border-0 shadow-sm">';
        html += '<div class="card-header bg-primary text-white">';
        html += '<h6 class="mb-0"><i class="ri-shirt-line me-2"></i>Giyim Bedenleri</h6>';
        html += '</div>';
        html += '<div class="card-body">';
        html += '<div class="row g-2">';
        
        clothingSizes.forEach(function(size) {
                    html += `
                <div class="col-4 col-md-3">
                    <div class="form-check form-check-custom">
                        <input class="form-check-input size-checkbox" type="checkbox" name="selected_sizes[]" value="${size}" id="size_${size}">
                        <label class="form-check-label fw-semibold" for="size_${size}">${size}</label>
                    </div>
                </div>
                    `;
                });
                
        html += '</div>';
        html += '</div>';
        html += '</div>';
                html += '</div>';
                
        html += '<div class="col-md-6">';
        html += '<div class="card border-0 shadow-sm">';
        html += '<div class="card-header bg-info text-white">';
        html += '<h6 class="mb-0"><i class="ri-pants-line me-2"></i>Pantolon Bedenleri</h6>';
        html += '</div>';
        html += '<div class="card-body">';
        html += '<div class="row g-2">';
        
        pantsSizes.forEach(function(size) {
        html += `
                <div class="col-4 col-md-3">
                    <div class="form-check form-check-custom">
                        <input class="form-check-input size-checkbox" type="checkbox" name="selected_sizes[]" value="${size}" id="size_${size}">
                        <label class="form-check-label fw-semibold" for="size_${size}">${size}</label>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        
        html += '<div class="mt-4">';
        html += '<div class="card border-0 shadow-sm">';
        html += '<div class="card-header bg-success text-white">';
        html += '<h6 class="mb-0"><i class="ri-list-check me-2"></i>Seçilen Bedenler ve Miktarlar</h6>';
        html += '</div>';
        html += '<div class="card-body">';
        html += '<div id="selectedSizesContainer">';
        html += '<div class="alert alert-info text-center">';
        html += '<i class="ri-information-line me-2"></i>';
        html += 'Lütfen yukarıdan bedenleri seçin, miktarları ayarlayın.';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        
        seriesContent.innerHTML = html;
        
        // Beden seçimi değiştiğinde miktar alanlarını güncelle
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('size-checkbox')) {
                updateSelectedSizes();
            }
        });
    }

    function updateSelectedSizes() {
        const selectedCheckboxes = document.querySelectorAll('.size-checkbox:checked');
        const container = document.getElementById('selectedSizesContainer');
        
        if (selectedCheckboxes.length === 0) {
            container.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class="ri-information-line me-2"></i>
                    Lütfen yukarıdan bedenleri seçin, miktarları ayarlayın.
                </div>
            `;
            return;
        }
        
        let html = '<div class="table-responsive">';
        html += '<table class="table table-hover table-bordered">';
        html += '<thead class="table-primary">';
        html += '<tr><th class="text-center">Beden</th><th class="text-center">Seri Başına Adet</th></tr>';
        html += '</thead><tbody>';
        
        selectedCheckboxes.forEach(function(checkbox, index) {
            const size = checkbox.value;
            html += `
                <tr>
                    <td class="text-center">
                        <span class="badge bg-primary fs-6">${size}</span>
                        <input type="hidden" name="sizes[]" value="${size}">
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="number" class="form-control text-center" name="quantities[]" value="1" min="1" required>
                            <span class="input-group-text">adet</span>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        html += '<div class="alert alert-success text-center">';
        html += '<i class="ri-check-line me-2"></i>';
        html += '<strong>Seçilen Bedenler:</strong> ' + selectedCheckboxes.length + ' adet beden seçildi. Miktarları ayarlayabilirsiniz.';
        html += '</div>';
        
        container.innerHTML = html;
    }

    // Eski beden ekleme kodu kaldırıldı - artık checkbox sistemi kullanılıyor

    // Eski fonksiyonlar kaldırıldı - artık checkbox sistemi kullanılıyor


    // Form submit validation
    document.getElementById('submit-btn').addEventListener('click', function(e) {
        const seriesType = seriesTypeSelect.value;
        
        // Özel seri için seçilen beden kontrolü yap
        if (seriesType === 'custom') {
            const selectedCheckboxes = document.querySelectorAll('.size-checkbox:checked');
            
            if (selectedCheckboxes.length === 0) {
                e.preventDefault();
                alert('Özel seri için en az 1 beden seçmelisiniz.');
                return false;
            }
        }
    });

    // Miktar kontrolü artık updateSelectedSizes fonksiyonunda yapılıyor

    // No color JavaScript needed - simple text input with comma separation
    
    // Otomatik SKU ve Barkod oluşturma fonksiyonu
    function generateSeriesCodes() {
        // Eğer SKU boşsa otomatik oluştur
        if (!$('#seriesSku').val()) {
            const timestamp = Date.now().toString().slice(-6); // Son 6 hane
            const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            const sku = 'SER' + timestamp + randomNum;
            $('#seriesSku').val(sku);
        }
        
        // Barkod otomatik oluştur (sadece boşsa)
        if (!$('#seriesBarcode').val()) {
        const timestamp = Date.now().toString().slice(-4); // Son 4 hane
        const barcode = 'S' + timestamp;
        $('#seriesBarcode').val(barcode);
        }
    }

    // Marka autocomplete
    $('#brandInput').on('input', function() {
        const query = $(this).val();
        if (query.length >= 2) {
            searchBrands(query);
        } else {
            $('#brandDropdown').hide();
        }
    });

    // Hide dropdowns when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#brandInput, #brandDropdown').length) {
            $('#brandDropdown').hide();
        }
    });

    async function searchBrands(query) {
        try {
            const url = `{{ route('products.brands.search') }}?q=${encodeURIComponent(query)}`;
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            const data = await res.json();
            let html = '';
            if (Array.isArray(data) && data.length) {
                data.forEach(function(item){
                    html += `
                        <div class="dropdown-item brand-option" data-brand="${item.name}" style="cursor: pointer;">
                            <i class=\"ri-star-line me-2\"></i>${item.name}
                        </div>
                    `;
                });
            } else {
                html = '<div class="dropdown-item text-muted">Marka bulunamadı</div>';
            }
            $('#brandDropdown').html(html).show();
        } catch (e) {
            $('#brandDropdown').hide();
        }
    }

    // Handle brand selection
    $(document).on('click', '.brand-option', function() {
        const brand = $(this).data('brand');
        $('#brandInput').val(brand);
        $('#brandDropdown').hide();
    });

    // Renk sistemi
    let colorTags = [];
    let currentColorIndex = -1;
    let colorStocks = {}; // Renk stok bilgilerini sakla
    
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
        modal.hide();
        
        // Input'ları güncelle
        updateColorInputs();
    });
    
    // Hidden input'ları güncelle
    function updateColorInputs() {
        // Mevcut hidden input'ları kaldır
        $('input[name^="color_variants"]').remove();
        
        // Yeni input'ları ekle
        colorTags.forEach((colorName, index) => {
            const stockData = colorStocks[colorName] || { stock: '0', critical: '0' };
            const stockQuantity = stockData.stock;
            const criticalStock = stockData.critical;
            
            // Hidden input'lar ekle
            $('<input>').attr({
                type: 'hidden',
                name: `color_variants[${index}][color]`,
                value: colorName
            }).appendTo('#colorTagsContainer');
            
            $('<input>').attr({
                type: 'hidden',
                name: `color_variants[${index}][stock_quantity]`,
                value: stockQuantity
            }).appendTo('#colorTagsContainer');
            
            $('<input>').attr({
                type: 'hidden',
                name: `color_variants[${index}][critical_stock]`,
                value: criticalStock || '0'
            }).appendTo('#colorTagsContainer');
        });
    }
});
</script>
@endpush
