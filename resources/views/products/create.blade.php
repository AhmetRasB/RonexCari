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
                                <input type="text" name="sku" class="form-control" placeholder="Ürün Kodu" value="{{ old('sku') }}">
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:tag-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
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
                        <div class="col-md-6">
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
                        <div class="col-md-6">
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
                                    <option value="Gömlek" {{ old('category') == 'Gömlek' ? 'selected' : '' }}>Gömlek</option>
                                    <option value="Ceket" {{ old('category') == 'Ceket' ? 'selected' : '' }}>Ceket</option>
                                    <option value="Takım Elbise" {{ old('category') == 'Takım Elbise' ? 'selected' : '' }}>Takım Elbise</option>
                                    <option value="Aksesuar" {{ old('category') == 'Aksesuar' ? 'selected' : '' }}>Aksesuar</option>
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
                                    <option value="Ronex" {{ old('brand') == 'Ronex' ? 'selected' : '' }}>Ronex</option>
                                    <option value="Diğer" {{ old('brand') == 'Diğer' ? 'selected' : '' }}>Diğer</option>
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
                            <label class="form-label">Renk</label>
                            <div class="position-relative">
                                <input type="text" id="colorSearch" name="color" class="form-control" placeholder="Renk ara..." value="{{ old('color') }}" autocomplete="off">
                                <input type="hidden" id="selectedColor" name="color" value="{{ old('color') }}">
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
                            <div class="position-relative">
                                <input type="text" name="barcode" class="form-control" placeholder="Barkod" value="{{ old('barcode') }}">
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:qr-code-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            @error('barcode')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label">Tedarikçi Kodu</label>
                            <div class="position-relative">
                                <input type="text" name="supplier_code" class="form-control" placeholder="Tedarikçi Kodu" value="{{ old('supplier_code') }}">
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:users-group-rounded-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            @error('supplier_code')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label">GTIP Kodu</label>
                            <div class="position-relative">
                                <input type="text" name="gtip_code" class="form-control" placeholder="GTIP Kodu" value="{{ old('gtip_code') }}">
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:buildings-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            <small class="text-secondary-light">İhracat işlemleri için kullanılır</small>
                            @error('gtip_code')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label">Sınıf Kodu</label>
                            <div class="position-relative">
                                <input type="text" name="class_code" class="form-control" placeholder="Sınıf Kodu" value="{{ old('class_code') }}">
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:users-group-two-rounded-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            <small class="text-secondary-light">Resmi kurum faturaları için kullanılır</small>
                            @error('class_code')
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
                            <label class="form-label">Başlangıç Stok Miktarı <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="number" name="initial_stock" class="form-control" placeholder="Başlangıç Stok Miktarı" value="{{ old('initial_stock', 0) }}" min="0" required>
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:box-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            <small class="text-secondary-light">Girilen miktar, açılış stok miktarı olarak işlenir</small>
                            @error('initial_stock')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kritik Stok Miktarı <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="number" name="critical_stock" class="form-control" placeholder="Kritik Stok Miktarı" value="{{ old('critical_stock', 0) }}" min="0" required>
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:danger-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            <small class="text-secondary-light">Girilen miktarın altına düşünce uyarı gönderilir</small>
                            @error('critical_stock')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mt-3">
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
});
</script>
@endpush

@endsection
