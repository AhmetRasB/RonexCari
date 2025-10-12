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
                    <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#barcodeModal">
                        <i class="ri-barcode-line me-1"></i>Barkod Oluştur
                    </button>
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
                                            @php
                                                $currency = $series->currency ?? 'TRY';
                                                $currencySymbol = $currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : '₺');
                                            @endphp
                                            {{ number_format($series->cost, 2) }} {{ $currencySymbol }}
                                            @if($currency !== 'TRY')
                                                <br><small class="text-muted" id="costTRY">-</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">Satış Fiyatı</label>
                                        <div class="form-control-plaintext fw-semibold text-success">
                                            {{ number_format($series->price, 2) }} {{ $currencySymbol }}
                                            @if($currency !== 'TRY')
                                                <br><small class="text-muted" id="priceTRY">-</small>
                                            @endif
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
                                                @if($series->critical_stock > 0 && $series->stock_quantity <= $series->critical_stock)
                                                    <i class="ri-alert-line text-danger ms-1" title="Kritik Stok!"></i>
                                                @endif
                                            @else
                                                <span class="text-muted">Belirlenmemiş</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Renk Bazlı Stok Bilgileri -->
                            @if($series->colorVariants->count() > 0)
                            <div class="col-12">
                                <h6 class="fw-semibold text-primary mb-3">Renk Bazlı Stok Bilgileri</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Renk</th>
                                                <th>Stok (Seri)</th>
                                                <th>Kritik Stok</th>
                                                <th>Durum</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($series->colorVariants as $variant)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-{{ $variant->color === 'Kırmızı' ? 'danger' : ($variant->color === 'Mavi' ? 'primary' : ($variant->color === 'Yeşil' ? 'success' : ($variant->color === 'Sarı' ? 'warning' : ($variant->color === 'Siyah' ? 'dark' : 'secondary')))) }} me-2">●</span>
                                                    {{ $variant->color }}
                                                </td>
                                                <td>{{ number_format($variant->stock_quantity) }} Seri</td>
                                                <td>{{ number_format($variant->critical_stock) }} Seri</td>
                                                <td>
                                                    @if($variant->critical_stock > 0 && $variant->stock_quantity <= $variant->critical_stock)
                                                        <span class="badge bg-danger">Kritik</span>
                                                    @else
                                                        <span class="badge bg-success">Normal</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                            <tr class="table-info">
                                                <td><strong>Toplam</strong></td>
                                                <td><strong>{{ number_format($series->colorVariants->sum('stock_quantity')) }} Seri</strong></td>
                                                <td><strong>{{ number_format($series->colorVariants->sum('critical_stock')) }} Seri</strong></td>
                                                <td>
                                                    @if($series->colorVariants->filter(function($v){ return $v->critical_stock > 0 && $v->stock_quantity <= $v->critical_stock; })->count() > 0)
                                                        <span class="badge bg-warning">Dikkat</span>
                                                    @else
                                                        <span class="badge bg-success">İyi</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
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
                                    <img src="{{ $series->image_url }}?v={{ time() }}" 
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

                        <!-- Kod Bilgileri -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="ri-barcode-line me-1"></i>Kod Bilgileri
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label text-muted small">Seri Barkod</label>
                                    <div class="fw-semibold font-monospace">
                                        @php
                                            $seriesBarcode = $series->barcode ?: ('SER-' . str_pad((string)$series->id, 8, '0', STR_PAD_LEFT));
                                        @endphp
                                        {{ $seriesBarcode }}
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6 text-center">
                                        <small class="text-muted d-block">Barkod</small>
                                        <div id="seriesBarcode" style="height: 40px; display: flex; align-items: center; justify-content: center;">
                                            <svg id="barcodeSvg" style="width: 100%; height: 40px;"></svg>
                                        </div>
                                        <div class="small mt-1 font-monospace">{{ $seriesBarcode }}</div>
                                    </div>
                                    <div class="col-6 text-center">
                                        <small class="text-muted d-block">QR Kod</small>
                                        <div id="seriesQR" style="width: 80px; height: 80px; margin: 0 auto;">
                                            <img id="qrImg" src="" alt="QR" class="img-fluid" style="max-width: 80px; max-height: 80px;" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hızlı Stok Güncelleme -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Hızlı Stok Güncelleme</h6>
                            </div>
                            <div class="card-body">
                                <form id="quick-stock-form">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Stok Ekle (Seri Adedi)</label>
                                            <input type="number" class="form-control" id="add_stock" 
                                                   placeholder="Eklenecek seri sayısı" min="0">
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ri-save-line me-1"></i>Stok Ekle
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Barcode Generation Modal -->
<div class="modal fade" id="barcodeModal" tabindex="-1" aria-labelledby="barcodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="barcodeModalLabel">Barkod/QR Etiketleri Oluştur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="barcodeForm" action="{{ route('products.series.barcodes.generate', $series) }}" method="POST" target="_blank">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Barkod Tipi</label>
                            <select name="type" class="form-select" required>
                                <option value="barcode">Sadece Barkod</option>
                                <option value="qr">Sadece QR</option>
                                <option value="both" selected>Barkod + QR</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kağıt Boyutu</label>
                            <select name="paper" class="form-select">
                                <option value="A4" selected>A4 (10x3, 30 etiket)</option>
                                <option value="A4-40">A4 (8x5, 40 etiket)</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="40%">Etiket Tipi</th>
                                    <th width="30%">Açıklama</th>
                                    <th width="30%">Adet</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($series->colorVariants->count() > 0)
                                    <!-- Color-based Labels -->
                                    @foreach($series->colorVariants as $color)
                                        <!-- Color Main Label -->
                                        <tr>
                                            <td>
                                                <label class="d-flex align-items-center gap-2 mb-0">
                                                    <input type="checkbox" class="form-check-input select-item" checked>
                                                    <span class="fw-semibold">{{ $color->color }} Ana</span>
                                                </label>
                                                <input type="hidden" name="items[][type]" value="color_main" disabled>
                                                <input type="hidden" name="items[][identifier]" value="{{ $color->color }}" disabled>
                                            </td>
                                            <td class="text-muted">{{ $color->color }} rengi için ana etiket (paket sayısı)</td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm qty-input" name="items[][count]" value="1" min="1" max="200" disabled>
                                            </td>
                                        </tr>
                                        
                                        <!-- Size Labels for this Color -->
                                        @foreach($series->seriesItems as $item)
                                        <tr>
                                            <td>
                                                <label class="d-flex align-items-center gap-2 mb-0">
                                                    <input type="checkbox" class="form-check-input select-item">
                                                    <span class="fw-semibold">{{ $item->size }}-{{ $color->color }}</span>
                                                </label>
                                                <input type="hidden" name="items[][type]" value="size_color" disabled>
                                                <input type="hidden" name="items[][identifier]" value="{{ $item->size }}-{{ $color->color }}" disabled>
                                            </td>
                                            <td class="text-muted">{{ $item->size }} bedeni {{ $color->color }} rengi için etiket</td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm qty-input" name="items[][count]" value="1" min="1" max="200" disabled style="opacity: 0.5;">
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endforeach
                                @else
                                    <!-- No Colors - Generic Series Labels -->
                                    <!-- Main Series Label -->
                                    <tr>
                                        <td>
                                            <label class="d-flex align-items-center gap-2 mb-0">
                                                <input type="checkbox" class="form-check-input select-item" checked>
                                                <span class="fw-semibold">Ana Seri Etiketi</span>
                                            </label>
                                            <input type="hidden" name="items[][type]" value="main" disabled>
                                            <input type="hidden" name="items[][identifier]" value="main" disabled>
                                        </td>
                                        <td class="text-muted">Seri paketi için ana etiket</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm qty-input" name="items[][count]" value="1" min="1" max="200" disabled>
                                        </td>
                                    </tr>

                                    <!-- Size Labels -->
                                    @foreach($series->seriesItems as $item)
                                    <tr>
                                        <td>
                                            <label class="d-flex align-items-center gap-2 mb-0">
                                                <input type="checkbox" class="form-check-input select-item">
                                                <span class="fw-semibold">Beden: {{ $item->size }}</span>
                                            </label>
                                            <input type="hidden" name="items[][type]" value="size" disabled>
                                            <input type="hidden" name="items[][identifier]" value="{{ $item->size }}" disabled>
                                        </td>
                                        <td class="text-muted">{{ $item->size }} bedeni için etiket</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm qty-input" name="items[][count]" value="1" min="1" max="200" disabled style="opacity: 0.5;">
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-printer-line me-1"></i>Yazdır
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate barcode and QR code for series
    const seriesBarcode = '{{ $seriesBarcode }}';
    const seriesQRUrl = '{{ route("products.series.show", $series) }}';
    
    // Generate barcode
    try {
        JsBarcode("#barcodeSvg", seriesBarcode, {
            format: "CODE128",
            displayValue: false,
            margin: 0,
            height: 40
        });
    } catch (e) {
        console.log('Barcode generation error:', e);
    }
    
    // Generate QR code
    const qrImg = document.getElementById('qrImg');
    qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=${encodeURIComponent(seriesQRUrl)}`;
    
    // Currency conversion for cost and price
    @if(($series->currency ?? 'TRY') !== 'TRY')
        const seriesCurrency = '{{ $series->currency ?? 'TRY' }}';
        const seriesCost = {{ $series->cost }};
        const seriesPrice = {{ $series->price }};
        
        $.get('{{ route("sales.invoices.currency.rates") }}')
            .done(function(response) {
                let exchangeRate;
                if (response.success && response.rates[seriesCurrency]) {
                    exchangeRate = response.rates[seriesCurrency];
                } else {
                    const fallbackRates = { 'USD': 41.29, 'EUR': 48.55 };
                    exchangeRate = fallbackRates[seriesCurrency] || 1;
                }
                
                const costTRY = seriesCost * exchangeRate;
                const priceTRY = seriesPrice * exchangeRate;
                
                $('#costTRY').text('(' + costTRY.toFixed(2).replace('.', ',') + ' ₺)');
                $('#priceTRY').text('(' + priceTRY.toFixed(2).replace('.', ',') + ' ₺)');
            })
            .fail(function() {
                $('#costTRY').text('(Kur alınamadı)');
                $('#priceTRY').text('(Kur alınamadı)');
            });
    @endif
    
    // Auto-open barcode modal if redirected from create page
    @if(session('generate_barcodes'))
        const barcodeModal = new bootstrap.Modal(document.getElementById('barcodeModal'));
        barcodeModal.show();
        
        // Set the barcode type and paper size from session
        @if(session('barcode_type'))
            document.querySelector('select[name="type"]').value = '{{ session("barcode_type") }}';
        @endif
        @if(session('paper_size'))
            document.querySelector('select[name="paper"]').value = '{{ session("paper_size") }}';
        @endif
    @endif
    
    // Quick stock form
    const form = document.getElementById('quick-stock-form');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const addStock = document.getElementById('add_stock').value;
        
        if (!addStock || addStock <= 0) {
            alert('Lütfen eklenecek stok miktarını giriniz.');
            return;
        }
        
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('add_stock', addStock);
        
        fetch('{{ route("products.series.quick-stock", $series) }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Stok güncellendi! Yeni stok: ' + data.data.stock_quantity + ' seri');
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

    // Barcode modal functionality
    document.querySelectorAll('.select-item').forEach(function(cb) {
        // Initialize checked items on page load
        if (cb.checked) {
            const row = cb.closest('tr');
            const qtyInput = row.querySelector('input[name="items[][count]"]');
            const typeInput = row.querySelector('input[name="items[][type]"]');
            const identifierInput = row.querySelector('input[name="items[][identifier]"]');
            
            typeInput.disabled = false;
            identifierInput.disabled = false;
            qtyInput.disabled = false;
            qtyInput.style.opacity = '1';
        }
        
        cb.addEventListener('change', function() {
            const row = this.closest('tr');
            const qtyInput = row.querySelector('input[name="items[][count]"]');
            const typeInput = row.querySelector('input[name="items[][type]"]');
            const identifierInput = row.querySelector('input[name="items[][identifier]"]');
            
            if (this.checked) {
                typeInput.disabled = false;
                identifierInput.disabled = false;
                qtyInput.disabled = false;
                qtyInput.style.opacity = '1';
            } else {
                typeInput.disabled = true;
                identifierInput.disabled = true;
                qtyInput.disabled = true;
                qtyInput.style.opacity = '0.5';
            }
        });
    });

    // Barcode form submission
    const barcodeForm = document.getElementById('barcodeForm');
    barcodeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const selectedRows = Array.from(document.querySelectorAll('.select-item')).filter(cb => cb.checked).map(cb => cb.closest('tr'));
        if (selectedRows.length === 0) {
            alert('Lütfen en az bir etiket tipi seçin.');
            return false;
        }

        // Create temp form
        const tmp = document.createElement('form');
        tmp.method = 'POST';
        tmp.action = this.action;
        tmp.target = '_blank';

        // CSRF
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        tmp.appendChild(csrf);

        // Type and paper selects
        ['type','paper'].forEach(name => {
            const sel = this.querySelector(`[name="${name}"]`);
            if (sel) {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = name;
                inp.value = sel.value;
                tmp.appendChild(inp);
            }
        });

        // Items
        selectedRows.forEach((row, idx) => {
            const type = row.querySelector('input[name="items[][type]"]').value;
            const identifier = row.querySelector('input[name="items[][identifier]"]').value;
            const count = row.querySelector('input[name="items[][count]"]').value || '1';

            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = `items[${idx}][type]`;
            typeInput.value = type;
            tmp.appendChild(typeInput);

            const identifierInput = document.createElement('input');
            identifierInput.type = 'hidden';
            identifierInput.name = `items[${idx}][identifier]`;
            identifierInput.value = identifier;
            tmp.appendChild(identifierInput);

            const countInput = document.createElement('input');
            countInput.type = 'hidden';
            countInput.name = `items[${idx}][count]`;
            countInput.value = count;
            tmp.appendChild(countInput);
        });

        document.body.appendChild(tmp);
        tmp.submit();
        setTimeout(() => tmp.remove(), 1000);
    });
});
</script>
@endpush
@endsection


