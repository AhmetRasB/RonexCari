@extends('layout.layout')

@section('title', 'Yeni Alış Faturası')
@section('subTitle', 'Alış Faturası Oluştur')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Yeni Alış Faturası</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('purchases.invoices.store') }}" method="POST" id="invoiceForm">
                    @csrf
                    
                    <!-- Supplier Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-semibold mb-3">Tedarikçi Bilgileri</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Tedarikçi <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" id="supplierSearch" class="form-control" placeholder="Tedarikçi ara..." autocomplete="off" data-bs-toggle="dropdown" data-bs-auto-close="false">
                                        <input type="hidden" name="supplier_id" id="supplierId" required>
                                        <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                            <iconify-icon icon="ion:search-outline" class="text-secondary-light"></iconify-icon>
                                        </div>
                                        <div id="supplierDropdown" class="dropdown-menu w-100" style="display: none; max-height: 300px; overflow-y: auto; position: absolute; top: 100%; left: 0; z-index: 1020; background: white; border: 1px solid #dee2e6; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); transform: none !important;">
                                            <!-- Supplier search results will be populated here -->
                                        </div>
                                    </div>
                                    <div id="supplierInfo" class="mt-2" style="display: none;">
                                        <!-- Selected supplier info will be shown here -->
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#supplierModal">
                                        <iconify-icon icon="solar:add-circle-outline" class="me-1"></iconify-icon>
                                        Yeni Tedarikçi Ekle
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Fatura Tarihi <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="date" name="invoice_date" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                                        <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                            <iconify-icon icon="solar:calendar-outline" class="text-secondary-light"></iconify-icon>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Fatura Saati <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="time" name="invoice_time" class="form-control" value="{{ \Carbon\Carbon::now()->format('H:i') }}" required>
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
                                        <input type="date" name="due_date" class="form-control" value="{{ \Carbon\Carbon::now()->addDays(30)->format('Y-m-d') }}" required>
                                        <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                            <iconify-icon icon="solar:calendar-outline" class="text-secondary-light"></iconify-icon>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Para Birimi</label>
                                    <select name="currency" id="currency" class="form-select">
                                        <option value="TRY" selected>₺ TRY</option>
                                        <option value="USD">$ USD</option>
                                        <option value="EUR">€ EUR</option>
                                    </select>
                                </div>
                                <div class="col-md-3" id="exchangeRateContainer" style="display: none;">
                                    <label class="form-label">Döviz Kuru</label>
                                    <input type="text" id="exchangeRate" class="form-control" readonly value="">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">KDV Durumu</label>
                                    <select name="vat_status" class="form-select">
                                        <option value="included" selected>Dahil</option>
                                        <option value="excluded">Hariç</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check mt-4">
                                        <input type="hidden" name="payment_completed" value="0">
                                        <input class="form-check-input" type="checkbox" id="paymentCompleted" name="payment_completed" value="1">
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
                                    <textarea name="description" class="form-control" rows="2" placeholder="Fatura açıklaması..."></textarea>
                                    <small class="text-secondary-light">
                                        <iconify-icon icon="solar:info-circle-outline" class="me-1"></iconify-icon>
                                        Faturanın alt kısmında açıklama olarak görünür
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-semibold mb-3">Ürün/Hizmet Detayları</h6>
                            <div class="table-responsive" style="overflow-x: auto; min-width: 100%;">
                                <table class="table table-bordered" id="invoiceItemsTable" style="min-width: 1500px; width: 100%;">
                                    <thead>
                                        <tr id="invoiceTableHeader">
                                            <th style="min-width: 250px; width: 20%;">ÜRÜN/HİZMET</th>
                                            <th style="min-width: 200px; width: 15%;">AÇIKLAMA</th>
                                            <th style="min-width: 180px; width: 12%;">MİKTAR</th>
                                            <th style="min-width: 200px; width: 15%;">B. FİYAT</th>
                                            <th style="min-width: 150px; width: 10%;">KDV</th>
                                            <th style="min-width: 150px; width: 10%;">İNDİRİM</th>
                                            <th style="min-width: 180px; width: 12%;">TOPLAM</th>
                                            <th style="min-width: 100px; width: 6%;">İŞLEM</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoiceItemsBody">
                                        <!-- Invoice items will be added here -->
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
                        <span>Ek İndirim:</span>
                        <span id="additionalDiscount">0,00 ₺</span>
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
                    <div id="foreignCurrencyTotals" style="display: none;">
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Döviz Kuru:</span>
                            <span id="exchangeRateDisplay">-</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Toplam (TL):</span>
                            <span id="totalAmountTRY">0,00 ₺</span>
                        </div>
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
                                Kaydet
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

<!-- Supplier Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1" aria-labelledby="supplierModalLabel" aria-hidden="true" style="z-index: 1055;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="max-height: 90vh; overflow-y: auto;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="supplierModalLabel">
                    <iconify-icon icon="solar:user-plus-outline" class="me-2"></iconify-icon>
                    Yeni Tedarikçi Ekle
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="newSupplierForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ad Soyad <span class="text-danger">*</span></label>
                            <input type="text" id="newSupplierName" class="form-control" placeholder="Tedarikçi adı ve soyadı" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Şirket Adı</label>
                            <input type="text" id="newSupplierCompany" class="form-control" placeholder="Şirket adı (opsiyonel)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">E-posta <span class="text-danger">*</span></label>
                            <input type="email" id="newSupplierEmail" class="form-control" placeholder="ornek@email.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telefon</label>
                            <input type="text" id="newSupplierPhone" class="form-control" placeholder="+90 555 123 4567">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Adres</label>
                            <textarea id="newSupplierAddress" class="form-control" rows="3" placeholder="Tedarikçi adresi (opsiyonel)"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top p-3" style="display: flex !important; justify-content: flex-end; gap: 10px; min-height: 60px; align-items: center;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="min-width: 100px;">
                    <iconify-icon icon="solar:close-circle-outline" class="me-1"></iconify-icon>
                    Kapat
                </button>
                <button type="button" class="btn btn-primary" id="saveNewSupplier" style="min-width: 100px;">
                    <iconify-icon icon="solar:check-circle-outline" class="me-1"></iconify-icon>
                    Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
