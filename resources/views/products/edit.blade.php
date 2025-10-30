@extends('layout.layout')

@section('title', 'Ürün Düzenle')
@section('subTitle', 'Ürün Düzenle')

@push('styles')
<style>
    .product-image-preview {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .product-image-preview:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    /* Kritik stok animasyonu */
    .blink {
        animation: blink 1.5s infinite;
    }
    
    @keyframes blink {
        0%, 50% { opacity: 1; }
        51%, 100% { opacity: 0.3; }
    }
    
    /* Kritik stok input focus */
    .border-danger:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Ürün Düzenle</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="productEditForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Ürün Bilgileri -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-semibold mb-3">Ürün Bilgileri</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ürün Adı <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" name="name" class="form-control" placeholder="Ürün Adı" value="{{ $product->name }}" required>
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
                            <input type="text" name="sku" class="form-control" value="{{ $product->sku }}">
                            @error('sku')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label">Birim</label>
                            <select name="unit" class="form-control">
                                <option value="">Seçiniz</option>
                                <option value="Adet" {{ $product->unit == 'Adet' ? 'selected' : '' }}>Adet</option>
                                <option value="Metre" {{ $product->unit == 'Metre' ? 'selected' : '' }}>Metre</option>
                            </select>
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
                            <input type="number" name="cost" class="form-control" value="{{ $product->cost ?? '' }}" step="0.01" min="0">
                            @error('cost')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Döviz</label>
                            <select name="cost_currency" class="form-control">
                                <option value="TRY" {{ ($product->cost_currency ?? 'TRY') == 'TRY' ? 'selected' : '' }}>TRY</option>
                                <option value="USD" {{ ($product->cost_currency ?? 'TRY') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ ($product->cost_currency ?? 'TRY') == 'EUR' ? 'selected' : '' }}>EUR</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Satış Fiyatı</label>
                            <input type="number" name="price" class="form-control" value="{{ $product->price }}" step="0.01" min="0">
                            @error('price')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Döviz</label>
                            <select name="price_currency" class="form-control">
                                <option value="TRY" {{ ($product->price_currency ?? 'TRY') == 'TRY' ? 'selected' : '' }}>TRY</option>
                                <option value="USD" {{ ($product->price_currency ?? 'TRY') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ ($product->price_currency ?? 'TRY') == 'EUR' ? 'selected' : '' }}>EUR</option>
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
                                    @foreach(($allowedCategories ?? []) as $cat)
                                        <option value="{{ $cat }}" {{ $product->category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
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
                                <select name="brand" class="form-control" required>
                                    <option value="">Seçiniz</option>
                                    <option value="Ronex" {{ $product->brand == 'Ronex' ? 'selected' : '' }}>Ronex</option>
                                    <option value="Diğer" {{ $product->brand == 'Diğer' ? 'selected' : '' }}>Diğer</option>
                                </select>
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
                                    <option value="XS" {{ $product->size == 'XS' ? 'selected' : '' }}>XS</option>
                                    <option value="S" {{ $product->size == 'S' ? 'selected' : '' }}>S</option>
                                    <option value="M" {{ $product->size == 'M' ? 'selected' : '' }}>M</option>
                                    <option value="L" {{ $product->size == 'L' ? 'selected' : '' }}>L</option>
                                    <option value="XL" {{ $product->size == 'XL' ? 'selected' : '' }}>XL</option>
                                    <option value="XXL" {{ $product->size == 'XXL' ? 'selected' : '' }}>XXL</option>
                                    <option value="XXXL" {{ $product->size == 'XXXL' ? 'selected' : '' }}>XXXL</option>
                                    <option value="28" {{ $product->size == '28' ? 'selected' : '' }}>28</option>
                                    <option value="30" {{ $product->size == '30' ? 'selected' : '' }}>30</option>
                                    <option value="32" {{ $product->size == '32' ? 'selected' : '' }}>32</option>
                                    <option value="34" {{ $product->size == '34' ? 'selected' : '' }}>34</option>
                                    <option value="36" {{ $product->size == '36' ? 'selected' : '' }}>36</option>
                                    <option value="38" {{ $product->size == '38' ? 'selected' : '' }}>38</option>
                                    <option value="40" {{ $product->size == '40' ? 'selected' : '' }}>40</option>
                                    <option value="42" {{ $product->size == '42' ? 'selected' : '' }}>42</option>
                                    <option value="44" {{ $product->size == '44' ? 'selected' : '' }}>44</option>
                                    <option value="46" {{ $product->size == '46' ? 'selected' : '' }}>46</option>
                                    <option value="48" {{ $product->size == '48' ? 'selected' : '' }}>48</option>
                                    <option value="50" {{ $product->size == '50' ? 'selected' : '' }}>50</option>
                                    <option value="52" {{ $product->size == '52' ? 'selected' : '' }}>52</option>
                                </select>
                            </div>
                            @error('size')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label">Renk</label>
                            <div class="position-relative">
                                <input type="text" id="colorSearch" name="color" class="form-control" placeholder="Renk ara..." value="{{ $product->color }}" autocomplete="off">
                                <input type="hidden" id="selectedColor" name="color" value="{{ $product->color }}">
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:palette-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                                <div id="colorDropdown" class="dropdown-menu w-100" style="display: none; max-height: 200px; overflow-y: auto; position: absolute; top: 100%; left: 0; z-index: 1050; background: white; border: 1px solid #dee2e6; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);">
                                    <!-- Color options will be populated here -->
                                </div>
                            </div>
                            @error('color')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label">Barkod</label>
                            <input type="text" name="barcode" class="form-control" placeholder="Barkod" value="{{ $product->barcode }}">
                            @error('barcode')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label">Açıklama</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Ürün açıklaması...">{{ $product->description }}</textarea>
                            @error('description')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Stok Yönetimi -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-semibold mb-3 d-flex align-items-center">
                                <iconify-icon icon="solar:box-minimalistic-outline" class="text-primary me-2"></iconify-icon>
                                Stok Yönetimi
                                @if($product->colorVariants && $product->colorVariants->count() > 0)
                                    @php
                                        $hasCritical = $product->colorVariants->filter(function($v){ return $v->critical_stock > 0 && $v->stock_quantity <= $v->critical_stock; })->count() > 0;
                                    @endphp
                                    @if($hasCritical)
                                        <span class="badge bg-danger ms-2 blink">KRİTİK STOK!</span>
                                    @endif
                                @else
                                    @if($product->critical_stock > 0 && $product->initial_stock <= $product->critical_stock)
                                        <span class="badge bg-danger ms-2 blink">KRİTİK STOK!</span>
                                    @endif
                                @endif
                            </h6>
                        </div>

                        @if($product->colorVariants && $product->colorVariants->count() > 0)
                            <!-- Multi-Color Product Stock Management -->
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
                                                        <th>Mevcut Stok</th>
                                                        <th>Kritik Stok</th>
                                                        <th>Durum</th>
                                                        <th>İşlemler</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($product->colorVariants as $index => $variant)
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
                                                        <th class="fw-bold">{{ $product->colorVariants->sum('stock_quantity') }}</th>
                                                        <th class="fw-bold">{{ $product->colorVariants->sum('critical_stock') }}</th>
                                                        <th>
                                                            @php
                                                                $hasProductCritical = $product->colorVariants->filter(function($v){ return $v->critical_stock > 0 && $v->stock_quantity <= $v->critical_stock; })->count() > 0;
                                                            @endphp
                                                            @if($hasProductCritical)
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
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Single Product Stock Management -->
                            <div class="col-md-6">
                                <label class="form-label">Mevcut Stok Miktarı</label>
                                <input type="number" name="initial_stock" class="form-control" placeholder="Mevcut Stok Miktarı" value="{{ $product->initial_stock }}" min="0">
                                @error('initial_stock')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kritik Stok Sınırı</label>
                                <input type="number" name="critical_stock" class="form-control" placeholder="Kritik Stok Sınırı" value="{{ $product->critical_stock }}" min="0">
                                @error('critical_stock')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="col-md-6 mt-3">
                            <label class="form-label">Ürün Durumu</label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }}>
                                <label class="form-check-label">Aktif</label>
                            </div>
                            @error('is_active')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                    <!-- Görsel -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label">Ürün Görseli</label>
                            @if($product->image)
                                <div class="mb-3">
                                    <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="img-thumbnail cursor-pointer product-image-preview" style="max-width: 200px; max-height: 200px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageModal">
                                    <div class="mt-2">
                                        <small class="text-muted">Mevcut görsel (büyütmek için tıklayın)</small>
                                    </div>
                                </div>
                            @endif
                            <div class="position-relative">
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <small class="text-secondary-light">JPEG, PNG, JPG, GIF, WebP formatları desteklenir. Maksimum 2MB. Yeni görsel seçerseniz mevcut görsel değiştirilir.</small>
                            </div>
                            @error('image')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">İptal</a>
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Turkish color names
    const colors = [
        'Beyaz', 'Siyah', 'Kırmızı', 'Mavi', 'Yeşil', 'Sarı', 'Turuncu', 'Mor', 'Pembe', 'Kahverengi',
        'Gri', 'Lacivert', 'Bordo', 'Turkuaz', 'Altın', 'Gümüş', 'Bej', 'Krem', 'Haki', 'Fuşya',
        'Lavanta', 'Mint', 'Koral', 'Turuncu', 'Koyu Mavi', 'Açık Mavi', 'Koyu Yeşil', 'Açık Yeşil',
        'Koyu Kırmızı', 'Açık Kırmızı', 'Koyu Sarı', 'Açık Sarı', 'Koyu Mor', 'Açık Mor', 'Koyu Pembe',
        'Açık Pembe', 'Koyu Kahverengi', 'Açık Kahverengi', 'Koyu Gri', 'Açık Gri', 'Çikolata',
        'Deniz Mavisi', 'Orman Yeşili', 'Kiraz Kırmızısı', 'Limon Sarısı', 'Menekşe', 'Şeftali',
        'Zeytin Yeşili', 'Bakır', 'Bronz', 'Platin', 'İnci', 'Koyu Lacivert', 'Açık Lacivert',
        'Koyu Bordo', 'Açık Bordo', 'Koyu Turkuaz', 'Açık Turkuaz', 'Koyu Altın', 'Açık Altın',
        'Koyu Gümüş', 'Açık Gümüş', 'Koyu Bej', 'Açık Bej', 'Koyu Krem', 'Açık Krem', 'Koyu Haki',
        'Açık Haki', 'Koyu Fuşya', 'Açık Fuşya', 'Koyu Lavanta', 'Açık Lavanta', 'Koyu Mint',
        'Açık Mint', 'Koyu Koral', 'Açık Koral', 'Neon Yeşil', 'Neon Sarı', 'Neon Pembe',
        'Neon Turuncu', 'Neon Mavi', 'Neon Kırmızı', 'Mat Siyah', 'Mat Beyaz', 'Mat Gri',
        'Mat Mavi', 'Mat Kırmızı', 'Mat Yeşil', 'Mat Sarı', 'Mat Mor', 'Mat Pembe', 'Mat Kahverengi'
    ];

    // Color search functionality
    $('#colorSearch').on('input', function() {
        const query = $(this).val().toLowerCase();
        if (query.length >= 1) {
            const filteredColors = colors.filter(color => 
                color.toLowerCase().includes(query)
            );
            showColorDropdown(filteredColors);
        } else {
            $('#colorDropdown').hide();
        }
    });

    // Show color dropdown
    function showColorDropdown(filteredColors) {
        let html = '';
        if (filteredColors.length > 0) {
            filteredColors.forEach(function(color) {
                html += `
                    <div class="dropdown-item color-option" data-color="${color}" style="cursor: pointer; padding: 8px 16px;">
                        <div class="fw-semibold">${color}</div>
                    </div>
                `;
            });
        } else {
            html = '<div class="dropdown-item text-secondary-light" style="padding: 8px 16px;">Renk bulunamadı</div>';
        }
        
        $('#colorDropdown').html(html).show();
        
        // Ensure dropdown is positioned correctly
        $('#colorDropdown').css({
            'position': 'absolute',
            'top': '100%',
            'left': '0',
            'right': '0',
            'transform': 'none',
            'margin-top': '0'
        });
    }

    // Color selection
    $(document).on('click', '.color-option', function() {
        const selectedColor = $(this).data('color');
        $('#colorSearch').val(selectedColor);
        $('#selectedColor').val(selectedColor);
        $('#colorDropdown').hide();
    });

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#colorSearch, #colorDropdown').length) {
            $('#colorDropdown').hide();
        }
    });

    // Show all colors on focus
    $('#colorSearch').on('focus', function() {
        if ($(this).val().length === 0) {
            showColorDropdown(colors);
        }
    });

    // Set initial color value if exists
    @if($product->color)
        $('#selectedColor').val('{{ $product->color }}');
    @endif

    // Color variant stock management
    function updateColorVariantStatus() {
        $('input[name*="[stock_quantity]"]').each(function() {
            const stockInput = $(this);
            const row = stockInput.closest('tr');
            const criticalInput = row.find('input[name*="[critical_stock]"]');
            const statusBadge = row.find('td:nth-child(4) .badge');
            
            const stock = parseInt(stockInput.val()) || 0;
            const critical = parseInt(criticalInput.val()) || 0;
            
            if (stock <= critical && critical > 0) {
                statusBadge.removeClass('bg-success').addClass('bg-danger').text('Kritik');
                row.removeClass('table-warning').addClass('table-warning');
            } else {
                statusBadge.removeClass('bg-danger').addClass('bg-success').text('Normal');
                row.removeClass('table-warning');
            }
        });
        
        // Update totals
        let totalStock = 0;
        let totalCritical = 0;
        let hasCritical = false;
        
        $('input[name*="[stock_quantity]"]').each(function() {
            totalStock += parseInt($(this).val()) || 0;
        });
        
        $('input[name*="[critical_stock]"]').each(function() {
            const critical = parseInt($(this).val()) || 0;
            totalCritical += critical;
            const row = $(this).closest('tr');
            const stock = parseInt(row.find('input[name*="[stock_quantity]"]').val()) || 0;
            if (stock <= critical && critical > 0) {
                hasCritical = true;
            }
        });
        
        // Update footer totals
        $('tfoot th:nth-child(2)').text(totalStock);
        $('tfoot th:nth-child(3)').text(totalCritical);
        
        const overallStatus = $('tfoot th:nth-child(4) .badge');
        if (hasCritical) {
            overallStatus.removeClass('bg-success').addClass('bg-warning').text('Dikkat');
        } else {
            overallStatus.removeClass('bg-warning').addClass('bg-success').text('İyi');
        }
    }
    
    // Bind events for color variant stock inputs
    $(document).on('input', 'input[name*="[stock_quantity]"], input[name*="[critical_stock]"]', function() {
        updateColorVariantStatus();
    });
    
    // Initial status update
    updateColorVariantStatus();
    
    // Debug form submission
    $('#productEditForm').on('submit', function(e) {
        const formData = new FormData(this);
        const colorVariantsData = {};
        
        // Collect color variant data
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('color_variants[')) {
                const match = key.match(/color_variants\[(\d+)\]\[(\w+)\]/);
                if (match) {
                    const variantId = match[1];
                    const field = match[2];
                    if (!colorVariantsData[variantId]) {
                        colorVariantsData[variantId] = {};
                    }
                    colorVariantsData[variantId][field] = value;
                }
            }
        }
        
        console.log('Color variants data being submitted:', colorVariantsData);
        
        // Don't prevent form submission, just log for debugging
    });

    // Critical stock focus - Dashboard'dan gelince stok alanına odaklan
    const urlParams = new URLSearchParams(window.location.search);
    const fromDashboard = document.referrer.includes('/dashboard') || urlParams.get('focus') === 'stock';
    
    @php
        $criticalCheck = false;
        if ($product->colorVariants && $product->colorVariants->count() > 0) {
            $criticalCheck = $product->colorVariants->filter(function($v){ return $v->critical_stock > 0 && $v->stock_quantity <= $v->critical_stock; })->count() > 0;
        } else {
            $criticalCheck = $product->critical_stock > 0 && $product->initial_stock <= $product->critical_stock;
        }
    @endphp
    if (fromDashboard || {{ $criticalCheck ? 'true' : 'false' }}) {
        // Sayfayı stok yönetimi bölümüne kaydır
        setTimeout(() => {
            const stockSection = $('[name="initial_stock"], input[name*="[stock_quantity]"]').first();
            if (stockSection.length) {
                $('html, body').animate({
                    scrollTop: stockSection.offset().top - 100
                }, 1000);
                
                // Stok input'una odaklan
                stockSection.focus().select();
                
                // Kısa bir titreşim efekti
                stockSection.addClass('border-warning').removeClass('border-danger');
                setTimeout(() => {
                    stockSection.removeClass('border-warning').addClass('border-warning');
                }, 2000);
            }
        }, 500);
    }
});
</script>
@endpush

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
                <a href="{{ route('products.show', $product) }}" class="btn btn-primary">Detayları Görüntüle</a>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
