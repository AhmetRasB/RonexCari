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
                    
                    <div class="row g-3">
                        <!-- Temel Bilgiler -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3">Temel Bilgiler</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Seri Adı *</label>
                            <input type="text" class="form-control radius-8 @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name') }}" placeholder="Seri adını girin" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">SKU</label>
                            <input type="text" class="form-control radius-8 @error('sku') is-invalid @enderror" 
                                   name="sku" id="seriesSku" value="{{ old('sku') }}" placeholder="Seri SKU'su">
                            <small class="text-secondary-light">Otomatik oluşturulur, isterseniz değiştirebilirsiniz</small>
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Barkod</label>
                            <input type="text" class="form-control radius-8 @error('barcode') is-invalid @enderror" 
                                   name="barcode" id="seriesBarcode" value="{{ old('barcode') }}" placeholder="Seri barkodu" readonly style="background-color: #f8f9fa;">
                            <small class="text-secondary-light">Otomatik oluşturulur (düzenlenemez)</small>
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
                            <select class="form-control radius-8 @error('category') is-invalid @enderror" name="category">
                                <option value="">Kategori seçin</option>
                                @php
                                    $currentAccountId = session('current_account_id');
                                    try { $code = \App\Models\Account::find($currentAccountId)?->code; } catch (\Throwable $e) { $code = null; }
                                    $options = ($code === 'ronex1') ? ['Gömlek'] : (($code === 'ronex2') ? ['Ceket','Takım Elbise','Pantalon'] : ['Gömlek','Ceket','Takım Elbise','Pantalon']);
                                @endphp
                                @foreach($options as $cat)
                                    <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
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
                                <option value="Ronex" {{ old('brand') == 'Ronex' ? 'selected' : '' }}>Ronex</option>
                                <option value="Diğer" {{ old('brand') == 'Diğer' ? 'selected' : '' }}>Diğer</option>
                            </select>
                            @error('brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Fiyat Bilgileri -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3 mt-4">Fiyat Bilgileri</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Maliyet (₺)</label>
                            <input type="number" class="form-control radius-8 @error('cost') is-invalid @enderror" 
                                   name="cost" value="{{ old('cost') }}" step="0.01" min="0" max="999999.99" placeholder="0.00">
                            @error('cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Satış Fiyatı (₺)</label>
                            <input type="number" class="form-control radius-8 @error('price') is-invalid @enderror" 
                                   name="price" value="{{ old('price') }}" step="0.01" min="0" max="999999.99" placeholder="0.00">
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Seri Bilgileri -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3 mt-4">Seri Bilgileri</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Seri Tipi *</label>
                            <select class="form-control radius-8 @error('series_type') is-invalid @enderror" 
                                    name="series_type" id="series_type" required>
                                <option value="">Seri tipi seçin</option>
                                <option value="fixed" {{ old('series_type') == 'fixed' ? 'selected' : '' }}>Sabit Seri</option>
                                <option value="custom" {{ old('series_type') == 'custom' ? 'selected' : '' }}>Özel Seri</option>
                            </select>
                            <small class="text-muted">Sabit: Önceden tanımlı bedenler, Özel: Kendi bedenleriniz</small>
                            @error('series_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4" id="series-size-container">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Seri Boyutu *</label>
                            <select class="form-control radius-8 @error('series_size') is-invalid @enderror" 
                                    name="series_size" id="series_size" required>
                                <option value="">Seri boyutu seçin</option>
                                <option value="5" {{ old('series_size') == '5' ? 'selected' : '' }}>5'li Seri</option>
                                <option value="6" {{ old('series_size') == '6' ? 'selected' : '' }}>6'lı Seri</option>
                                <option value="7" {{ old('series_size') == '7' ? 'selected' : '' }}>7'li Seri</option>
                            </select>
                            <small class="text-muted">Her seride kaç adet ürün olacağı</small>
                            @error('series_size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Seri Adedi *</label>
                            <input type="number" class="form-control radius-8 @error('stock_quantity') is-invalid @enderror" 
                                   name="stock_quantity" value="{{ old('stock_quantity', 1) }}" min="0" required>
                            <small class="text-muted">Kaç adet seri oluşturulacak</small>
                            @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Kritik Stok</label>
                            <input type="number" class="form-control radius-8 @error('critical_stock') is-invalid @enderror" 
                                   name="critical_stock" value="{{ old('critical_stock') }}" min="0">
                            @error('critical_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Renk Seçimi -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3 mt-4">Renk Seçimi</h6>
                            <div class="position-relative">
                                <input type="text" id="multiColorSearch" class="form-control" placeholder="Renk ara..." autocomplete="off">
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:palette-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                                <div id="multiColorDropdown" class="dropdown-menu w-100" style="display: none; max-height: 240px; overflow-y: auto; position: absolute; top: 100%; left: 0; z-index: 1060; background: white; border: 1px solid #dee2e6; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);"></div>
                            </div>
                            <div id="selectedColorChips" class="d-flex flex-wrap gap-2 mt-2 mb-1"></div>
                            <div id="multiColorsHidden"></div>
                            <small class="text-muted">Seçilen her renk için ayrı stok takibi yapılacaktır.</small>
                        </div>

                        <!-- Seri İçeriği -->
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3 mt-4">Seri İçeriği *</h6>
                            <div class="alert alert-info mb-3">
                                <i class="ri-information-line me-2"></i>
                                <strong>Sabit Seri:</strong> Önceden tanımlı bedenler (XS, S, M, L, XL, XXL, XXXL) otomatik gelir, her bedenden 1'er adet olur.<br>
                                <strong>Özel Seri:</strong> Kendi bedenlerinizi dropdown'dan seçebilir ve miktarlarını ayarlayabilirsiniz.
                            </div>
                            <div id="series-content">
                                <div class="alert alert-info">
                                    <i class="ri-information-line me-2"></i>
                                    Önce seri boyutunu seçin, bedenler otomatik olarak eklenecektir.
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
    const seriesTypeSelect = document.getElementById('series_type');
    const seriesSizeSelect = document.getElementById('series_size');
    const seriesContent = document.getElementById('series-content');

    // Element'lerin varlığını kontrol et
    if (!seriesTypeSelect || !seriesSizeSelect || !seriesContent) {
        console.error('Required elements not found:', {
            seriesTypeSelect: !!seriesTypeSelect,
            seriesSizeSelect: !!seriesSizeSelect,
            seriesContent: !!seriesContent
        });
        return;
    }

    // Seri tipi veya boyutu değiştiğinde bedenleri güncelle
    seriesTypeSelect.addEventListener('change', updateSeriesContent);
    seriesSizeSelect.addEventListener('change', updateSeriesContent);

    function updateSeriesContent() {
        const seriesType = seriesTypeSelect.value;
        const seriesSize = seriesSizeSelect.value;
        const seriesSizeContainer = document.getElementById('series-size-container');
        
        // Seri boyutu alanını göster/gizle
        if (seriesType === 'custom') {
            seriesSizeContainer.style.display = 'none';
            seriesSizeSelect.required = false;
            loadCustomSeries();
        } else if (seriesType === 'fixed') {
            seriesSizeContainer.style.display = 'block';
            seriesSizeSelect.required = true;
            if (!seriesSize) {
                seriesContent.innerHTML = '<div class="alert alert-info"><i class="ri-information-line me-2"></i>Önce seri boyutunu seçin.</div>';
                return;
            }
            loadFixedSeries(parseInt(seriesSize));
        } else {
            seriesSizeContainer.style.display = 'block';
            seriesSizeSelect.required = true;
            seriesContent.innerHTML = '<div class="alert alert-info"><i class="ri-information-line me-2"></i>Önce seri tipi ve boyutunu seçin.</div>';
        }
    }

    function loadFixedSeries(seriesSize) {
        // API'den güncel beden ayarlarını çek
        fetch(`/api/products/series-default-sizes?series_size=${seriesSize}`)
            .then(response => response.json())
            .then(data => {
                const selectedSizes = data.sizes || [];
                
                let html = '<div class="table-responsive"><table class="table table-bordered">';
                html += '<thead><tr><th>Beden</th><th>Seri Başına Adet</th></tr></thead><tbody>';
                
                selectedSizes.forEach(function(size) {
                    html += `
                        <tr>
                            <td>
                                <input type="text" class="form-control" name="sizes[]" value="${size}" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control" name="quantities[]" value="1" min="1" required>
                            </td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table></div>';
                html += '<div class="alert alert-info mt-2">';
                html += '<i class="ri-information-line me-2"></i>';
                html += '<strong>Sabit Seri:</strong> Bedenler önceden tanımlıdır, miktarları istediğiniz gibi ayarlayabilirsiniz.';
                html += '</div>';
                
                seriesContent.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading fixed series:', error);
                seriesContent.innerHTML = '<div class="alert alert-danger"><i class="ri-error-warning-line me-2"></i>Beden ayarları yüklenirken hata oluştu.</div>';
            });
    }

    function loadCustomSeries() {
        let html = '<div class="table-responsive"><table class="table table-bordered">';
        html += '<thead><tr><th>Beden</th><th>Seri Başına Adet</th><th>İşlem</th></tr></thead><tbody>';
        
        // İlk satırı boş olarak ekle
        html += `
            <tr>
                <td>
                    <select class="form-control" name="sizes[]" required>
                        <option value="">Beden seçin</option>
                        ${sizeOptions.map(size => `<option value="${size}">${size}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control" name="quantities[]" value="1" min="1" required>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-size" disabled>
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        `;
        
        html += '</tbody></table></div>';
        html += '<button type="button" class="btn btn-outline-primary btn-sm" id="add-custom-size">Beden Ekle</button>';
        html += '<div id="total-quantity-info" class="alert alert-info mt-2">';
        html += '<i class="ri-information-line me-2"></i>';
        html += '<strong>Özel Seri:</strong> İstediğiniz kadar beden ekleyebilirsiniz. Toplam miktar otomatik hesaplanır.';
        html += '</div>';
        
        seriesContent.innerHTML = html;
        updateRemoveButtons(); // Initial update for remove buttons
    }

    // Özel beden ekleme
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'add-custom-size') {
            const currentRows = document.querySelectorAll('#series-content tbody tr').length;
            
            const newRow = `
                <tr>
                    <td>
                        <select class="form-control" name="sizes[]" required>
                            <option value="">Beden seçin</option>
                            ${sizeOptions.map(size => `<option value="${size}">${size}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control" name="quantities[]" value="1" min="1" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-size">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            document.querySelector('#series-content tbody').insertAdjacentHTML('beforeend', newRow);
            updateRemoveButtons();
            updateTotalQuantityInfo();
        }

        // Beden silme
        if (e.target && e.target.closest('.remove-size')) {
            const currentRows = document.querySelectorAll('#series-content tbody tr').length;
            if (currentRows > 1) {
                e.target.closest('tr').remove();
                updateRemoveButtons();
                updateTotalQuantityInfo();
            }
        }
    });

    function updateRemoveButtons() {
        const currentRows = document.querySelectorAll('#series-content tbody tr').length;
        const removeButtons = document.querySelectorAll('.remove-size');
        removeButtons.forEach(button => {
            button.disabled = currentRows <= 1;
        });
    }

    function updateTotalQuantityInfo() {
        const seriesType = seriesTypeSelect.value;
        
        // Sadece custom seri tipi için toplam miktar bilgisini göster
        if (seriesType === 'custom') {
            const quantityInputs = document.querySelectorAll('input[name="quantities[]"]');
            let totalQuantity = 0;
            
            quantityInputs.forEach(input => {
                totalQuantity += parseInt(input.value) || 0;
            });
            
            const totalInfo = document.getElementById('total-quantity-info');
            if (totalInfo) {
                totalInfo.innerHTML = `
                    <i class="ri-information-line me-2"></i>
                    <strong>Toplam Miktar:</strong> ${totalQuantity} adet
                    <span class="text-success ms-2"><i class="ri-check-line"></i> Özel Seri</span>
                `;
            }
        }
    }


    // Form submit validation
    document.getElementById('submit-btn').addEventListener('click', function(e) {
        const seriesType = seriesTypeSelect.value;
        const seriesSize = parseInt(seriesSizeSelect.value);
        
        // Özel seri için sadece en az 1 beden kontrolü yap
        if (seriesType === 'custom') {
            const sizeInputs = document.querySelectorAll('select[name="sizes[]"]');
            let validSizes = 0;
            sizeInputs.forEach(select => {
                if (select.value && select.value.trim() !== '') {
                    validSizes++;
                }
            });
            
            if (validSizes === 0) {
                e.preventDefault();
                alert('Özel seri için en az 1 beden seçmelisiniz.');
                return false;
            }
        }
        
        // Sabit seri için sadece beden sayısı kontrolü yap
        if (seriesType === 'fixed') {
            const sizeInputs = document.querySelectorAll('input[name="sizes[]"]');
            if (sizeInputs.length !== seriesSize) {
                e.preventDefault();
                alert(`Sabit seri için ${seriesSize} beden olmalı, ${sizeInputs.length} beden bulundu.`);
                return false;
            }
        }
    });

    // Miktar değiştiğinde toplam kontrolü (özel seriler için)
    document.addEventListener('input', function(e) {
        if (e.target && e.target.name === 'quantities[]') {
            const seriesType = seriesTypeSelect.value;
            if (seriesType === 'custom') {
                updateTotalQuantityInfo();
            }
        }
    });

    // Renk seçimi JavaScript
    const colors = [
        'Kırmızı', 'Mavi', 'Yeşil', 'Sarı', 'Siyah', 'Beyaz', 'Mor', 'Turuncu', 'Pembe', 'Lacivert',
        'Kahverengi', 'Gri', 'Altın', 'Gümüş', 'Bronz', 'Turkuaz', 'Lavanta', 'Koyu Mavi', 'Koyu Yeşil',
        'Koyu Kırmızı', 'Açık Mavi', 'Açık Yeşil', 'Açık Pembe', 'Koyu Gri', 'Açık Gri', 'Neon Yeşil',
        'Neon Turuncu', 'Neon Mavi', 'Neon Kırmızı', 'Mat Siyah', 'Mat Beyaz', 'Mat Gri',
        'Mat Mavi', 'Mat Kırmızı', 'Mat Yeşil', 'Mat Sarı', 'Mat Mor', 'Mat Pembe', 'Mat Kahverengi'
    ];

    const selectedSet = new Set();

    // Multi-color search functionality
    $('#multiColorSearch').on('input', function() {
        const query = $(this).val().toLowerCase();
        if (query.length >= 1) {
            const filteredColors = colors.filter(color => 
                color.toLowerCase().includes(query)
            );
            renderMultiDropdown(filteredColors);
        } else {
            renderMultiDropdown(colors);
        }
    });

    // Show all colors on focus
    $('#multiColorSearch').on('focus', function() {
        renderMultiDropdown(colors);
    });

    // Render multi-color dropdown
    function renderMultiDropdown(list) {
        let html = '';
        if (list.length === 0) {
            html = '<div class="dropdown-item text-secondary-light" style="padding: 8px 16px;">Renk bulunamadı</div>';
        } else {
            list.forEach(function(color) {
                const active = selectedSet.has(color) ? ' active' : '';
                const checked = selectedSet.has(color) ? 'checked' : '';
                html += `
                    <div class="color-option-check dropdown-item${active}" data-color="${color}" style="cursor: pointer; padding: 8px 16px;">
                        <input type="checkbox" class="form-check-input" ${checked}>
                        <span>${color}</span>
                    </div>
                `;
            });
        }
        
        $('#multiColorDropdown').html(html).show();
        
        // Ensure dropdown is positioned correctly
        $('#multiColorDropdown').css({
            'position': 'absolute',
            'top': '100%',
            'left': '0',
            'right': '0',
            'transform': 'none',
            'margin-top': '0'
        });
    }

    // Color selection
    $(document).on('click', '.color-option-check', function(e) {
        e.preventDefault();
        const color = $(this).data('color');
        const checkbox = $(this).find('input[type="checkbox"]');
        
        if (selectedSet.has(color)) {
            selectedSet.delete(color);
            checkbox.prop('checked', false);
            $(this).removeClass('active');
        } else {
            selectedSet.add(color);
            checkbox.prop('checked', true);
            $(this).addClass('active');
        }
        
        updateSelectedColors();
    });

    // Update selected colors display
    function updateSelectedColors() {
        const chipsContainer = $('#selectedColorChips');
        const hiddenContainer = $('#multiColorsHidden');
        
        chipsContainer.empty();
        hiddenContainer.empty();
        
        selectedSet.forEach(function(color) {
            // Add chip
            const chip = $(`
                <span class="badge bg-primary d-inline-flex align-items-center gap-1">
                    ${color}
                    <button type="button" class="btn-close btn-close-white" style="font-size: 0.7em;" data-color="${color}"></button>
                </span>
            `);
            chipsContainer.append(chip);
            
            // Add hidden input
            hiddenContainer.append(`<input type="hidden" name="colors[]" value="${color}">`);
        });
    }

    // Remove color chip
    $(document).on('click', '.btn-close', function(e) {
        e.preventDefault();
        const color = $(this).data('color');
        selectedSet.delete(color);
        updateSelectedColors();
        
        // Update dropdown
        const query = $('#multiColorSearch').val().toLowerCase();
        if (query.length >= 1) {
            const filteredColors = colors.filter(color => 
                color.toLowerCase().includes(query)
            );
            renderMultiDropdown(filteredColors);
        } else {
            renderMultiDropdown(colors);
        }
    });

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#multiColorSearch, #multiColorDropdown').length) {
            $('#multiColorDropdown').hide();
        }
    });

    // Initialize with all colors
    renderMultiDropdown(colors);
    
    // Otomatik SKU ve Barkod oluşturma fonksiyonu
    function generateSeriesCodes() {
        // Eğer SKU boşsa otomatik oluştur
        if (!$('#seriesSku').val()) {
            const timestamp = Date.now().toString().slice(-6); // Son 6 hane
            const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            const sku = 'SER' + timestamp + randomNum;
            $('#seriesSku').val(sku);
        }
        
        // Barkod her zaman otomatik oluştur (kısa format)
        const timestamp = Date.now().toString().slice(-4); // Son 4 hane
        const barcode = 'S' + timestamp;
        $('#seriesBarcode').val(barcode);
    }
});
</script>
@endpush