/* Fix dropdown positioning */
#supplierDropdown {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    right: 0 !important;
    transform: none !important;
    margin-top: 0 !important;
}

/* Ensure parent container has relative positioning */
.position-relative {
    position: relative !important;
}

/* Mobile Responsive Invoice Table */
@media screen and (max-width: 768px) {
    /* Make table horizontally scrollable on mobile */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Ensure table has minimum width for readability */
    #invoiceItemsTable {
        min-width: 1200px;
    }
    
    /* Make table cells more readable on mobile */
    #invoiceItemsTable th,
    #invoiceItemsTable td {
        min-width: 120px;
        padding: 12px 8px;
        font-size: 0.9rem;
        white-space: nowrap;
    }
    
    /* Specific column widths for better mobile experience - MUCH WIDER */
    #invoiceItemsTable th:nth-child(1), /* ÜRÜN/HİZMET */
    #invoiceItemsTable td:nth-child(1) {
        min-width: 200px;
        max-width: 200px;
    }
    
    #invoiceItemsTable th:nth-child(2), /* AÇIKLAMA */
    #invoiceItemsTable td:nth-child(2) {
        min-width: 180px;
        max-width: 180px;
    }
    
    #invoiceItemsTable th:nth-child(3), /* MİKTAR */
    #invoiceItemsTable td:nth-child(3) {
        min-width: 120px;
        max-width: 120px;
    }
    
    #invoiceItemsTable th:nth-child(4), /* B. FİYAT */
    #invoiceItemsTable td:nth-child(4) {
        min-width: 150px;
        max-width: 150px;
    }
    
    #invoiceItemsTable th:nth-child(5), /* KDV */
    #invoiceItemsTable td:nth-child(5) {
        min-width: 120px;
        max-width: 120px;
    }
    
    #invoiceItemsTable th:nth-child(6), /* İNDİRİM */
    #invoiceItemsTable td:nth-child(6) {
        min-width: 120px;
        max-width: 120px;
    }
    
    #invoiceItemsTable th:nth-child(7), /* TOPLAM */
    #invoiceItemsTable td:nth-child(7) {
        min-width: 140px;
        max-width: 140px;
    }
    
    #invoiceItemsTable th:nth-child(8), /* İŞLEM */
    #invoiceItemsTable td:nth-child(8) {
        min-width: 100px;
        max-width: 100px;
    }
    
    /* Make form inputs more mobile-friendly */
    .form-control, .form-select {
        font-size: 16px; /* Prevent zoom on iOS */
        padding: 0.5rem 0.75rem;
    }
    
    /* Adjust input groups for mobile */
    .input-group .form-control,
    .input-group .form-select {
        font-size: 14px;
    }
    
    .input-group-text {
        font-size: 12px;
        padding: 0.375rem 0.5rem;
    }
    
    /* Make buttons more touch-friendly */
    .btn {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    /* Adjust card padding for mobile */
    .card-body {
        padding: 1rem;
    }
    
    /* Make totals card more mobile-friendly */
    .col-md-4 .card {
        margin-top: 1rem;
    }
    
    /* Adjust modal for mobile */
    .modal-dialog {
        margin: 0.5rem;
        max-width: calc(100% - 1rem);
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    /* Make dropdown menus more mobile-friendly */
    .dropdown-menu {
        font-size: 0.875rem;
        max-height: 300px;
        overflow-y: auto;
        position: absolute !important;
        top: 100% !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 1020 !important;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        background: white;
        margin-top: 2px;
    }
    
    /* Product search dropdown specific styles */
    .product-service-dropdown {
        position: absolute !important;
        top: 100% !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 1020 !important;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        max-height: 300px;
        overflow-y: auto;
        margin-top: 2px;
    }
    
    /* Ensure dropdown is visible on mobile */
    @media (max-width: 768px) {
        .dropdown-menu,
        .product-service-dropdown {
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1020 !important;
            max-height: 250px;
        }
    }
    
    .dropdown-item {
        padding: 0.5rem 0.75rem;
    }
}

/* Extra small screens */
@media screen and (max-width: 576px) {
    /* Further reduce padding and font sizes */
    #invoiceItemsTable th,
    #invoiceItemsTable td {
        padding: 6px 3px;
        font-size: 0.8rem;
    }
    
    .form-control, .form-select {
        font-size: 16px;
        padding: 0.4rem 0.6rem;
    }
    
    .btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
    }
    
    /* Stack form elements vertically on very small screens */
    .row .col-md-3,
    .row .col-md-6 {
        margin-bottom: 0.5rem;
    }
}

/* Landscape mobile orientation */
@media screen and (max-width: 768px) and (orientation: landscape) {
    #invoiceItemsTable {
        min-width: 1400px; /* Much wider for landscape */
    }
    
    .table-responsive {
        max-height: 60vh;
        overflow-y: auto;
    }
}
</style>

<script>
let itemCounter = 0;

