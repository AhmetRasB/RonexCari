@extends('layout.layout')

@section('title', 'Fatura Detayı')
@section('subTitle', 'Fatura Bilgileri')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h5 class="card-title mb-0">Fatura Detayı</h5>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('sales.invoices.edit', $invoice) }}" class="btn btn-sm btn-success radius-8 d-inline-flex align-items-center gap-1">
                        <iconify-icon icon="lucide:edit" class="text-xl"></iconify-icon>
                        Düzenle
                    </a>
                    <a href="{{ route('sales.exchanges.create', $invoice) }}" class="btn btn-sm btn-info radius-8 d-inline-flex align-items-center gap-1">
                        <iconify-icon icon="solar:refresh-outline" class="text-xl"></iconify-icon>
                        Değişim
                    </a>
                    <div class="btn-group">
                        <a href="{{ route('sales.invoices.print', $invoice) }}" target="_blank" class="btn btn-sm btn-warning radius-8 d-inline-flex align-items-center gap-1">
                            <iconify-icon icon="basil:printer-outline" class="text-xl"></iconify-icon>
                            Yazdır
                        </a>
                        <button type="button" class="btn btn-sm btn-warning dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" target="_blank" href="{{ route('sales.invoices.print', $invoice) }}?lang=tr">Türkçe</a></li>
                            <li><a class="dropdown-item" target="_blank" href="{{ route('sales.invoices.print', $invoice) }}?lang=en">English</a></li>
                            <li><a class="dropdown-item" target="_blank" href="{{ route('sales.invoices.print', $invoice) }}?lang=ar">العربية</a></li>
                            <li><a class="dropdown-item" target="_blank" href="{{ route('sales.invoices.print', $invoice) }}?lang=ru">Русский</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('sales.invoices.index') }}" class="btn btn-sm btn-secondary radius-8 d-inline-flex align-items-center gap-1">
                        <iconify-icon icon="solar:arrow-left-outline" class="text-xl"></iconify-icon>
                        Geri Dön
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-3">Fatura Bilgileri</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-medium">Fatura No:</td>
                                <td>{{ $invoice->invoice_number }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Fatura Tarihi:</td>
                                <td>{{ $invoice->invoice_date->format('d.m.Y') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Fatura Saati:</td>
                                <td>{{ $invoice->invoice_time ?: '00:00' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Vade Tarihi:</td>
                                <td>{{ $invoice->due_date->format('d.m.Y') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Para Birimi:</td>
                                <td>
                                    @if($invoice->currency === 'USD')
                                        $ USD
                                    @elseif($invoice->currency === 'EUR')
                                        € EUR
                                    @else
                                        ₺ TRY
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium">KDV Durumu:</td>
                                <td>{{ $invoice->vat_status == 'included' ? 'Dahil' : 'Hariç' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Durum:</td>
                                <td>
                                    @switch($invoice->status)
                                        @case('draft')
                                            <span class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">Taslak</span>
                                            @break
                                        @case('sent')
                                            <span class="bg-info-focus text-info-main px-24 py-4 rounded-pill fw-medium text-sm">Gönderildi</span>
                                            @break
                                        @case('paid')
                                            <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Ödendi</span>
                                            @break
                                        @case('overdue')
                                            <span class="bg-danger-focus text-danger-main px-24 py-4 rounded-pill fw-medium text-sm">Vadesi Geçti</span>
                                            @break
                                        @case('cancelled')
                                            <span class="bg-secondary-focus text-secondary-main px-24 py-4 rounded-pill fw-medium text-sm">İptal</span>
                                            @break
                                    @endswitch
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Tahsilat Durumu:</td>
                                <td>
                                    @if($invoice->payment_completed)
                                        <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Tahsilat Yapıldı</span>
                                    @else
                                        <span class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">Tahsilat Bekleniyor</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-3">Müşteri Bilgileri</h6>
                        <table class="table table-borderless">
                            @if($invoice->customer)
                            <tr>
                                <td class="fw-medium">Müşteri Adı:</td>
                                <td>{{ $invoice->customer->name }}</td>
                            </tr>
                            @if($invoice->customer->company_name)
                            <tr>
                                <td class="fw-medium">Şirket Adı:</td>
                                <td>{{ $invoice->customer->company_name }}</td>
                            </tr>
                            @endif
                            @if($invoice->customer->email)
                            <tr>
                                <td class="fw-medium">E-posta:</td>
                                <td>{{ $invoice->customer->email }}</td>
                            </tr>
                            @endif
                            @if($invoice->customer->phone)
                            <tr>
                                <td class="fw-medium">Telefon:</td>
                                <td>{{ $invoice->customer->phone }}</td>
                            </tr>
                            @endif
                            @else
                            <tr>
                                <td colspan="2" class="text-muted">Müşteri bilgisi bulunamadı</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                @if($invoice->description)
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="fw-semibold mb-3">Açıklama</h6>
                        <p class="text-secondary-light">{{ $invoice->description }}</p>
                    </div>
                </div>
                @endif

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-semibold mb-0">Fatura Kalemleri</h6>
                            <button type="button" class="btn btn-sm btn-danger radius-8 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addReturnModal">
                                <iconify-icon icon="solar:refresh-outline" class="text-xl"></iconify-icon>
                                İade Ekle
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sıra</th>
                                        <th>Ürün/Hizmet</th>
                                        <th>Açıklama</th>
                                        <th>Miktar</th>
                                        <th>Birim Fiyat</th>
                                        <th>KDV Oranı</th>
                                        <th>İndirim</th>
                                        <th>Toplam</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->items as $index => $item)
                                    @php
                                        $isExchange = str_starts_with($item->description ?? '', 'Değişim -');
                                    @endphp
                                    <tr class="{{ $item->is_return ? 'table-danger' : ($isExchange ? 'table-info' : '') }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            {{ $item->product_service_name }}
                                            @if($item->is_return)
                                                <span class="badge bg-danger ms-2" style="font-size: 10px;">İADE</span>
                                            @elseif($isExchange)
                                                <span class="badge bg-info ms-2" style="font-size: 10px;">DEĞİŞİM</span>
                                            @endif
                                            @if($item->selected_color)
                                                <br><small class="text-muted">Renk: {{ $item->selected_color }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->description ?? '-' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>
                                            {{ number_format($item->unit_price, 2) }}
                                            @if($invoice->currency === 'USD')
                                                $
                                            @elseif($invoice->currency === 'EUR')
                                                €
                                            @else
                                                ₺
                                            @endif
                                            @if($invoice->currency !== 'TRY')
                                                <br><small class="text-muted" id="unitPriceTRY_{{ $index }}">-</small>
                                            @endif
                                        </td>
                                        <td>%{{ $item->tax_rate }}</td>
                                        <td>
                                            {{ number_format($item->discount_rate, 2) }}
                                            @if($invoice->currency === 'USD')
                                                $
                                            @elseif($invoice->currency === 'EUR')
                                                €
                                            @else
                                                ₺
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->is_return)
                                                <span class="text-danger">-{{ number_format(abs($item->line_total), 2) }}</span>
                                            @else
                                                {{ number_format($item->line_total, 2) }}
                                            @endif
                                            @if($invoice->currency === 'USD')
                                                $
                                            @elseif($invoice->currency === 'EUR')
                                                €
                                            @else
                                                ₺
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->is_return)
                                                <span class="badge bg-danger">İade</span>
                                            @elseif($isExchange)
                                                <span class="badge bg-info">Değişim</span>
                                            @else
                                                <span class="badge bg-success">Normal</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3">Fatura Toplamları</h6>
                                @php
                                    // Recalculate totals consistently from item primitives to avoid mixed stored values
                                    $calcSubtotal = 0;
                                    $calcVat = 0;
                                    $returnsTotalDisplay = 0;

                                    foreach ($invoice->items as $it) {
                                        $lineSubtotal = max(0, ($it->quantity * $it->unit_price) - ($it->discount_rate ?? 0));
                                        $lineVat = $invoice->vat_status === 'included'
                                            ? $lineSubtotal * (($it->tax_rate ?? 0) / 100)
                                            : 0;
                                        if ($it->is_return) {
                                            // Returns reduce totals
                                            $calcSubtotal -= $lineSubtotal;
                                            $calcVat -= $lineVat;
                                            $returnsTotalDisplay += ($lineSubtotal + $lineVat);
                                        } else {
                                            $calcSubtotal += $lineSubtotal;
                                            $calcVat += $lineVat;
                                        }
                                    }

                                    // Display values: never below zero for classic fields
                                    $displaySubtotal = max(0, $calcSubtotal);
                                    $displayVat = max(0, $calcVat);
                                    $displayTotal = max(0, $calcSubtotal + $calcVat);
                                    $remainingAfterReturns = max(0, -($calcSubtotal + $calcVat)); // Kalan/Alacak
                                @endphp
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Ara Toplam:</span>
                                    <span>
                                        {{ number_format($displaySubtotal, 2) }}
                                        @if($invoice->currency === 'USD')
                                            $
                                        @elseif($invoice->currency === 'EUR')
                                            €
                                        @else
                                            ₺
                                        @endif
                                    </span>
                                </div>
                                @if($returnsTotalDisplay > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-danger">İade Toplamı:</span>
                                    <span class="text-danger">
                                        {{ number_format($returnsTotalDisplay, 2) }}
                                        @if($invoice->currency === 'USD')
                                            $
                                        @elseif($invoice->currency === 'EUR')
                                            €
                                        @else
                                            ₺
                                        @endif
                                    </span>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between mb-2">
                                    <span>KDV:</span>
                                    <span>
                                        {{ number_format($displayVat, 2) }}
                                        @if($invoice->currency === 'USD')
                                            $
                                        @elseif($invoice->currency === 'EUR')
                                            €
                                        @else
                                            ₺
                                        @endif
                                    </span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Genel Toplam:</span>
                                    <span>
                                        {{ number_format($displayTotal, 2) }}
                                        @if($invoice->currency === 'USD')
                                            $
                                        @elseif($invoice->currency === 'EUR')
                                            €
                                        @else
                                            ₺
                                        @endif
                                    </span>
                                </div>
                                @if($remainingAfterReturns > 0)
                                <div class="d-flex justify-content-between mt-2">
                                    <span class="fw-medium text-danger">Kalan İade/Alacak:</span>
                                    <span class="fw-medium text-danger">
                                        {{ number_format($remainingAfterReturns, 2) }}
                                        @if($invoice->currency === 'USD')
                                            $
                                        @elseif($invoice->currency === 'EUR')
                                            €
                                        @else
                                            ₺
                                        @endif
                                    </span>
                                </div>
                                @endif
                                <!-- TL eşdeğeri ve kur gösterimi kaldırıldı -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- İade Ekle Modal -->
<div class="modal fade" id="addReturnModal" tabindex="-1" aria-labelledby="addReturnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addReturnModalLabel">
                    <iconify-icon icon="solar:refresh-outline" class="me-2"></iconify-icon>
                    İade Ekle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="returnItemForm" action="{{ route('sales.invoices.add-return', $invoice) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <iconify-icon icon="solar:info-circle-outline" class="me-2"></iconify-icon>
                        <strong>Not:</strong> İade edilen ürün fatura toplamından düşecek ve stoğa geri eklenecektir.
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Faturadaki Ürün/Hizmet <span class="text-danger">*</span></label>
                            <select id="returnProductSelect" class="form-select" required>
                                <option value="">-- Ürün Seçin --</option>
                                @foreach($invoice->items()->where('is_return', false)->get() as $item)
                                    <option value="{{ $item->id }}" 
                                            data-product-id="{{ $item->product_id }}" 
                                            data-product-type="{{ $item->product_type }}"
                                            data-product-name="{{ $item->product_service_name }}"
                                            data-unit-price="{{ $item->unit_price }}"
                                            data-tax-rate="{{ $item->tax_rate }}"
                                            data-discount-rate="{{ $item->discount_rate }}"
                                            data-quantity="{{ $item->quantity }}"
                                            data-color="{{ $item->selected_color }}"
                                            data-color-variant-id="{{ $item->color_variant_id }}"
                                            data-description="{{ $item->description ?? '' }}">
                                        {{ $item->product_service_name }}
                                        @if($item->selected_color)
                                            - Renk: {{ $item->selected_color }}
                                        @endif
                                        (Miktar: {{ $item->quantity }}, Fiyat: {{ number_format($item->unit_price, 2) }} {{ $invoice->currency === 'USD' ? '$' : ($invoice->currency === 'EUR' ? '€' : '₺') }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" id="returnProductId" name="product_id">
                            <input type="hidden" id="returnProductType" name="type">
                            <input type="hidden" id="returnInvoiceItemId" name="invoice_item_id">
                            <small class="text-muted">Faturada bulunan ürünlerden birini seçin</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">İade Miktarı <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="returnQuantity" class="form-control" step="0.01" min="0.01" max="0" required>
                            <small class="text-muted" id="returnMaxQuantityHint">Max: <span id="returnMaxQuantity">0</span></small>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="alert alert-light border">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <strong>Birim Fiyat:</strong> <span id="displayUnitPrice">-</span> {{ $invoice->currency === 'USD' ? '$' : ($invoice->currency === 'EUR' ? '€' : '₺') }}
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label mb-0"><strong>KDV Oranı</strong></label>
                                        <select id="returnTaxRate" name="tax_rate" class="form-select form-select-sm" style="max-width: 120px; display: inline-block;">
                                            <option value="0">%0</option>
                                            <option value="1">%1</option>
                                            <option value="10">%10</option>
                                            <option value="20">%20</option>
                                        </select>
                                    </div>
                                    @if(false)
                                    <div class="col-md-6">
                                        <strong>Renk:</strong> <span id="displayColor">-</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" name="unit_price" id="returnUnitPrice">
                            <input type="hidden" name="discount_rate" id="returnDiscount" value="0">
                            <input type="hidden" name="selected_color" id="returnSelectedColor">
                            <input type="hidden" name="color_variant_id" id="returnColorVariantId">
                            <input type="hidden" name="description" id="returnDescription">
                        </div>
                    </div>
                    
                    <div class="mt-3 p-3 bg-light rounded" id="returnItemSummary" style="display: none;">
                        <h6 class="fw-semibold mb-2">Özet:</h6>
                        <div id="returnItemSummaryContent"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger">
                        <iconify-icon icon="solar:refresh-outline" class="me-2"></iconify-icon>
                        İade Ekle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const invoiceCurrency = '{{ $invoice->currency }}';
    const currencySymbol = invoiceCurrency === 'USD' ? '$' : (invoiceCurrency === 'EUR' ? '€' : '₺');
    
    let selectedReturnItem = null;
    
    // Ürün seçimi (dropdown'dan)
    $('#returnProductSelect').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        
        if (selectedOption.val() === '') {
            // Seçim temizlendi
            resetReturnForm();
            return;
        }
        
        // Seçilen ürün bilgilerini al
        selectedReturnItem = {
            invoiceItemId: selectedOption.val(),
            productId: selectedOption.data('product-id'),
            productType: selectedOption.data('product-type'),
            productName: selectedOption.data('product-name'),
            unitPrice: parseFloat(selectedOption.data('unit-price')) || 0,
            taxRate: (selectedOption.data('tax-rate') !== undefined && selectedOption.data('tax-rate') !== null)
                ? parseFloat(selectedOption.data('tax-rate'))
                : 0,
            discountRate: parseFloat(selectedOption.data('discount-rate')) || 0,
            maxQuantity: parseFloat(selectedOption.data('quantity')) || 0,
            selectedColor: selectedOption.data('color') || '',
            colorVariantId: selectedOption.data('color-variant-id') || '',
            description: selectedOption.data('description') || ''
        };
        
        // Form alanlarını doldur
        $('#returnInvoiceItemId').val(selectedReturnItem.invoiceItemId);
        $('#returnProductId').val(selectedReturnItem.productId);
        $('#returnProductType').val(selectedReturnItem.productType);
        $('#returnUnitPrice').val(selectedReturnItem.unitPrice);
        $('#returnTaxRate').val(selectedReturnItem.taxRate);
        $('#returnDiscount').val(selectedReturnItem.discountRate);
        $('#returnDescription').val('İade - ' + selectedReturnItem.productName);
        $('#returnQuantity').attr('max', selectedReturnItem.maxQuantity).attr('placeholder', 'Max: ' + selectedReturnItem.maxQuantity);
        $('#returnMaxQuantity').text(selectedReturnItem.maxQuantity);
        
        // Görüntüleme alanlarını doldur
        $('#displayUnitPrice').text(selectedReturnItem.unitPrice.toLocaleString('tr-TR', {minimumFractionDigits: 2}));
        $('#returnTaxRate').val(selectedReturnItem.taxRate ?? 0).trigger('change');
        if (selectedReturnItem.selectedColor) {
            $('#displayColor').text(selectedReturnItem.selectedColor);
            $('#returnSelectedColor').val(selectedReturnItem.selectedColor);
            $('#returnColorVariantId').val(selectedReturnItem.colorVariantId);
        } else {
            $('#displayColor').text('Yok');
            $('#returnSelectedColor').val('');
            $('#returnColorVariantId').val('');
        }
        
        updateReturnSummary();
    });
    
    // Hesaplama
    $('#returnQuantity, #returnTaxRate').on('input change', function() {
        // Max miktar kontrolü
        const maxQty = parseFloat($('#returnQuantity').attr('max')) || 0;
        const enteredQty = parseFloat($('#returnQuantity').val()) || 0;
        if (enteredQty > maxQty) {
            $('#returnQuantity').val(maxQty);
            alert(`Maksimum iade miktarı ${maxQty} adettir.`);
        }
        updateReturnSummary();
    });
    
    function resetReturnForm() {
        selectedReturnItem = null;
        $('#returnInvoiceItemId').val('');
        $('#returnProductId').val('');
        $('#returnProductType').val('');
        $('#returnUnitPrice').val('');
        $('#returnTaxRate').val(0);
        $('#returnDiscount').val(0);
        $('#returnDescription').val('');
        $('#returnQuantity').val('').attr('max', 0).attr('placeholder', '');
        $('#returnMaxQuantity').text(0);
        $('#returnSelectedColor').val('');
        $('#returnColorVariantId').val('');
        $('#displayUnitPrice').text('-');
        $('#returnTaxRate').val(0);
        $('#displayColor').text('-');
        $('#returnItemSummary').hide();
    }
    
    function updateReturnSummary() {
        const quantity = parseFloat($('#returnQuantity').val()) || 0;
        const unitPrice = parseFloat($('#returnUnitPrice').val()) || 0;
        const taxRate = parseFloat($('#returnTaxRate').val()) || 0;
        const discount = parseFloat($('#returnDiscount').val()) || 0;
        
        if (quantity > 0 && unitPrice > 0) {
            const lineTotal = quantity * unitPrice;
            const discountAmount = discount;
            const afterDiscount = Math.max(0, lineTotal - discountAmount);
            const taxAmount = afterDiscount * (taxRate / 100);
            const total = afterDiscount + taxAmount;
            
            $('#returnItemSummaryContent').html(`
                <div class="d-flex justify-content-between mb-1">
                    <span>Ara Toplam:</span>
                    <span>${lineTotal.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ${currencySymbol}</span>
                </div>
                ${discount > 0 ? `<div class="d-flex justify-content-between mb-1"><span>İndirim:</span><span class="text-danger">-${discount.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ${currencySymbol}</span></div>` : ''}
                <div class="d-flex justify-content-between mb-1">
                    <span>KDV (${taxRate}%):</span>
                    <span>${taxAmount.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ${currencySymbol}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>İade Tutarı:</span>
                    <span class="text-danger">-${total.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ${currencySymbol}</span>
                </div>
            `);
            $('#returnItemSummary').show();
        } else {
            $('#returnItemSummary').hide();
        }
    }
    
    // Modal açıldığında formu temizle
    $('#addReturnModal').on('show.bs.modal', function() {
        $('#returnItemForm')[0].reset();
        $('#returnProductSelect').val('').trigger('change');
        $('#returnItemSummary').hide();
        selectedReturnItem = null;
        resetReturnForm();
    });
    
    function updateReturnSummary() {
        if (!selectedReturnItem) {
            $('#returnItemSummary').hide();
            return;
        }
        
        const quantity = parseFloat($('#returnQuantity').val()) || 0;
        const unitPrice = parseFloat($('#returnUnitPrice').val()) || selectedReturnItem.unitPrice || 0;
        const taxRate = parseFloat($('#returnTaxRate').val()) || selectedReturnItem.taxRate || 20;
        const discount = parseFloat($('#returnDiscount').val()) || selectedReturnItem.discountRate || 0;
        const maxQty = selectedReturnItem.maxQuantity || 0;
        
        if (quantity > maxQty) {
            $('#returnQuantity').val(maxQty);
            return;
        }
        
        if (quantity > 0 && unitPrice > 0) {
            const lineTotal = quantity * unitPrice;
            const discountAmount = discount;
            const afterDiscount = Math.max(0, lineTotal - discountAmount);
            const taxAmount = afterDiscount * (taxRate / 100);
            const total = afterDiscount + taxAmount;
            
            $('#returnItemSummaryContent').html(`
                <div class="d-flex justify-content-between mb-1">
                    <span>Orijinal Miktar:</span>
                    <span>${maxQty.toLocaleString('tr-TR', {minimumFractionDigits: 2})} Adet</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>İade Miktarı:</span>
                    <span class="text-danger">${quantity.toLocaleString('tr-TR', {minimumFractionDigits: 2})} Adet</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-1">
                    <span>Ara Toplam:</span>
                    <span>${lineTotal.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ${currencySymbol}</span>
                </div>
                ${discount > 0 ? `<div class="d-flex justify-content-between mb-1"><span>İndirim:</span><span class="text-danger">-${discount.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ${currencySymbol}</span></div>` : ''}
                <div class="d-flex justify-content-between mb-1">
                    <span>KDV (${taxRate}%):</span>
                    <span>${taxAmount.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ${currencySymbol}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>İade Tutarı:</span>
                    <span class="text-danger">-${total.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ${currencySymbol}</span>
                </div>
            `);
            $('#returnItemSummary').show();
        } else {
            $('#returnItemSummary').hide();
        }
    }
});
</script>
@endpush
@endsection