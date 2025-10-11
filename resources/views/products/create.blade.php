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
                            <label class="form-label d-flex align-items-center justify-content-between">
                                <span>Renk (Çoklu renk için virgülle ayırın)</span>
                            </label>
                            <div class="position-relative">
                                <input type="text" name="colors_input" class="form-control" placeholder="Örn: mavi, kırmızı, haki, koyu kahverengi" value="{{ old('colors_input') }}" autocomplete="off">
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:palette-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            <small class="text-secondary-light">Birden fazla renk girmek için virgülle ayırın (örn: mavi, siyah, beyaz). Her renk için ayrı ürün oluşturulur.</small>
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
                                <input type="text" name="barcode" id="productBarcode" class="form-control" placeholder="Barkod" value="{{ old('barcode') }}" readonly style="background-color: #f8f9fa;">
                                <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                    <iconify-icon icon="solar:qr-code-outline" class="text-secondary-light"></iconify-icon>
                                </div>
                            </div>
                            <small class="text-secondary-light">Otomatik oluşturulur (düzenlenemez)</small>
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
                                <input type="number" name="stock_quantity" class="form-control" placeholder="Stok Miktarı" value="{{ old('stock_quantity', 0) }}" min="0" required>
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
    // Otomatik SKU ve Barkod oluştur
    generateProductCodes();
    
    // Otomatik SKU ve Barkod oluşturma fonksiyonu
    function generateProductCodes() {
        // Eğer SKU boşsa otomatik oluştur
        if (!$('#productSku').val()) {
            const timestamp = Date.now().toString().slice(-6); // Son 6 hane
            const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            const sku = 'PRD' + timestamp + randomNum;
            $('#productSku').val(sku);
        }
        
        // Barkod her zaman otomatik oluştur (kısa format)
        const timestamp = Date.now().toString().slice(-4); // Son 4 hane
        const barcode = 'P' + timestamp;
        $('#productBarcode').val(barcode);
    }
});
</script>
@endpush

@endsection