$(document).ready(function() {
    
    // Setup CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Add first invoice item row
    addInvoiceItemRow();
    
    // Initialize exchange rate if currency is not TRY
    const initialCurrency = $('#currency').val();
    if (initialCurrency !== 'TRY') {
        updateExchangeRate(initialCurrency);
    }
    
    // Supplier search functionality
    $('#supplierSearch').on('input', function() {
        const query = $(this).val();
        console.log('Supplier search input:', query);
        if (query.length >= 2) {
            searchSuppliers(query);
        } else {
            $('#supplierDropdown').hide();
        }
    });
    
    // Also trigger search on focus if there's text
    $('#supplierSearch').on('focus', function() {
        const query = $(this).val();
        if (query.length >= 2) {
            searchSuppliers(query);
        }
    });
    
    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#supplierSearch, #supplierDropdown').length) {
            $('#supplierDropdown').hide();
        }
    });
    
    // Add new item row button
    $('#addInvoiceItem').on('click', function() {
        console.log('Add invoice item button clicked');
        addInvoiceItemRow();
    });
    
    // Remove item row
    $(document).on('click', '.remove-item', function() {
        if ($('#invoiceItemsBody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateTotals();
        }
    });
    
    // Save new supplier
    $('#saveNewSupplier').on('click', function() {
        console.log('Save new supplier button clicked');
        saveNewSupplier();
    });
    
    // Ensure modal is properly initialized
    $('#supplierModal').on('shown.bs.modal', function() {
        console.log('Supplier modal shown');
        console.log('Modal footer exists:', $('#supplierModal .modal-footer').length > 0);
        console.log('Save button exists:', $('#saveNewSupplier').length > 0);
        // Focus on first input
        $('#newSupplierName').focus();
    });
    
    // Clear form when modal is hidden
    $('#supplierModal').on('hidden.bs.modal', function() {
        console.log('Supplier modal hidden');
        $('#newSupplierForm')[0].reset();
    });
    
    // Currency change handler
    $('#currency').on('change', function() {
        const selectedCurrency = $(this).val();
        const exchangeRateContainer = $('#exchangeRateContainer');
        const foreignCurrencyTotals = $('#foreignCurrencyTotals');
        
        if (selectedCurrency === 'TRY') {
            exchangeRateContainer.hide();
            foreignCurrencyTotals.hide();
            updateCurrencySymbols('₺');
        } else {
            exchangeRateContainer.show();
            foreignCurrencyTotals.show();
            updateCurrencySymbols(selectedCurrency === 'USD' ? '$' : '€');
            updateExchangeRate(selectedCurrency);
        }
        
        // Wait a bit for exchange rate to update, then calculate totals
        setTimeout(function() {
            calculateTotals();
        }, 500);
    });
    
    // Form validation
    $('#invoiceForm').on('submit', function(e) {
        console.log('Form submit triggered');
        console.log('Supplier ID:', $('#supplierId').val());
        console.log('Invoice items count:', $('#invoiceItemsBody tr').length);
        
        if ($('#supplierId').val() === '') {
            e.preventDefault();
            alert('Lütfen bir tedarikçi seçin.');
            return false;
        }
        
        if ($('#invoiceItemsBody tr').length === 0) {
            e.preventDefault();
            alert('En az bir ürün/hizmet eklemelisiniz.');
            return false;
        }
        
        // Stock validation
        let hasStockError = false;
        let errorMessages = [];
        
        $('tbody tr').each(function() {
            const row = $(this);
            const quantity = parseFloat(row.find('input[name*="[quantity]"]').val()) || 0;
            const productType = row.data('product-type') || 'product';
            const productName = row.find('input[name*="[product_service_name]"]').val() || 'Ürün';
            
            // Check if product has color variants
            const colorSelect = row.find('.color-variant-select');
            let stockQuantity = 0;
            let selectedColor = '';
            
            if (colorSelect.length > 0 && colorSelect.val()) {
                // Color variant selected - use color-specific stock
                const selectedOption = colorSelect.find('option:selected');
                stockQuantity = parseInt(selectedOption.data('stock')) || 0;
                selectedColor = selectedOption.text().split(' (')[0]; // Get color name without stock info
            } else {
                // No color variant - use general stock
                stockQuantity = row.data('stock-quantity') || 0;
            }
            
            if (quantity > 0 && stockQuantity > 0 && quantity > stockQuantity) {
                hasStockError = true;
                const colorInfo = selectedColor ? ` (${selectedColor} rengi)` : '';
                errorMessages.push(`${productName}${colorInfo}: Stokta ${stockQuantity} ${productType === 'series' ? 'seri' : 'adet'} var, ${quantity} ${productType === 'series' ? 'seri' : 'adet'} isteniyor.`);
            }
        });
        
        if (hasStockError) {
            e.preventDefault();
            alert('Yetersiz Stok Uyarısı:\n\n' + errorMessages.join('\n') + '\n\nLütfen miktarları kontrol edin.');
            return false;
        }
        
        // Update form with calculated values before submit
        updateFormWithCalculatedValues();
        
        console.log('Form validation passed, submitting...');
    });
});

