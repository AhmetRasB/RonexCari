@extends('layout.layout')

@section('title', 'Alış Faturası Düzenle')
@section('subTitle', 'Alış Faturası Güncelle')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Alış Faturası Düzenle - {{ $invoice->invoice_number }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('purchases.invoices.update', $invoice) }}" method="POST" id="invoiceForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Supplier Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-semibold mb-3">Tedarikçi Bilgileri</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Tedarikçi <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" id="supplierSearch" class="form-control" placeholder="Tedarikçi ara..." autocomplete="off" value="{{ $invoice->supplier->name ?? '' }}">
                                        <input type="hidden" name="supplier_id" id="supplierId" value="{{ $invoice->supplier_id }}" required>
                                        <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                            <iconify-icon icon="ion:search-outline" class="text-secondary-light"></iconify-icon>
                                        </div>
                                        <div id="supplierDropdown" class="dropdown-menu w-100" style="display: none; max-height: 300px; overflow-y: auto; position: absolute; top: 100%; left: 0; z-index: 1020; background: white; border: 1px solid #dee2e6; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); transform: none !important;">
                                        </div>
                                    </div>
                                    <div id="supplierInfo" class="mt-2" style="display: block;">
                                        <div class="text-success"><iconify-icon icon="solar:check-circle-outline" class="me-1"></iconify-icon>{{ $invoice->supplier->name ?? '' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Fatura Tarihi <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="date" name="invoice_date" class="form-control" value="{{ $invoice->invoice_date->format('Y-m-d') }}" required>
                                        <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                            <iconify-icon icon="solar:calendar-outline" class="text-secondary-light"></iconify-icon>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Fatura Saati <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="time" name="invoice_time" class="form-control" value="{{ $invoice->invoice_time ?? \Carbon\Carbon::now()->format('H:i') }}" required>
                                        <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                            <iconify-icon icon="solar:clock-circle-outline" class="text-secondary-light"></iconify-icon>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-semibold mb-3">Fatura Detayları</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Vade Tarihi <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="date" name="due_date" class="form-control" value="{{ $invoice->due_date->format('Y-m-d') }}" required>
                                        <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                            <iconify-icon icon="solar:calendar-outline" class="text-secondary-light"></iconify-icon>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Para Birimi</label>
                                    <select name="currency" id="currency" class="form-select">
                                        <option value="TRY" {{ ($invoice->currency ?? 'TRY') == 'TRY' ? 'selected' : '' }}>₺ TRY</option>
                                        <option value="USD" {{ ($invoice->currency ?? 'TRY') == 'USD' ? 'selected' : '' }}>$ USD</option>
                                        <option value="EUR" {{ ($invoice->currency ?? 'TRY') == 'EUR' ? 'selected' : '' }}>€ EUR</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">KDV Durumu</label>
                                    <select name="vat_status" class="form-select">
                                        <option value="included" {{ ($invoice->vat_status ?? 'included') == 'included' ? 'selected' : '' }}>Dahil</option>
                                        <option value="excluded" {{ ($invoice->vat_status ?? 'included') == 'excluded' ? 'selected' : '' }}>Hariç</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check mt-4">
                                        <input type="hidden" name="payment_completed" value="0">
                                        <input class="form-check-input" type="checkbox" id="paymentCompleted" name="payment_completed" value="1" {{ $invoice->payment_completed ? 'checked' : '' }}>
                                        <label class="form-check-label" for="paymentCompleted">
                                            <iconify-icon icon="solar:check-circle-outline" class="me-2"></iconify-icon>
                                            Ödeme Yapıldı
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label class="form-label">Açıklama</label>
                                    <textarea name="description" class="form-control" rows="2" placeholder="Fatura açıklaması...">{{ $invoice->description ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-semibold mb-3">Ürün/Hizmet Detayları</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="invoiceItemsTable">
                                    <thead>
                                        <tr id="invoiceTableHeader">
                                            <th style="min-width: 250px;">ÜRÜN/HİZMET</th>
                                            <th style="min-width: 200px;">AÇIKLAMA</th>
                                            <th style="min-width: 150px;">MİKTAR</th>
                                            <th style="min-width: 180px;">B. FİYAT</th>
                                            <th style="min-width: 120px;">KDV</th>
                                            <th style="min-width: 120px;">İNDİRİM</th>
                                            <th style="min-width: 150px;">TOPLAM</th>
                                            <th style="min-width: 80px;">İŞLEM</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoiceItemsBody">
                                        @foreach($invoice->items as $item)
                                        <tr data-item-index="{{ $loop->index }}" data-existing-item-id="{{ $item->id }}">
                                            <td>
                                                <input type="text" name="items[{{ $loop->index }}][product_service_name]" class="form-control product-service-search" placeholder="Ürün/Hizmet ara..." value="{{ $item->product_service_name }}" required>
                                            </td>
                                            <td>
                                                <textarea name="items[{{ $loop->index }}][description]" class="form-control" rows="2">{{ $item->description ?? '' }}</textarea>
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $loop->index }}][quantity]" class="form-control" value="{{ $item->quantity }}" min="0.01" step="0.01" required>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="number" name="items[{{ $loop->index }}][unit_price]" class="form-control unit-price" value="{{ $item->unit_price }}" min="0" step="0.01" required>
                                                    <select name="items[{{ $loop->index }}][unit_currency]" class="form-select unit-currency" style="max-width: 80px;">
                                                        <option value="TRY" {{ ($item->unit_currency ?? 'TRY') == 'TRY' ? 'selected' : '' }}>₺</option>
                                                        <option value="USD" {{ ($item->unit_currency ?? 'TRY') == 'USD' ? 'selected' : '' }}>$</option>
                                                        <option value="EUR" {{ ($item->unit_currency ?? 'TRY') == 'EUR' ? 'selected' : '' }}>€</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <select name="items[{{ $loop->index }}][tax_rate]" class="form-select tax-rate">
                                                    <option value="0" {{ $item->tax_rate == 0 ? 'selected' : '' }}>KDV %0</option>
                                                    <option value="1" {{ $item->tax_rate == 1 ? 'selected' : '' }}>KDV %1</option>
                                                    <option value="10" {{ $item->tax_rate == 10 ? 'selected' : '' }}>KDV %10</option>
                                                    <option value="20" {{ $item->tax_rate == 20 ? 'selected' : '' }}>KDV %20</option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="number" name="items[{{ $loop->index }}][discount_rate]" class="form-control discount-rate" value="{{ $item->discount_rate ?? 0 }}" min="0" max="100" step="0.01">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" class="form-control line-total" readonly value="{{ number_format($item->total, 2, ',', '') }}">
                                                    <span class="input-group-text invoice-currency-symbol">{{ $invoice->currency == 'USD' ? '$' : ($invoice->currency == 'EUR' ? '€' : '₺') }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                                                    <iconify-icon icon="solar:trash-bin-minimalistic-outline"></iconify-icon>
                                                </button>
                                            </td>
                                            <input type="hidden" name="items[{{ $loop->index }}][product_id]" value="{{ $item->product_id ?? '' }}">
                                            <input type="hidden" name="items[{{ $loop->index }}][type]" value="{{ $item->product_type ?? '' }}">
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-outline-primary" id="addInvoiceItem">
                                <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                                Yeni Satır Ekle
                            </button>
                        </div>
                    </div>

                    <!-- Invoice Totals -->
                    <div class="row">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="fw-semibold mb-3">Fatura Toplamları</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Ara Toplam:</span>
                                        <span id="subtotal">0,00 ₺</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>İndirim:</span>
                                        <span id="discount">0,00 ₺</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>KDV:</span>
                                        <span id="vatAmount">0,00 ₺</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Genel Toplam:</span>
                                        <span id="totalAmount">0,00 ₺</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <iconify-icon icon="solar:check-circle-outline" class="me-2"></iconify-icon>
                                Güncelle
                            </button>
                            <a href="{{ route('purchases.invoices.index') }}" class="btn btn-secondary ms-2">
                                <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                                Geri Dön
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let itemCounter = {{ count($invoice->items) }};

$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Initial calculation
    calculateTotals();
    
    // Supplier search
    $('#supplierSearch').on('input', function() {
        const query = $(this).val();
        if (query.length >= 2) {
            searchSuppliers(query);
        } else {
            $('#supplierDropdown').hide();
        }
    });
    
    // Add new item
    $('#addInvoiceItem').on('click', function() {
        addInvoiceItemRow();
    });
    
    // Remove item
    $(document).on('click', '.remove-item', function() {
        if ($('#invoiceItemsBody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateTotals();
        }
    });
    
    // Add event listeners for existing rows and load color data
    @php
    $hasAnyColorVariants = $invoice->items->whereNotNull('color_variant_id')->count() > 0;
    @endphp
    const hasAnyColorVariants = {{ $hasAnyColorVariants ? 'true' : 'false' }};
    
    // Add color column if any item has color variants
    if (hasAnyColorVariants) {
        addColorColumnToTable();
    }
    
    $('#invoiceItemsBody tr').each(function() {
        const row = $(this);
        row.find('.unit-price, .discount-rate, .tax-rate, .unit-currency').on('input change', calculateLineTotal);
        row.find('input[name*="[quantity]"]').on('input', calculateLineTotal);
    });
    
    // Load color variants for existing items
    @foreach($invoice->items as $index => $item)
    @if($item->product_id)
    (function() {
        const row = $('tr[data-item-index="{{ $index }}"]');
        const productId = {{ $item->product_id }};
        const itemColorVariantId = {{ $item->color_variant_id ?? 'null' }};
        
        if (productId && row.length > 0) {
            // Search for product to get color variants
            $.get('{{ route("sales.invoices.search.products") }}', { q: productId })
                .done(function(data) {
                    const product = data.find(p => p.product_id == productId);
                    
                    if (product && product.has_color_variants && product.color_variants) {
                        // Add color column if not exists
                        addColorColumnToTable();
                        
                        // Add or update color cell for this row
                        addColorCellToRow(row, product.color_variants);
                        
                        // Set selected color variant if exists
                        if (itemColorVariantId) {
                            row.find('.color-variant-select').val(itemColorVariantId);
                            // Set color name in hidden field
                            const selectedOption = row.find('.color-variant-select option:selected');
                            const colorName = selectedOption.text().split(' (')[0];
                            row.find('input[name*="[selected_color]"]').val(colorName);
                        }
                    }
                });
        }
    })();
    @endif
    @endforeach
});

function searchSuppliers(query) {
    $.get('{{ route("purchases.invoices.search.suppliers") }}', { q: query })
        .done(function(suppliers) {
            let html = '';
            if (suppliers.length > 0) {
                suppliers.forEach(function(supplier) {
                    html += `
                        <div class="dropdown-item supplier-option" data-supplier-id="${supplier.id}" data-supplier-name="${supplier.name}" style="cursor: pointer; padding: 8px 16px;">
                            <div class="fw-semibold">${supplier.name}</div>
                            ${supplier.company_name ? `<small class="text-secondary-light">${supplier.company_name}</small>` : ''}
                        </div>
                    `;
                });
            } else {
                html = '<div class="dropdown-item text-secondary-light">Tedarikçi bulunamadı</div>';
            }
            $('#supplierDropdown').html(html).show();
        });
}

$(document).on('click', '.supplier-option', function() {
    const supplierId = $(this).data('supplier-id');
    const supplierName = $(this).data('supplier-name');
    
    $('#supplierId').val(supplierId);
    $('#supplierSearch').val(supplierName);
    $('#supplierDropdown').hide();
    
    $('#supplierInfo').html(`<div class="text-success"><iconify-icon icon="solar:check-circle-outline" class="me-1"></iconify-icon>${supplierName}</div>`).show();
});

function addInvoiceItemRow() {
    // Check if color column exists in table header
    const hasColorColumn = $('#invoiceTableHeader th:contains("RENK")').length > 0;
    
    const rowHtml = `
        <tr data-item-index="${itemCounter}">
            <td>
                <input type="text" name="items[${itemCounter}][product_service_name]" class="form-control product-service-search" placeholder="Ürün/Hizmet ara..." required>
            </td>
            <td>
                <textarea name="items[${itemCounter}][description]" class="form-control" rows="2"></textarea>
            </td>
            ${hasColorColumn ? `
            <td class="color-cell">
                <select name="items[${itemCounter}][color_variant_id]" class="form-select color-variant-select">
                    <option value="">Renk Seçin</option>
                </select>
                <input type="hidden" name="items[${itemCounter}][selected_color]" value="">
            </td>
            ` : ''}
            <td>
                <input type="number" name="items[${itemCounter}][quantity]" class="form-control" value="1" min="0.01" step="0.01" required>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" name="items[${itemCounter}][unit_price]" class="form-control unit-price" value="0" min="0" step="0.01" required>
                    <select name="items[${itemCounter}][unit_currency]" class="form-select unit-currency" style="max-width: 80px;">
                        <option value="TRY" selected>₺</option>
                        <option value="USD">$</option>
                        <option value="EUR">€</option>
                    </select>
                </div>
            </td>
            <td>
                <select name="items[${itemCounter}][tax_rate]" class="form-select tax-rate">
                    <option value="0">KDV %0</option>
                    <option value="1">KDV %1</option>
                    <option value="10">KDV %10</option>
                    <option value="20" selected>KDV %20</option>
                </select>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" name="items[${itemCounter}][discount_rate]" class="form-control discount-rate" value="0" min="0" max="100" step="0.01">
                    <span class="input-group-text">%</span>
                </div>
            </td>
            <td>
                <div class="input-group">
                    <input type="text" class="form-control line-total" readonly value="0,00">
                    <span class="input-group-text invoice-currency-symbol">₺</span>
                </div>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                    <iconify-icon icon="solar:trash-bin-minimalistic-outline"></iconify-icon>
                </button>
            </td>
            <input type="hidden" name="items[${itemCounter}][product_id]" value="">
            <input type="hidden" name="items[${itemCounter}][type]" value="">
        </tr>
    `;
    
    $('#invoiceItemsBody').append(rowHtml);
    itemCounter++;
    
    const newRow = $(`tr[data-item-index="${itemCounter - 1}"]`);
    newRow.find('.unit-price, .discount-rate, .tax-rate, .unit-currency').on('input change', calculateLineTotal);
    newRow.find('input[name*="[quantity]"]').on('input', calculateLineTotal);
}

function calculateLineTotal() {
    const row = $(this).closest('tr');
    const quantity = parseFloat(row.find('input[name*="[quantity]"]').val()) || 0;
    const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
    const discountRate = parseFloat(row.find('.discount-rate').val()) || 0;
    
    const lineTotal = quantity * unitPrice;
    const discountAmount = lineTotal * (discountRate / 100);
    const lineTotalAfterDiscount = lineTotal - discountAmount;
    
    row.find('.line-total').val(lineTotalAfterDiscount.toFixed(2).replace('.', ','));
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let totalDiscount = 0;
    let totalVat = 0;
    
    $('#invoiceItemsBody tr').each(function() {
        const lineTotal = parseFloat($(this).find('.line-total').val().replace(',', '.')) || 0;
        const discountRate = parseFloat($(this).find('.discount-rate').val()) || 0;
        const taxRate = parseFloat($(this).find('.tax-rate').val()) || 0;
        
        const discountAmount = lineTotal * (discountRate / 100);
        const lineTotalAfterDiscount = lineTotal - discountAmount;
        
        subtotal += lineTotalAfterDiscount;
        totalDiscount += discountAmount;
        
        if ($('select[name="vat_status"]').val() === 'included') {
            totalVat += lineTotalAfterDiscount * (taxRate / 100);
        }
    });
    
    const totalAmount = subtotal + totalVat;
    const currencySymbol = $('#currency').val() === 'USD' ? '$' : $('#currency').val() === 'EUR' ? '€' : '₺';
    
    $('#subtotal').text(subtotal.toFixed(2).replace('.', ',') + ' ' + currencySymbol);
    $('#discount').text(totalDiscount.toFixed(2).replace('.', ',') + ' ' + currencySymbol);
    $('#vatAmount').text(totalVat.toFixed(2).replace('.', ',') + ' ' + currencySymbol);
    $('#totalAmount').text(totalAmount.toFixed(2).replace('.', ',') + ' ' + currencySymbol);
}

// Function to add color column to table header
function addColorColumnToTable() {
    const header = $('#invoiceTableHeader');
    if (header.find('th:contains("RENK")').length === 0) {
        // Insert color column after description column (2nd column)
        header.find('th:nth-child(2)').after('<th style="min-width: 150px;">RENK</th>');
        
        // Add color cells to all existing rows that don't have them
        $('#invoiceItemsBody tr').each(function() {
            const row = $(this);
            if (row.find('.color-cell').length === 0) {
                const rowIndex = row.data('item-index');
                row.find('td:nth-child(2)').after(`
                    <td class="color-cell">
                        <select name="items[${rowIndex}][color_variant_id]" class="form-select color-variant-select">
                            <option value="">Renk Seçin</option>
                        </select>
                        <input type="hidden" name="items[${rowIndex}][selected_color]" value="">
                    </td>
                `);
            }
        });
    }
}

// Function to add color cell to row
function addColorCellToRow(row, colorVariants) {
    const rowIndex = row.data('item-index');
    
    // Add color column to table header if not exists
    addColorColumnToTable();
    
    // Check if color cell already exists, if not add it
    if (row.find('.color-cell').length === 0) {
        // Insert color cell after description column (2nd column)
        row.find('td:nth-child(2)').after(`
            <td class="color-cell">
                <select name="items[${rowIndex}][color_variant_id]" class="form-select color-variant-select">
                    <option value="">Renk Seçin</option>
                </select>
                <input type="hidden" name="items[${rowIndex}][selected_color]" value="">
            </td>
        `);
    }
    
    // Show color cell
    row.find('.color-cell').show();
    
    // Populate color options
    const colorSelect = row.find('.color-variant-select');
    colorSelect.empty().append('<option value="">Renk Seçin</option>');
    
    colorVariants.forEach(function(variant) {
        const stockText = variant.stock_quantity ? ` (${variant.stock_quantity} adet)` : '';
        colorSelect.append(`<option value="${variant.id}" data-stock="${variant.stock_quantity}">${variant.color}${stockText}</option>`);
    });

    // Persist selected color name into hidden input for backend display
    colorSelect.off('change').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const colorName = selectedOption.length ? (selectedOption.text().split(' (')[0]) : '';
        row.find(`input[name="items[${rowIndex}][selected_color]"]`).val(colorName);
    });
}

$(document).on('click', function(e) {
    if (!$(e.target).closest('#supplierSearch, #supplierDropdown').length) {
        $('#supplierDropdown').hide();
    }
});
</script>
@endpush
@endsection
