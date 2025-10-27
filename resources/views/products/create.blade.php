@extends('layout.layout')

@section('title', 'Yeni Ürün')
@section('subTitle', 'Ürün Ekle')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Yeni Ürün Ekle</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Ürün Bilgileri -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-semibold mb-3">Ürün Bilgileri</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ürün Adı <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" name="name" class="form-control" placeholder="Ürün Adı" value="{{ old('name') }}" required>
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:box-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            @error('name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ürün Kodu</label>
                            <div class="position-relative">
                                <input type="text" name="sku" id="productSku" class="form-control" placeholder="Ürün Kodu" value="{{ old('sku') }}">
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:tag-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            <small class="text-secondary-light">Otomatik oluşturulur, isterseniz değiştirebilirsiniz</small>
                            @error('sku')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label">Birim</label>
                            <div class="position-relative">
                                <select name="unit" class="form-control">
                                    <option value="">Seçiniz</option>
                                    <option value="Adet" {{ old('unit') == 'Adet' ? 'selected' : '' }}>Adet</option>
                                    <option value="Metre" {{ old('unit') == 'Metre' ? 'selected' : '' }}>Metre</option>
                                </select>
                            </div>
                            @error('unit')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Fiyat Bilgileri -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-semibold mb-3">Fiyat Bilgileri</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Alış Fiyatı</label>
                            <div class="position-relative">
                                <input type="number" name="cost" class="form-control" placeholder="Alış Fiyatı" value="{{ old('cost') }}" step="0.01" min="0" max="999999.99">
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:dollar-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            <small class="text-secondary-light">KDV Hariç</small>
                            @error('cost')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Döviz</label>
                            <select name="cost_currency" class="form-control">
                                <option value="TRY" {{ old('cost_currency') == 'TRY' ? 'selected' : '' }}>TRY</option>
                                <option value="USD" {{ old('cost_currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ old('cost_currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Satış Fiyatı</label>
                            <div class="position-relative">
                                <input type="number" name="price" class="form-control" placeholder="Satış Fiyatı" value="{{ old('price') }}" step="0.01" min="0" max="999999.99">
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:dollar-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            <small class="text-secondary-light">KDV Hariç</small>
                            @error('price')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Döviz</label>
                            <select name="price_currency" class="form-control">
                                <option value="TRY" {{ old('price_currency', 'TRY') == 'TRY' ? 'selected' : '' }}>TRY</option>
                                <option value="USD" {{ old('price_currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ old('price_currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tanımlar -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-semibold mb-3">Tanımlar</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <select name="category" class="form-control" required>
                                    <option value="">Seçiniz</option>
                                    @php
                                    $allCategories = ['Gömlek','Ceket','Takım Elbise','Pantalon'];
                                        $options = isset($allowedCategories) && is_array($allowedCategories) && count($allowedCategories) > 0 ? $allowedCategories : $allCategories;
                                    @endphp
                                    @foreach($options as $cat)
                                        <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('category')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Marka <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control @error('brand') is-invalid @enderror" 
                                       name="brand" id="brandInput" value="{{ old('brand') }}" 
                                       placeholder="Marka yazın veya seçin..." autocomplete="off" required>
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:star-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                                <div id="brandDropdown" class="dropdown-menu w-100" style="display: none; max-height: 200px; overflow-y: auto;">
                                    <!-- Brand suggestions will be populated here -->
                                </div>
                            </div>
                            @error('brand')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label">Beden</label>
                            <div class="position-relative">
                                <select name="size" class="form-control">
                                    <option value="">Seçiniz</option>
                                    <option value="XS" {{ old('size') == 'XS' ? 'selected' : '' }}>XS</option>
                                    <option value="S" {{ old('size') == 'S' ? 'selected' : '' }}>S</option>
                                    <option value="M" {{ old('size') == 'M' ? 'selected' : '' }}>M</option>
                                    <option value="L" {{ old('size') == 'L' ? 'selected' : '' }}>L</option>
                                    <option value="XL" {{ old('size') == 'XL' ? 'selected' : '' }}>XL</option>
                                    <option value="XXL" {{ old('size') == 'XXL' ? 'selected' : '' }}>XXL</option>
                                    <option value="XXXL" {{ old('size') == 'XXXL' ? 'selected' : '' }}>XXXL</option>
                                    <option value="28" {{ old('size') == '28' ? 'selected' : '' }}>28</option>
                                    <option value="30" {{ old('size') == '30' ? 'selected' : '' }}>30</option>
                                    <option value="32" {{ old('size') == '32' ? 'selected' : '' }}>32</option>
                                    <option value="34" {{ old('size') == '34' ? 'selected' : '' }}>34</option>
                                    <option value="36" {{ old('size') == '36' ? 'selected' : '' }}>36</option>
                                    <option value="38" {{ old('size') == '38' ? 'selected' : '' }}>38</option>
                                    <option value="40" {{ old('size') == '40' ? 'selected' : '' }}>40</option>
                                    <option value="42" {{ old('size') == '42' ? 'selected' : '' }}>42</option>
                                    <option value="44" {{ old('size') == '44' ? 'selected' : '' }}>44</option>
                                    <option value="46" {{ old('size') == '46' ? 'selected' : '' }}>46</option>
                                    <option value="48" {{ old('size') == '48' ? 'selected' : '' }}>48</option>
                                    <option value="50" {{ old('size') == '50' ? 'selected' : '' }}>50</option>
                                    <option value="52" {{ old('size') == '52' ? 'selected' : '' }}>52</option>
                                </select>
                            </div>
                            @error('size')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label d-flex align-items-center justify-content-between">
                                <span>Renkler</span>
                                <small class="text-muted">Enter tuşu ile ekleyin</small>
                            </label>
                            <div class="position-relative">
                                <div id="colorTagsContainer" class="border rounded p-2 min-height-50" style="min-height: 50px; background: #f8f9fa;">
                                    <div id="colorTags" class="d-flex flex-wrap gap-2 mb-2"></div>
                                    <input type="text" id="colorInput" class="form-control border-0 bg-transparent" placeholder="Renk yazın ve Enter'a basın..." autocomplete="off" style="box-shadow: none;">
                                </div>
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:palette-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            <small class="text-secondary-light">Her renk için ayrı stok miktarı belirleyebilirsiniz.</small>
                            @error('color')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            @error('colors_input')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label">Barkod</label>
                            <div class="position-relative">
                                <input type="text" name="barcode" id="productBarcode" class="form-control" placeholder="Kendi barkodunuzu girin" value="{{ old('barcode') }}">
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:qr-code-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            <small class="text-secondary-light">Kendi barkodunuzu girin veya otomatik oluşturulsun</small>
                            @error('barcode')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Ayarlar -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-semibold mb-3">Ayarlar</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Durum</label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label">Aktif</label>
                            </div>
                            @error('is_active')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Açıklama -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label">Açıklama</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Ürün açıklaması">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Görsel -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label">Ürün Görseli</label>
                            <div class="position-relative">
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <small class="text-secondary-light">JPEG, PNG, JPG, GIF, WebP formatları desteklenir. Maksimum 2MB.</small>
                            </div>
                            @error('image')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">İptal</a>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
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
                    <small class="text-muted">Bu renk için stok miktarı</small>
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

@push('scripts')
<script>
$(document).ready(function() {
    // Otomatik SKU ve Barkod oluştur
    generateProductCodes();
    
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
    
    // Otomatik SKU ve Barkod oluşturma fonksiyonu
    function generateProductCodes() {
        // Eğer SKU boşsa otomatik oluştur
        if (!$('#productSku').val()) {
            const timestamp = Date.now().toString().slice(-6); // Son 6 hane
            const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            const sku = 'PRD' + timestamp + randomNum;
            $('#productSku').val(sku);
        }
        
        // Barkod otomatik oluştur (sadece boşsa)
        if (!$('#productBarcode').val()) {
            const timestamp = Date.now().toString().slice(-4); // Son 4 hane
            const barcode = 'P' + timestamp;
            $('#productBarcode').val(barcode);
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

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#brandInput, #brandDropdown').length) {
            $('#brandDropdown').hide();
        }
    });

    function searchBrands(query) {
        // Mevcut markalar
        const existingBrands = ['Ronex', 'Diğer', 'Nike', 'Adidas', 'Puma', 'Lacoste', 'Tommy Hilfiger', 'Calvin Klein'];
        
        const filtered = existingBrands.filter(brand => 
            brand.toLowerCase().includes(query.toLowerCase())
        );
        
        let html = '';
        if (filtered.length > 0) {
            filtered.forEach(function(brand) {
                html += `
                    <div class="dropdown-item brand-option" data-brand="${brand}" style="cursor: pointer;">
                        <i class="ri-star-line me-2"></i>${brand}
                    </div>
                `;
            });
        } else {
            html = '<div class="dropdown-item text-muted">Marka bulunamadı</div>';
        }
        
        $('#brandDropdown').html(html).show();
    }

    // Handle brand selection
    $(document).on('click', '.brand-option', function() {
        const brand = $(this).data('brand');
        $('#brandInput').val(brand);
        $('#brandDropdown').hide();
    });
});
</script>
@endpush

@endsection