function searchSuppliers(query) {
    console.log('Searching suppliers with query:', query);
    console.log('Search URL:', '{{ route("purchases.invoices.search.suppliers") }}');
    
    $.get('{{ route("purchases.invoices.search.suppliers") }}', { q: query })
        .done(function(suppliers) {
            console.log('Suppliers found:', suppliers);
            let html = '';
            if (suppliers.length > 0) {
                suppliers.forEach(function(supplier) {
                    html += `
                        <div class="dropdown-item supplier-option" data-supplier-id="${supplier.id}" data-supplier-name="${supplier.name}" data-supplier-company="${supplier.company_name || ''}" data-supplier-email="${supplier.email || ''}" data-supplier-phone="${supplier.phone || ''}" style="cursor: pointer; padding: 8px 16px;">
                            <div class="fw-semibold">${supplier.name}</div>
                            ${supplier.company_name ? `<small class="text-secondary-light">${supplier.company_name}</small>` : ''}
                        </div>
                    `;
                });
            } else {
                html = '<div class="dropdown-item text-secondary-light" style="padding: 8px 16px;">Tedarikçi bulunamadı</div>';
            }
            $('#supplierDropdown').html(html).show();
            // Ensure dropdown is positioned correctly
            $('#supplierDropdown').css({
                'position': 'absolute',
                'top': '100%',
                'left': '0',
                'right': '0',
                'transform': 'none',
                'margin-top': '0'
            });
            console.log('Dropdown shown with HTML:', html);
        })
        .fail(function(xhr, status, error) {
            console.error('Supplier search failed:', xhr.responseText, status, error);
            $('#supplierDropdown').html('<div class="dropdown-item text-danger" style="padding: 8px 16px;">Arama sırasında hata oluştu</div>').show();
            // Ensure dropdown is positioned correctly
            $('#supplierDropdown').css({
                'position': 'absolute',
                'top': '100%',
                'left': '0',
                'right': '0',
                'transform': 'none',
                'margin-top': '0'
            });
        });
}

$(document).on('click', '.supplier-option', function() {
    console.log('Supplier option clicked');
    const supplierId = $(this).data('supplier-id');
    const supplierName = $(this).data('supplier-name');
    const supplierCompany = $(this).data('supplier-company');
    const supplierEmail = $(this).data('supplier-email');
    const supplierPhone = $(this).data('supplier-phone');
    
    console.log('Selected supplier:', { supplierId, supplierName, supplierCompany, supplierEmail, supplierPhone });
    
    $('#supplierId').val(supplierId);
    $('#supplierSearch').val(supplierName);
    $('#supplierDropdown').hide();
    
    // Show supplier info
    let infoHtml = `<div class="text-success"><iconify-icon icon="solar:check-circle-outline" class="me-1"></iconify-icon>${supplierName}`;
    if (supplierCompany) infoHtml += ` - ${supplierCompany}`;
    if (supplierEmail) infoHtml += ` (${supplierEmail})`;
    infoHtml += `</div>`;
    $('#supplierInfo').html(infoHtml).show();
    
    console.log('Supplier selected and info shown');
});

function addInvoiceItemRow() {
    console.log('Adding invoice item row, current counter:', itemCounter);
    const rowHtml = `
        <tr data-item-index="${itemCounter}">
            <td>
                <div class="position-relative">
                    <input type="text" name="items[${itemCounter}][product_service_name]" class="form-control product-service-search" placeholder="Ürün/Hizmet ara..." required style="min-height: 50px; font-size: 16px;">
                    <div id="productServiceDropdown${itemCounter}" class="dropdown-menu" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1020; max-height: 200px; overflow-y: auto;">
                        <!-- Search results will be populated here -->
                    </div>
                </div>
            </td>
            <td>
                <textarea name="items[${itemCounter}][description]" class="form-control" rows="3" placeholder="Açıklama" style="min-height: 60px; font-size: 14px;"></textarea>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" name="items[${itemCounter}][quantity]" class="form-control" value="1" min="0.01" step="0.01" required style="min-height: 50px; font-size: 16px;">
                    <span class="input-group-text" style="min-height: 50px; font-size: 14px;">Ad</span>
                </div>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" name="items[${itemCounter}][unit_price]" class="form-control unit-price" value="0" min="0" step="0.01" required style="min-height: 50px; font-size: 16px;">
                    <select name="items[${itemCounter}][unit_currency]" class="form-select unit-currency" style="max-width: 80px; min-height: 50px; font-size: 14px;">
                        <option value="TRY" ${$('#currency').val() === 'TRY' ? 'selected' : ''}>₺</option>
                        <option value="USD" ${$('#currency').val() === 'USD' ? 'selected' : ''}>$</option>
                        <option value="EUR" ${$('#currency').val() === 'EUR' ? 'selected' : ''}>€</option>
                    </select>
                </div>
            </td>
            <td>
                <select name="items[${itemCounter}][tax_rate]" class="form-select tax-rate" style="min-height: 50px; font-size: 14px;">
                    <option value="0">KDV %0</option>
                    <option value="1">KDV %1</option>
                    <option value="10">KDV %10</option>
                    <option value="20" selected>KDV %20</option>
                </select>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" name="items[${itemCounter}][discount_rate]" class="form-control discount-rate" value="0" min="0" max="100" step="0.01" style="min-height: 50px; font-size: 16px;">
                    <span class="input-group-text" style="min-height: 50px; font-size: 14px;">%</span>
                </div>
            </td>
            <td>
                <div class="input-group">
                    <input type="text" class="form-control line-total" readonly value="0,00" style="min-height: 50px; font-size: 16px;">
                    <span class="input-group-text invoice-currency-symbol" style="min-height: 50px; font-size: 14px;">${$('#currency').val() === 'USD' ? '$' : $('#currency').val() === 'EUR' ? '€' : '₺'}</span>
                </div>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                    <iconify-icon icon="solar:trash-bin-minimalistic-outline"></iconify-icon>
                </button>
            </td>
        </tr>
    `;
    
    $('#invoiceItemsBody').append(rowHtml);
    itemCounter++;
    
    // Add event listeners for calculations
    const newRow = $(`tr[data-item-index="${itemCounter - 1}"]`);
    newRow.find('.unit-price, .discount-rate, .tax-rate, .unit-currency').on('input change', calculateLineTotal);
    newRow.find('input[name*="[quantity]"]').on('input', calculateLineTotal);
}

function calculateLineTotal() {
    const row = $(this).closest('tr');
    const quantity = parseFloat(row.find('input[name*="[quantity]"]').val()) || 0;
    let unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
    const discountRate = parseFloat(row.find('.discount-rate').val()) || 0;
    const unitCurrency = row.find('.unit-currency').val();
    const invoiceCurrency = $('#currency').val();
    
    // Convert unit price from unit currency to invoice currency
    if (unitCurrency !== invoiceCurrency) {
        const exchangeRates = getExchangeRates();
        
        // Convert: unitPrice (in unit currency) -> TL -> invoice currency
        const priceInTL = unitPrice * exchangeRates[unitCurrency];
        unitPrice = priceInTL / exchangeRates[invoiceCurrency];
    }
    
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
        
        // Line total is already in invoice currency
        const discountAmount = lineTotal * (discountRate / 100);
        const lineTotalAfterDiscount = lineTotal - discountAmount;
        
        subtotal += lineTotalAfterDiscount;
        totalDiscount += discountAmount;
        
        if ($('select[name="vat_status"]').val() === 'included') {
            totalVat += lineTotalAfterDiscount * (taxRate / 100);
        }
    });
    
    const totalAmount = subtotal + totalVat;
    const selectedCurrency = $('#currency').val();
    const currencySymbol = selectedCurrency === 'USD' ? '$' : selectedCurrency === 'EUR' ? '€' : '₺';
    
    // Always show totals in the selected currency
    $('#subtotal').text(subtotal.toFixed(2).replace('.', ',') + ' ' + currencySymbol);
    $('#discount').text(totalDiscount.toFixed(2).replace('.', ',') + ' ' + currencySymbol);
    $('#additionalDiscount').text('0,00 ' + currencySymbol);
    $('#vatAmount').text(totalVat.toFixed(2).replace('.', ',') + ' ' + currencySymbol);
    $('#totalAmount').text(totalAmount.toFixed(2).replace('.', ',') + ' ' + currencySymbol);
    
    // Show TL equivalent if foreign currency is selected
    if (selectedCurrency !== 'TRY') {
        const exchangeRate = parseFloat($('#exchangeRate').val()) || 1;
        const totalAmountTRY = totalAmount * exchangeRate;
        $('#totalAmountTRY').text(totalAmountTRY.toFixed(2).replace('.', ',') + ' ₺');
        $('#exchangeRateDisplay').text(exchangeRate.toFixed(4));
    }
}

// Stock validation function
function validateStock(row) {
    const quantity = parseFloat(row.find('input[name*="[quantity]"]').val()) || 0;
    const productType = row.data('product-type') || 'product';
    const productName = row.find('input[name*="[product_service_name]"]').val() || 'Ürün';
    
    // Check if product has color variants
    const colorSelect = row.find('.color-variant-select');
    let stockQuantity = 0;
    let selectedColor = '';
    
    if (colorSelect.length > 0 && colorSelect.val()) {
        // Color variant selected - use color-specific stock
        const selectedOption = colorSelect.find('option:selected');
        stockQuantity = parseInt(selectedOption.data('stock')) || 0;
        selectedColor = selectedOption.text().split(' (')[0]; // Get color name without stock info
    } else {
        // No color variant - use general stock
        stockQuantity = row.data('stock-quantity') || 0;
    }
    
    // Clear previous warnings and styling
    row.find('.stock-warning').remove();
    row.next('.stock-warning-row').remove(); // Remove warning row below
    row.removeClass('table-danger');
    row.find('td').removeClass('bg-danger-subtle');
    
    if (quantity > 0 && stockQuantity > 0 && quantity > stockQuantity) {
        // Make entire row red
        row.addClass('table-danger');
        row.find('td').addClass('bg-danger-subtle');
        
        // Add warning message below the row
        const colorInfo = selectedColor ? ` (${selectedColor} rengi)` : '';
        const warningHtml = `
            <tr class="stock-warning-row">
                <td colspan="8" class="p-0">
                    <div class="stock-warning alert alert-danger alert-sm m-2" style="padding: 8px 12px; font-size: 0.875rem; border: none; border-radius: 6px;">
                        <i class="ri-alert-line me-2"></i>
                        <strong>YETERSİZ STOK!</strong> 
                        ${productType === 'series' ? 'Seri' : 'Ürün'}${colorInfo} stokta: ${stockQuantity} ${productType === 'series' ? 'seri' : 'adet'}, 
                        istenen: ${quantity} ${productType === 'series' ? 'seri' : 'adet'}
                    </div>
                </td>
            </tr>
        `;
        
        // Remove existing warning row if any
        row.next('.stock-warning-row').remove();
        // Add warning row after current row
        row.after(warningHtml);
        return false;
    }
    
    return true;
}

// Validate stock when quantity changes
$(document).on('input', 'input[name*="[quantity]"]', function() {
    const row = $(this).closest('tr');
    validateStock(row);
});

// Validate stock when product is selected
$(document).on('change', 'input[name*="[product_service_name]"]', function() {
    const row = $(this).closest('tr');
    setTimeout(() => validateStock(row), 100);
});

// Validate stock when color variant is selected
$(document).on('change', '.color-variant-select', function() {
    const row = $(this).closest('tr');
    validateStock(row);
});

function updateCurrencySymbols(symbol) {
    // Update invoice currency symbols in totals
    $('.invoice-currency-symbol').each(function() {
        $(this).text(symbol);
    });
}

function getExchangeRates() {
    // Get current exchange rates from TCMB
    const currentRates = {
        'TRY': 1,
        'USD': 41.29, // Current accurate rates
        'EUR': 48.55  // Current accurate rates
    };
    
    // Try to get real-time rates
    $.ajax({
        url: '{{ route("purchases.invoices.currency.rates") }}',
        async: false,
        success: function(response) {
            if (response.success && response.rates) {
                currentRates['USD'] = response.rates['USD'] || 41.29;
                currentRates['EUR'] = response.rates['EUR'] || 48.55;
            }
        }
    });
    
    return currentRates;
}

function updateExchangeRate(currency) {
    // TRY için döviz kuru 1 olarak ayarla
    if (currency === 'TRY') {
        $('#exchangeRate').val('1.0000');
        hideCurrencyLinks(); // TRY için linkleri gizle
        return;
    }
    
    // Her durumda kur linklerini göster
    showCurrencyLinks(currency);
    
    // Fetch real-time exchange rates from API
    $.get('{{ route("purchases.invoices.currency.rates") }}')
        .done(function(response) {
            if (response.success && response.rates[currency] !== null) {
                // API'den gelen değeri direkt döviz kuru alanına koy
                $('#exchangeRate').val(response.rates[currency].toFixed(4));
                console.log(`Exchange rate for ${currency}: ${response.rates[currency]}`);
                
                // API başarılı olsa bile düzenleme modalını göster
                showEditableRateModal(currency, response.rates[currency]);
            } else {
                // API'den null gelirse fallback değer koy ve modal göster
                console.log(`API returned null for ${currency}, showing manual input`);
                showManualRateInputForCurrency(currency);
            }
        })
        .fail(function() {
            console.error('Exchange rate API failed');
            // API başarısız olursa manuel input göster
            showManualRateInputForCurrency(currency);
        });
}

function showCurrencyLinks(currency) {
    const currencyLinks = {
        'USD': 'https://www.haremaltin.com/?lang=en',
        'EUR': 'https://www.haremaltin.com/?lang=en'
    };
    
    const link = currencyLinks[currency];
    if (link) {
        const linkHtml = `
            <div id="currencyLinks" class="mt-2">
                <small class="text-muted">Güncel ${currency} kurunu kontrol edin:</small><br>
                <a href="${link}" target="_blank" class="btn btn-outline-info btn-sm">
                    <iconify-icon icon="solar:external-link-outline" class="me-1"></iconify-icon>
                    ${currency} Canlı Kur - Harem Altın
                </a>
            </div>
        `;
        
        // Mevcut linkleri kaldır ve yeni ekle
        $('#currencyLinks').remove();
        $('#exchangeRate').parent().append(linkHtml);
    }
}

function hideCurrencyLinks() {
    $('#currencyLinks').remove();
}

function showEditableRateModal(currency, apiRate) {
    const currencyLinks = {
        'USD': 'https://www.haremaltin.com/?lang=en',
        'EUR': 'https://www.haremaltin.com/?lang=en'
    };
    
    const link = currencyLinks[currency];
    
    const modalHtml = `
        <div class="modal fade" id="editRateModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Döviz Kuru Düzenleme</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <iconify-icon icon="solar:check-circle-outline" class="me-2"></iconify-icon>
                            API'den ${currency} kuru başarıyla alındı: <strong>${apiRate.toFixed(4)}</strong>
                        </div>
                        
                        ${link ? `
                        <div class="mb-3">
                            <label class="form-label">Güncel Kuru Kontrol Edin:</label><br>
                            <a href="${link}" target="_blank" class="btn btn-outline-primary">
                                <iconify-icon icon="solar:external-link-outline" class="me-1"></iconify-icon>
                                ${currency} Canlı Kur - Harem Altın
                            </a>
                        </div>
                        ` : ''}
                        
                        <div class="mb-3">
                            <label for="editableRate" class="form-label">${currency} Kuru</label>
                            <input type="number" class="form-control" id="editableRate" 
                                   value="${apiRate.toFixed(4)}" step="0.0001" min="0">
                            <small class="form-text text-muted">API'den gelen kur otomatik yüklendi. İsterseniz düzenleyebilirsiniz.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">API Kurunu Kullan</button>
                        <button type="button" class="btn btn-primary" onclick="saveEditedRate('${currency}')">Düzenlenen Kuru Kullan</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if present
    $('#editRateModal').remove();
    
    // Add modal to body and show
    $('body').append(modalHtml);
    $('#editRateModal').modal('show');
}

function saveEditedRate(currency) {
    const editedRate = parseFloat($('#editableRate').val());
    
    if (isNaN(editedRate) || editedRate <= 0) {
        alert('Lütfen geçerli bir kur değeri girin.');
        return;
    }
    
    // Döviz kuru alanına düzenlenen değeri koy
    $('#exchangeRate').val(editedRate.toFixed(4));
    
    // Modal'ı kapat
    $('#editRateModal').modal('hide');
    
    console.log(`Edited rate set for ${currency}: ${editedRate}`);
    
    // Totalleri yeniden hesapla
    setTimeout(function() {
        calculateTotals();
    }, 100);
}

function showManualRateInputForCurrency(currency) {
    // Fallback rates as default values
    const fallbackRates = {
        'USD': 41.29,
        'EUR': 48.55
    };
    
    const fallbackRate = fallbackRates[currency] || 1;
    
    // Döviz kuru alanına fallback değeri koy
    $('#exchangeRate').val(fallbackRate.toFixed(4));
    
    // Modal göster
    const modalHtml = `
        <div class="modal fade" id="manualRateModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Döviz Kuru Girişi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-warning">
                            <iconify-icon icon="solar:info-circle-outline" class="me-2"></iconify-icon>
                            ${currency} için güncel kur alınamadı. Lütfen manuel olarak girin.
                        </p>
                        <div class="mb-3">
                            <label for="manualRate" class="form-label">${currency} Kuru</label>
                            <input type="number" class="form-control" id="manualRate" 
                                   value="${fallbackRate.toFixed(4)}" step="0.0001" min="0">
                            <small class="form-text text-muted">Güncel ${currency} kurunu girin</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="button" class="btn btn-primary" onclick="saveManualRate('${currency}')">Kaydet</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if present
    $('#manualRateModal').remove();
    
    // Add modal to body and show
    $('body').append(modalHtml);
    $('#manualRateModal').modal('show');
}

function saveManualRate(currency) {
    const manualRate = parseFloat($('#manualRate').val());
    
    if (isNaN(manualRate) || manualRate <= 0) {
        alert('Lütfen geçerli bir kur değeri girin.');
        return;
    }
    
    // Döviz kuru alanına manuel girilen değeri koy
    $('#exchangeRate').val(manualRate.toFixed(4));
    
    // Modal'ı kapat
    $('#manualRateModal').modal('hide');
    
    console.log(`Manual rate set for ${currency}: ${manualRate}`);
    
    // Totalleri yeniden hesapla
    setTimeout(function() {
        calculateTotals();
    }, 100);
}

function updateFormWithCalculatedValues() {
    // Update all unit prices with calculated values
    $('#invoiceItemsBody tr').each(function() {
        const row = $(this);
        const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
        const unitCurrency = row.find('.unit-currency').val();
        const invoiceCurrency = $('#currency').val();
        
        // Convert unit price from unit currency to invoice currency
        if (unitCurrency !== invoiceCurrency) {
            const exchangeRates = getExchangeRates();
            const priceInTL = unitPrice * exchangeRates[unitCurrency];
            const convertedPrice = priceInTL / exchangeRates[invoiceCurrency];
            
            // Update the unit price input with converted value
            row.find('.unit-price').val(convertedPrice.toFixed(2));
        }
    });
}

function saveNewSupplier() {
    console.log('saveNewSupplier function called');
    
    const name = $('#newSupplierName').val();
    const company = $('#newSupplierCompany').val();
    const email = $('#newSupplierEmail').val();
    const phone = $('#newSupplierPhone').val();
    const address = $('#newSupplierAddress').val();
    
    console.log('Supplier data:', { name, company, email, phone, address });
    
    if (!name || !email) {
        alert('Ad Soyad ve E-posta alanları zorunludur.');
        return;
    }
    
    console.log('Sending AJAX request to:', '{{ route("purchases.suppliers.store") }}');
    
    $.post('{{ route("purchases.suppliers.store") }}', {
        name: name,
        company_name: company,
        email: email,
        phone: phone,
        address: address,
        is_active: 1
    })
    .done(function(response) {
        console.log('Supplier created successfully:', response);
        
        // Close modal
        $('#supplierModal').modal('hide');
        
        // Clear form
        $('#newSupplierForm')[0].reset();
        
        // Set the new supplier as selected
        $('#supplierId').val(response.supplier.id);
        $('#supplierSearch').val(response.supplier.name);
        
        // Show supplier info
        let infoHtml = `<div class="text-success"><iconify-icon icon="solar:check-circle-outline" class="me-1"></iconify-icon>${response.supplier.name}`;
        if (response.supplier.company_name) infoHtml += ` - ${response.supplier.company_name}`;
        if (response.supplier.email) infoHtml += ` (${response.supplier.email})`;
        infoHtml += `</div>`;
        $('#supplierInfo').html(infoHtml).show();
        
        alert('Tedarikçi başarıyla eklendi!');
    })
    .fail(function(xhr, status, error) {
        console.error('Supplier creation failed:', xhr.responseText);
        console.error('Status:', status, 'Error:', error);
        
        let errorMessage = 'Tedarikçi eklenirken bir hata oluştu.';
        
        if (xhr.responseJSON && xhr.responseJSON.errors) {
            const errors = xhr.responseJSON.errors;
            const errorList = Object.values(errors).flat().join(', ');
            errorMessage = 'Hata: ' + errorList;
        }
        
        alert(errorMessage);
    });
}

// Product/Service search functionality
function searchProductsServices(query, rowIndex) {
    if (query.length < 2) {
        $(`#productServiceDropdown${rowIndex}`).hide();
        return;
    }
    
    $.get('{{ route("purchases.invoices.search.products") }}', { q: query })
        .done(function(data) {
            const dropdown = $(`#productServiceDropdown${rowIndex}`);
            dropdown.empty();
            
            if (data.length === 0) {
                dropdown.append('<div class="dropdown-item text-muted">Sonuç bulunamadı</div>');
            } else {
                data.forEach(function(item) {
                    let details = [];
                    
                    if (item.type === 'product') {
                        if (item.product_code) details.push(`Kod: ${item.product_code}`);
                        if (item.category) details.push(`Kategori: ${item.category}`);
                        if (item.brand) details.push(`Marka: ${item.brand}`);
                        if (item.size) details.push(`Beden: ${item.size}`);
                        if (item.color) details.push(`Renk: ${item.color}`);
                    } else {
                        if (item.code) details.push(`Kod: ${item.code}`);
                        if (item.category) details.push(`Kategori: ${item.category}`);
                    }
                    
                    const itemHtml = `
                        <div class="dropdown-item product-service-item" 
                             data-name="${item.name}" 
                             data-price="${item.price}" 
                             data-vat-rate="${item.vat_rate}"
                             data-type="${item.type}"
                             style="cursor: pointer;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <strong>${item.name}</strong>
                                    <br>
                                    <small class="text-muted">${item.type === 'product' ? 'Ürün' : 'Hizmet'}</small>
                                    ${details.length > 0 ? `<br><small class="text-secondary">${details.join(' • ')}</small>` : ''}
                                </div>
                                <div class="text-end ms-2">
                                    <span class="fw-semibold text-success">${parseFloat(item.price).toFixed(2)} ${item.currency || 'TRY'}</span>
                                    <br>
                                    <small class="text-muted">KDV %${item.vat_rate}</small>
                                    ${item.purchase_price ? `<br><small class="text-info">Alış: ${parseFloat(item.purchase_price).toFixed(2)} ${item.currency || 'TRY'}</small>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                    dropdown.append(itemHtml);
                });
            }
            
            // Position dropdown as separate box below the row
            const input = $(`.product-service-search[data-row="${rowIndex}"]`);
            if (input.length > 0) {
                const inputRect = input[0].getBoundingClientRect();
                const row = input.closest('tr');
                const rowRect = row[0].getBoundingClientRect();
                
                // Move dropdown to body for better positioning
                dropdown.appendTo('body');
                
                // Check if mobile
                const isMobile = window.innerWidth <= 768;
                const isTablet = window.innerWidth <= 1024 && window.innerWidth > 768;
                
                let dropdownConfig = {};
                
                if (isMobile) {
                    // Mobile positioning - above the table
                    const table = $('.table-responsive').first();
                    const tableRect = table.length > 0 ? table[0].getBoundingClientRect() : { top: 0 };
                    
                    dropdownConfig = {
                        'position': 'fixed',
                        'top': Math.max(20, tableRect.top - 10) + 'px',
                        'left': '2.5%',
                        'right': '2.5%',
                        'width': '95%',
                        'max-width': '450px',
                        'z-index': 1020,
                        'background': 'white',
                        'border': '2px solid #007bff',
                        'border-radius': '12px',
                        'box-shadow': '0 8px 24px rgba(0, 0, 0, 0.2)',
                        'max-height': '300px',
                        'overflow-y': 'auto',
                        'margin-top': '0',
                        'padding': '0'
                    };
                } else if (isTablet) {
                    // Tablet positioning
                    dropdownConfig = {
                        'position': 'fixed',
                        'top': (rowRect.bottom + 10) + 'px',
                        'left': Math.max(20, Math.min(inputRect.left, window.innerWidth - 470)) + 'px',
                        'width': '450px',
                        'z-index': 1020,
                        'background': 'white',
                        'border': '2px solid #007bff',
                        'border-radius': '8px',
                        'box-shadow': '0 4px 12px rgba(0, 0, 0, 0.15)',
                        'max-height': '400px',
                        'overflow-y': 'auto',
                        'margin-top': '0',
                        'padding': '0'
                    };
                } else {
                    // Desktop positioning
                    dropdownConfig = {
                        'position': 'fixed',
                        'top': (rowRect.bottom + 10) + 'px',
                        'left': Math.max(20, Math.min(inputRect.left, window.innerWidth - 520)) + 'px',
                        'width': '500px',
                        'z-index': 1020,
                        'background': 'white',
                        'border': '2px solid #007bff',
                        'border-radius': '8px',
                        'box-shadow': '0 4px 12px rgba(0, 0, 0, 0.15)',
                        'max-height': '350px',
                        'overflow-y': 'auto',
                        'margin-top': '0',
                        'padding': '0'
                    };
                }
                
                dropdown.css(dropdownConfig);
            }
            
            dropdown.show();
        })
        .fail(function() {
            console.error('Product/Service search failed');
        });
}

// Handle product/service selection
$(document).on('click', '.product-service-item', function() {
    const row = $(this).closest('tr');
    const rowIndex = row.data('item-index');
    
    const name = $(this).data('name');
    const price = $(this).data('price');
    const vatRate = $(this).data('vat-rate');
    
    // Set the product/service name
    row.find('input[name*="[product_service_name]"]').val(name);
    
    // Set the unit price
    row.find('input[name*="[unit_price]"]').val(price);
    
    // Set the tax rate
    row.find('select[name*="[tax_rate]"]').val(vatRate);
    
    // Hide dropdown
    $(`#productServiceDropdown${rowIndex}`).hide();
    
    // Recalculate totals
    calculateLineTotal.call(row.find('.unit-price')[0]);
});

// Handle product/service search input
$(document).on('input', '.product-service-search', function() {
    const query = $(this).val();
    const row = $(this).closest('tr');
    const rowIndex = row.data('item-index');
    
    searchProductsServices(query, rowIndex);
});

// Hide dropdowns when clicking outside
$(document).on('click', function(e) {
    if (!$(e.target).closest('.position-relative').length) {
        $('.dropdown-menu').hide();
    }
});
</script>
@endpush
@endsection

@push('styles')
<style>
.table-danger {
    background-color: #f8d7da !important;
    border-color: #f5c6cb !important;
}

.table-danger td {
    background-color: #f8d7da !important;
    border-color: #f5c6cb !important;
    color: #721c24 !important;
}

.table-danger input,
.table-danger select {
    background-color: #fff !important;
    border-color: #dc3545 !important;
    color: #721c24 !important;
}

.stock-warning-row {
    background-color: transparent !important;
}

.stock-warning-row td {
    background-color: transparent !important;
    border: none !important;
    padding: 0 !important;
}

.stock-warning {
    background-color: #dc3545 !important;
    color: white !important;
    border: none !important;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3) !important;
}

.stock-warning strong {
    color: white !important;
}
</style>
@endpush