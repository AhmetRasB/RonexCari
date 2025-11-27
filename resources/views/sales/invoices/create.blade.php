@extends('layout.layout')

@section('title', 'Yeni Fatura')
@section('subTitle', 'Satış Faturası Oluştur')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Yeni Satış Faturası</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('sales.invoices.store') }}" method="POST" id="invoiceForm">
                    @csrf
                    
                    <!-- Customer Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-semibold mb-3">Müşteri Bilgileri</h6>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                    <label class="form-label">Müşteri <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" id="customerSearch" class="form-control" placeholder="Müşteri ara..." autocomplete="off" data-bs-toggle="dropdown" data-bs-auto-close="false" style="min-height: 44px; font-size: 16px;">
                                        <input type="hidden" name="customer_id" id="customerId" required>
                                        <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                            <iconify-icon icon="ion:search-outline" class="text-secondary-light"></iconify-icon>
                                        </div>
                                        <div id="customerDropdown" class="dropdown-menu w-100" style="display: none; max-height: 300px; overflow-y: auto; position: absolute; top: 100%; left: 0; z-index: 1020; background: white; border: 1px solid #dee2e6; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); transform: none !important;">
                                            <!-- Customer search results will be populated here -->
                                        </div>
                                    </div>
                                    <div id="customerInfo" class="mt-2" style="display: none;">
                                        <!-- Selected customer info will be shown here -->
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm mt-2 w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#customerModal">
                                        <iconify-icon icon="solar:add-circle-outline" class="me-1"></iconify-icon>
                                        Yeni Müşteri Ekle
                                    </button>
                                </div>
                                <div class="col-12 col-md-3 mb-3 mb-md-0">
                                    <label class="form-label">Fatura Tarihi <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="date" name="invoice_date" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required style="min-height: 44px; font-size: 16px;">
                                        <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                            <iconify-icon icon="solar:calendar-outline" class="text-secondary-light"></iconify-icon>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Fatura Saati <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="time" name="invoice_time" class="form-control" value="{{ \Carbon\Carbon::now()->format('H:i') }}" required style="min-height: 44px; font-size: 16px;">
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
                                <div class="col-12 col-md-3 mb-3 mb-md-0">
                                    <label class="form-label">Vade Tarihi <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="date" name="due_date" class="form-control" value="{{ \Carbon\Carbon::now()->addDays(30)->format('Y-m-d') }}" required style="min-height: 44px; font-size: 16px;">
                                        <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                            <iconify-icon icon="solar:calendar-outline" class="text-secondary-light"></iconify-icon>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 mb-3 mb-md-0">
                                    <label class="form-label">Para Birimi</label>
                                    <select name="currency" id="currency" class="form-select" style="min-height: 44px; font-size: 16px;">
                                        <option value="TRY" selected>₺ TRY</option>
                                        <option value="USD">$ USD</option>
                                        <option value="EUR">€ EUR</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3 mb-3 mb-md-0" id="exchangeRateContainer" style="display: none;">
                                    <label class="form-label">Döviz Kuru</label>
                                    <input type="text" id="exchangeRate" class="form-control" readonly value="" style="min-height: 44px; font-size: 16px;">
                                </div>
                                <div class="col-12 col-md-3 mb-3 mb-md-0">
                                    <label class="form-label">KDV Durumu</label>
                                    <select name="vat_status" class="form-select" style="min-height: 44px; font-size: 16px;">
                                        <option value="included" selected>Dahil</option>
                                        <option value="excluded">Hariç</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3 mt-3 mt-md-0">
                                    <div class="form-check mt-4 mt-md-0">
                                        <input type="hidden" name="payment_completed" value="0">
                                        <input class="form-check-input" type="checkbox" id="paymentCompleted" name="payment_completed" value="1" style="width: 20px; height: 20px;">
                                        <label class="form-check-label" for="paymentCompleted" style="font-size: 16px; padding-left: 10px;">
                                            <iconify-icon icon="solar:check-circle-outline" class="me-2"></iconify-icon>
                                            Tahsilat Yapıldı
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
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-semibold mb-0">Ürün/Hizmet Detayları</h6>
                                <button type="button" class="btn btn-outline-success btn-sm" id="openInvoiceScanner">
                                    <iconify-icon icon="solar:qr-code-outline" class="me-1"></iconify-icon>
                                    QR ile Ekle
                                </button>
                            </div>
                            
                            <!-- Desktop Table View (hidden on mobile) -->
                            <div class="invoice-table-scroll d-none d-lg-block">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="invoiceItemsTable">
                                        <thead class="table-light">
                                        <tr id="invoiceTableHeader">
                                                <th style="min-width: 280px; padding: 15px;">ÜRÜN/HİZMET</th>
                                                <th style="min-width: 200px; padding: 15px;">AÇIKLAMA</th>
                                                <th style="min-width: 140px; padding: 15px;">MİKTAR</th>
                                                <th style="min-width: 160px; padding: 15px;">B. FİYAT</th>
                                                <th style="min-width: 120px; padding: 15px;">KDV</th>
                                                <th style="min-width: 120px; padding: 15px;">İNDİRİM</th>
                                                <th style="min-width: 160px; padding: 15px;">TOPLAM</th>
                                                <th style="min-width: 80px; padding: 15px;">İŞLEM</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoiceItemsBody">
                                        <!-- Invoice items will be added here -->
                                    </tbody>
                                </table>
                            </div>
                            </div>

                            <!-- Mobile Card View (visible on mobile) -->
                            <div class="d-lg-none" id="mobileInvoiceItems">
                                <!-- Mobile items will be added here -->
                            </div>
                            
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-primary btn-lg w-100 d-lg-none" id="addInvoiceItemMobile">
                                    <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                                    Yeni Ürün Ekle
                                </button>
                                <button type="button" class="btn btn-outline-primary d-none d-lg-inline-block" id="addInvoiceItem">
                                <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                                Yeni Satır Ekle
                            </button>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Totals -->
                    <div class="row">
                        <div class="col-12 col-md-8"></div>
                        <div class="col-12 col-md-4 mt-3 mt-md-0">
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
                    <!-- TL eşdeğeri ve kur gösterimi kaldırıldı -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="submitInvoiceBtn">
                                <iconify-icon icon="solar:check-circle-outline" class="me-2"></iconify-icon>
                                Kaydet
                            </button>
                            <a href="{{ route('sales.invoices.index') }}" class="btn btn-secondary ms-2">
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

<!-- Scanned Product Modal -->
<div class="modal fade" id="scannedProductModal" tabindex="-1" aria-labelledby="scannedProductModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="scannedProductModalLabel">
                    <iconify-icon icon="solar:qr-code-outline" class="me-2"></iconify-icon>
                    Taratılan Ürün
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="scannedProductContent">
                    <!-- Scanned product details will be populated here -->
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <iconify-icon icon="solar:close-circle-outline" class="me-1"></iconify-icon>
                    İptal
                </button>
                <button type="button" class="btn btn-success" id="addScannedProduct">
                    <iconify-icon icon="solar:check-circle-outline" class="me-1"></iconify-icon>
                    Ekle
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true" style="z-index: 1055;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="max-height: 90vh; overflow-y: auto;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="customerModalLabel">
                    <iconify-icon icon="solar:user-plus-outline" class="me-2"></iconify-icon>
                    Yeni Müşteri Ekle
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="newCustomerForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ad Soyad <span class="text-danger">*</span></label>
                            <input type="text" id="newCustomerName" class="form-control" placeholder="Müşteri adı ve soyadı" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Şirket Adı</label>
                            <input type="text" id="newCustomerCompany" class="form-control" placeholder="Şirket adı (opsiyonel)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">E-posta</label>
                            <input type="email" id="newCustomerEmail" class="form-control" placeholder="ornek@email.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telefon <span class="text-danger">*</span></label>
                            <input type="text" id="newCustomerPhone" class="form-control" placeholder="+90 555 123 4567" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Adres</label>
                            <textarea id="newCustomerAddress" class="form-control" rows="3" placeholder="Müşteri adresi (opsiyonel)"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top p-3" style="display: flex !important; justify-content: flex-end; gap: 10px; min-height: 60px; align-items: center;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="min-width: 100px;">
                    <iconify-icon icon="solar:close-circle-outline" class="me-1"></iconify-icon>
                    Kapat
                </button>
                <button type="button" class="btn btn-primary" id="saveNewCustomer" style="min-width: 100px;">
                    <iconify-icon icon="solar:check-circle-outline" class="me-1"></iconify-icon>
                    Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
/* Always-on horizontal scroll and fixed widths for invoice items table */
.invoice-table-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

#invoiceItemsTable {
    min-width: 1400px; /* keep columns readable on any screen */
    table-layout: fixed; /* enforce fixed column widths */
}

#invoiceItemsTable th,
#invoiceItemsTable td {
    white-space: nowrap;
    padding: 12px 10px;
}

/* Prevent controls from collapsing: ensure at least 20px width */
#invoiceItemsTable input,
#invoiceItemsTable select,
#invoiceItemsTable button,
#invoiceItemsTable .btn,
#invoiceItemsTable .input-group-text,
#invoiceItemsTable .dropdown-toggle,
#invoiceItemsTable .form-control,
#invoiceItemsTable .form-select {
    min-width: 20px;
}

/* Column widths: adjust to keep consistent layout */
#invoiceItemsTable th:nth-child(1),
#invoiceItemsTable td:nth-child(1) { /* ÜRÜN/HİZMET */
    width: 280px;
}
#invoiceItemsTable th:nth-child(2),
#invoiceItemsTable td:nth-child(2) { /* AÇIKLAMA */
    width: 200px;
}
#invoiceItemsTable th:nth-child(3),
#invoiceItemsTable td:nth-child(3) { /* SERİ BOYUTU */
    width: 140px;
}
#invoiceItemsTable th:nth-child(4),
#invoiceItemsTable td:nth-child(4) { /* MİKTAR */
    width: 140px;
}
#invoiceItemsTable th:nth-child(5),
#invoiceItemsTable td:nth-child(5) { /* B. FİYAT */
    width: 160px;
}
#invoiceItemsTable th:nth-child(6),
#invoiceItemsTable td:nth-child(6) { /* KDV */
    width: 120px;
}
#invoiceItemsTable th:nth-child(7),
#invoiceItemsTable td:nth-child(7) { /* İNDİRİM */
    width: 120px;
}
#invoiceItemsTable th:nth-child(8),
#invoiceItemsTable td:nth-child(8) { /* TOPLAM */
    width: 160px;
}
#invoiceItemsTable th:nth-child(9),
#invoiceItemsTable td:nth-child(9) { /* İŞLEM */
    width: 100px;
}

/* Make inputs/selects fill their cells but not shrink content */
#invoiceItemsTable input.form-control,
#invoiceItemsTable select.form-select,
#invoiceItemsTable .form-control {
    min-width: 100%;
}

/* Fix dropdown positioning */
#customerDropdown {
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
    
    #invoiceItemsTable th:nth-child(4), /* MİKTAR */
    #invoiceItemsTable td:nth-child(4) {
        min-width: 140px;
        max-width: 140px;
    }
    
    #invoiceItemsTable th:nth-child(5), /* B. FİYAT */
    #invoiceItemsTable td:nth-child(5) {
        min-width: 160px;
        max-width: 160px;
    }
    
    #invoiceItemsTable th:nth-child(6), /* KDV */
    #invoiceItemsTable td:nth-child(6) {
        min-width: 120px;
        max-width: 120px;
    }
    
    #invoiceItemsTable th:nth-child(7), /* İNDİRİM */
    #invoiceItemsTable td:nth-child(7) {
        min-width: 120px;
        max-width: 120px;
    }
    
    #invoiceItemsTable th:nth-child(8), /* TOPLAM */
    #invoiceItemsTable td:nth-child(8) {
        min-width: 160px;
        max-width: 160px;
    }
    
    #invoiceItemsTable th:nth-child(9), /* İŞLEM */
    #invoiceItemsTable td:nth-child(9) {
        min-width: 80px;
        max-width: 80px;
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
    
    /* Product search dropdown specific styles - separate box */
    .product-service-dropdown {
        position: fixed !important;
        z-index: 1020 !important;
        background: white;
        border: 2px solid #007bff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        max-height: 350px;
        overflow-y: auto;
        margin-top: 10px;
        width: 500px;
        padding: 0;
    }
    
    /* Ensure dropdown is visible on mobile */
    @media (max-width: 768px) {
        .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1020 !important;
            max-height: 250px;
        }
        
        .product-service-dropdown {
            position: fixed !important;
            z-index: 9999 !important;
            width: 95% !important;
            max-width: 450px !important;
            left: 2.5% !important;
            right: 2.5% !important;
            max-height: 400px !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2) !important;
            background: white !important;
            border: 2px solid #007bff !important;
        }
        
        .product-service-dropdown .dropdown-item {
            padding: 20px !important;
            font-size: 14px !important;
            line-height: 1.4 !important;
        }
        
        .product-service-dropdown .product-service-item {
            padding: 20px !important;
            font-size: 14px !important;
            line-height: 1.4 !important;
        }
        
        .product-service-dropdown .row {
            margin: 0;
        }
        
        .product-service-dropdown .col-8,
        .product-service-dropdown .col-4 {
            padding: 0 8px;
        }
    }
    
    /* Tablet optimizations */
    @media (max-width: 1024px) and (min-width: 769px) {
        .product-service-dropdown {
            width: 450px !important;
            max-height: 400px !important;
        }
    }
    
    .dropdown-item {
        padding: 0.5rem 0.75rem;
    }
    
    /* Product service dropdown row layout */
    .product-service-dropdown .row {
        margin: 0;
    }
    
    .product-service-dropdown .col-8,
    .product-service-dropdown .col-4 {
        padding: 0 8px;
    }
}

/* Small desktop screens - make rows stack vertically */
@media screen and (max-width: 1200px) and (min-width: 992px) {
    #invoiceItemsTable {
        display: none;
    }
    
    #mobileInvoiceItems {
        display: block !important;
    }
    
    .d-lg-none {
        display: block !important;
    }
    
    .d-none.d-lg-block {
        display: none !important;
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
    // Check if mobile view
    const isMobile = window.innerWidth < 992;
    
    // Add initial invoice item row based on device
    if (isMobile) {
        addMobileInvoiceItemRow();
    } else {
        addInvoiceItemRow();
    }
    
    // Open scanner in invoice context
    $('#openInvoiceScanner').on('click', function(){
        if (window.openGlobalScanner) window.openGlobalScanner({ invoiceContext: true, multi: true });
    });
    
    // Setup CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Initialize exchange rate if currency is not TRY
    const initialCurrency = $('#currency').val();
    if (initialCurrency !== 'TRY') {
        updateExchangeRate(initialCurrency);
    }
    
    // Customer search functionality
    $('#customerSearch').on('input', function() {
        const query = $(this).val();
        console.log('Customer search input:', query);
        if (query.length >= 2) {
            searchCustomers(query);
        } else {
            const dd = $('#customerDropdown');
            dd.hide();
            dd.removeClass('force-visible');
        }
    });
    
    // Also trigger search on focus if there's text
    $('#customerSearch').on('focus', function() {
        const query = $(this).val();
        if (query.length >= 2) {
            searchCustomers(query);
        }
    });
    
    // Hide dropdown when clicking outside - improved for mobile
    let clickOutsideTimer = null;
    $(document).on('click touchend', function(e) {
        // Check if click is on customer search input, dropdown, or any dropdown item
        const $target = $(e.target);
        const isInsideDropdown = $target.closest('#customerDropdown').length > 0;
        const isInsideSearch = $target.closest('#customerSearch').length > 0;
        const isDropdownItem = $target.hasClass('customer-option') || $target.closest('.customer-option').length > 0;
        
        // On mobile, add a small delay to allow dropdown item click to register first
        const isMobile = window.innerWidth < 992;
        
        if (!isInsideDropdown && !isInsideSearch && !isDropdownItem) {
            if (isMobile && clickOutsideTimer) {
                clearTimeout(clickOutsideTimer);
            }
            
            if (isMobile) {
                // Small delay on mobile to ensure dropdown item clicks register first
                clickOutsideTimer = setTimeout(function() {
                    const dd = $('#customerDropdown');
                    dd.hide();
                    dd.removeClass('force-visible');
                }, 150);
            } else {
                const dd = $('#customerDropdown');
                dd.hide();
                dd.removeClass('force-visible');
            }
        } else if (isDropdownItem || isInsideDropdown) {
            // Clear any pending hide timer if clicking inside dropdown
            if (clickOutsideTimer) {
                clearTimeout(clickOutsideTimer);
                clickOutsideTimer = null;
            }
        }
    });
    // Reposition on resize/scroll for mobile - but don't hide dropdown
    let isScrollingDropdown = false;
    $('#customerDropdown').on('scroll touchstart touchmove', function() {
        isScrollingDropdown = true;
        clearTimeout(window.dropdownScrollTimeout);
        window.dropdownScrollTimeout = setTimeout(function() {
            isScrollingDropdown = false;
        }, 150);
    });
    
    $(window).on('resize', function(){
        const dd = $('#customerDropdown');
        if (dd.is(':visible') && dd.data('moved-to-body')) {
            const inputEl = document.getElementById('customerSearch');
            const rect = inputEl.getBoundingClientRect();
            const isMobile = window.innerWidth < 992;
            const isVerySmall = window.innerWidth < 576;
            
            if (isMobile) {
                let dropdownWidth;
                let dropdownLeft;
                
                if (isVerySmall) {
                    // At very small sizes, use almost full width (95% of viewport)
                    dropdownWidth = Math.max(window.innerWidth - 20, window.innerWidth * 0.95);
                    dropdownLeft = (window.innerWidth - dropdownWidth) / 2; // Center it
                    if (dropdownLeft < 10) dropdownLeft = 10;
                } else {
                    // For larger mobile screens, use input width or min 300px
                    dropdownWidth = Math.min(window.innerWidth - 20, Math.max(300, rect.width));
                    dropdownLeft = Math.max(10, Math.min(rect.left, window.innerWidth - dropdownWidth - 10));
                }
                
                let dropdownTop = rect.bottom + 8;
                const maxTop = window.innerHeight - 100;
                if (dropdownTop > maxTop) {
                    dropdownTop = Math.max(10, rect.top - 8);
                }
                    if (dropdownTop < 10) {
                        dropdownTop = 10;
                }
                
                dd.css({
                    'top': dropdownTop + 'px',
                    'left': dropdownLeft + 'px',
                    'width': dropdownWidth + 'px',
                    'display': 'block',
                    'visibility': 'visible',
                    'opacity': '1',
                    'max-height': isVerySmall ? '70vh' : '60vh'
                });
            }
        }
    });
    
    // Add new item row button
    $('#addInvoiceItem').on('click', function() {
        console.log('Add invoice item button clicked');
        addInvoiceItemRow();
    });
    
    // Add new mobile item button
    $('#addInvoiceItemMobile').on('click', function() {
        console.log('Add mobile invoice item button clicked');
        addMobileInvoiceItemRow();
    });

    // Add scanned product button
    $('#addScannedProduct').on('click', function(){
        if (scannedProductData) {
            // Collect overrides from modal inputs
            const overrides = {
                description: ($('#modalDescription').val() || '').trim(),
                quantity: parseFloat($('#modalQuantity').val()) || 1,
                discount_rate: parseFloat($('#modalDiscountAmount').val()) || 0,
                unit_price: parseFloat($('#modalUnitPrice').val()) || (parseFloat(scannedProductData.price || 0) || 0),
                unit_currency: $('#modalUnitCurrency').val() || (scannedProductData.currency || $('#currency').val()),
                tax_rate: parseFloat($('#modalTaxRate').val()) || (parseFloat(scannedProductData.vat_rate || 10) || 10),
                color_variant_id: $('#modalColorVariant').length ? ($('#modalColorVariant').val() || null) : null,
            };
            
            // Build item to append with price/currency/tax and preferred color
            const item = Object.assign({}, scannedProductData, {
                price: overrides.unit_price,
                vat_rate: overrides.tax_rate,
                currency: overrides.unit_currency,
                preferred_color_variant_id: overrides.color_variant_id || scannedProductData.preferred_color_variant_id || null
            });
            
            appendInvoiceItemFromResult(item);
            
            // After row/card created, set description/qty/discount and color name
            setTimeout(() => {
                const index = itemCounter - 1;
                const row = $(`tr[data-item-index="${index}"]`);
                const card = $(`.card[data-item-index="${index}"]`);
                const selectedVariantId = overrides.color_variant_id;
                const selectedVariant = (scannedProductData.color_variants || []).find(v => (v.id+'' === (selectedVariantId||'')+''));
                const selectedColorName = selectedVariant ? selectedVariant.color : '';
                
                if (row.length) {
                    if (overrides.description) row.find('textarea[name*="[description]"]').val(overrides.description);
                    row.find('input[name*="[quantity]"]').val(overrides.quantity);
                    row.find('input[name*="[discount_rate]"]').val(overrides.discount_rate);
                    row.find('.unit-currency').val(overrides.unit_currency).trigger('change');
                    if (selectedVariantId) {
                        const colorSelect = row.find('.color-variant-select');
                        if (colorSelect.length) {
                            colorSelect.val(selectedVariantId).trigger('change');
                            colorSelect.prop('disabled', true).addClass('bg-light');
                        }
                        row.find('input[name*="[selected_color]"]').val(selectedColorName);
                        // Ensure disabled select value is submitted by adding a hidden mirror input
                        if (row.find('input.color-variant-id-hidden').length === 0) {
                            row.find('.color-cell').append(`<input type="hidden" class="color-variant-id-hidden" name="items[${index}][color_variant_id]" value="${selectedVariantId}">`);
                        } else {
                            row.find('input.color-variant-id-hidden').val(selectedVariantId);
                        }
                    }
                    calculateLineTotal.call(row.find('.unit-price')[0]);
                } else if (card.length) {
                    if (overrides.description) card.find('textarea[name*="[description]"]').val(overrides.description);
                    card.find('input[name*="[quantity]"]').val(overrides.quantity).trigger('input');
                    card.find('input[name*="[discount_rate]"]').val(overrides.discount_rate);
                    card.find('.unit-currency').val(overrides.unit_currency).trigger('change');
                    if (selectedVariantId) {
                        const colorSelect = card.find('.color-variant-select');
                        if (colorSelect.length) {
                            colorSelect.val(selectedVariantId).trigger('change');
                            colorSelect.prop('disabled', true).addClass('bg-light');
                        }
                        card.find('input[name*="[selected_color]"]').val(selectedColorName);
                        // Ensure disabled select value is submitted by adding a hidden mirror input
                        if (card.find('input.color-variant-id-hidden').length === 0) {
                            card.find('.color-selection-mobile').append(`<input type="hidden" class="color-variant-id-hidden" name="items[${index}][color_variant_id]" value="${selectedVariantId}">`);
                        } else {
                            card.find('input.color-variant-id-hidden').val(selectedVariantId);
                        }
                    }
                    calculateMobileLineTotal.call(card.find('.unit-price')[0]);
                }
            }, 120);
            
            toastr.success(scannedProductData.name + ' eklendi');
            
            // Close modal using Bootstrap method
            (function(){
                try {
                    const m = bootstrap.Modal.getInstance(document.getElementById('scannedProductModal'));
                    if (m) m.hide();
                } catch(e){}
                // Fallback for jQuery plugin
                try { $('#scannedProductModal').modal('hide'); } catch(e){}
                // Also ensure any scanner modal/backdrop is removed if still present
                try {
                    const gs = bootstrap.Modal.getInstance(document.getElementById('globalScannerModal'));
                    if (gs) gs.hide();
                } catch(e){}
                setTimeout(function(){
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right','');
                    $('#scannedProductModal').attr('aria-hidden','true');
                }, 150);
            })();
            scannedProductData = null;
        }
    });
    
    // Remove item row
    $(document).on('click', '.remove-item', function() {
        if ($('#invoiceItemsBody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateTotals();
        }
    });
    
    // Remove mobile item button
    $(document).on('click', '.remove-mobile-item', function() {
        $(this).closest('.card').remove();
        calculateTotals();
    });
    
    // Save new customer
    $('#saveNewCustomer').on('click', function() {
        console.log('Save new customer button clicked');
        saveNewCustomer();
    });
    
    // Ensure modal is properly initialized
    $('#customerModal').on('shown.bs.modal', function() {
        console.log('Customer modal shown');
        console.log('Modal footer exists:', $('#customerModal .modal-footer').length > 0);
        console.log('Save button exists:', $('#saveNewCustomer').length > 0);
        // Focus on first input
        $('#newCustomerName').focus();
    });
    
    // Clear form when modal is hidden
    $('#customerModal').on('hidden.bs.modal', function() {
        console.log('Customer modal hidden');
        $('#newCustomerForm')[0].reset();
    });
    
    // Currency change handler
    $('#currency').on('change', function() {
        const selectedCurrency = $(this).val();
        const exchangeRateContainer = $('#exchangeRateContainer');
        // Show only exchange rate (no TL totals)
        if (selectedCurrency === 'TRY') {
            exchangeRateContainer.hide();
        } else {
            exchangeRateContainer.show();
            updateExchangeRate(selectedCurrency);
        }
        // Keep TL totals hidden
        $('#foreignCurrencyTotals').hide();
        updateCurrencySymbols(selectedCurrency === 'USD' ? '$' : (selectedCurrency === 'EUR' ? '€' : '₺'));
            calculateTotals();
    });
    
    // Form validation
    $('#invoiceForm').on('submit', function(e) {
        console.log('=== FORM SUBMIT HANDLER 1 (Müşteri ve Ürün Kontrolü) ===');
        console.log('Form submit triggered');
        console.log('Customer ID:', $('#customerId').val());
        console.log('Invoice items count:', $('#invoiceItemsBody tr').length);
        
        if ($('#customerId').val() === '') {
            console.log('FORM SUBMIT ENGELLENDİ - Müşteri seçilmemiş');
            e.preventDefault();
            alert('Lütfen bir müşteri seçin.');
            return false;
        }
        
        if ($('#invoiceItemsBody tr').length === 0) {
            console.log('FORM SUBMIT ENGELLENDİ - Ürün/hizmet eklenmemiş');
            e.preventDefault();
            alert('En az bir ürün/hizmet eklemelisiniz.');
            return false;
        }
        
        // Update form with calculated values before submit
        updateFormWithCalculatedValues();
        
        console.log('Form validation passed, submitting...');
    });
    
    // Submit button click handler for debugging
    $('#submitInvoiceBtn, button[type="submit"]').on('click', function(e) {
        console.log('=== SUBMIT BUTTON CLICKED ===');
        console.log('Button clicked, form will submit...');
        console.log('Form element:', $('#invoiceForm').length);
        console.log('Form action:', $('#invoiceForm').attr('action'));
    });
});
// Functions to be called by global scanner
window.addScannedProductById = function(id){
    // Try to fetch product details via search endpoint by ID
    $.get('{{ route("sales.invoices.search.products") }}', { q: id })
        .done(function(list){
            const item = list.find(i => (i.id+'').endsWith(id+''));
            if (item) {
                appendInvoiceItemFromResult(item);
                toastr.success(item.name + ' eklendi');
            } else {
                toastr.error('Ürün bulunamadı: #' + id);
            }
        })
        .fail(function(){
            toastr.error('Ürün arama hatası');
        });
}

window.addScannedProductByCode = function(code){
    // Directly search for the product using the search endpoint
    $.get('{{ route("sales.invoices.search.products") }}', { q: code })
                    .done(function(list){
            if (list.length > 0) {
                // Use the first result (most relevant)
                const item = list[0];
                showScannedProductModal(item);
                            } else {
                alert('Kod ile ürün bulunamadı: ' + code);
            }
        })
        .fail(function(xhr, status, error){
            alert('Ürün arama hatası');
        });
}

// Global variable to store the scanned product data
let scannedProductData = null;
// Guard to prevent multiple quick clicks on "Ekle"
let scannedAddInProgress = false;

function showScannedProductModal(item) {
    scannedProductData = item;
    
    const modalContent = `
        <div class="row g-3">
            <div class="col-12">
                <div class="alert alert-success">
                    <iconify-icon icon="solar:check-circle-outline" class="me-2"></iconify-icon>
                    <strong>Ürün bulundu!</strong> Aşağıdaki ürünü faturaya eklemek istediğinizden emin misiniz?
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h6 class="fw-semibold text-primary mb-3">Ürün Bilgileri</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Ürün/Hizmet Adı</label>
                                <div class="form-control-plaintext fw-semibold">${item.name}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kategori</label>
                                <div class="form-control-plaintext">${item.category || 'Belirtilmemiş'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Marka</label>
                                <div class="form-control-plaintext">${item.brand || 'Belirtilmemiş'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Satış Fiyatı</label>
                                <div class="form-control-plaintext fw-semibold text-success">${parseFloat(item.price || 0).toFixed(2)} ₺</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Stok Durumu</label>
                                <div class="form-control-plaintext">${item.stock_quantity || 0} adet</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="fw-semibold text-primary mb-3">Ek Bilgiler</h6>
                        <div class="mb-2">
                            <small class="text-muted">Ürün Kodu:</small><br>
                            <span class="fw-semibold">${item.product_code || 'Yok'}</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Tip:</small><br>
                            <span class="badge bg-${item.type === 'series' ? 'info' : 'primary'}">${item.type === 'series' ? 'Seri Ürün' : 'Tekil Ürün'}</span>
                        </div>
                        ${item.size ? `
                        <div class="mb-2">
                            <small class="text-muted">Beden:</small><br>
                            <span class="fw-semibold">${item.size}</span>
                        </div>
                        ` : ''}
                        ${item.color ? `
                        <div class="mb-2">
                            <small class="text-muted">Renk:</small><br>
                            <span class="fw-semibold">${item.color}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#scannedProductContent').html(modalContent);
    $('#scannedProductModal').modal('show');
    
    // Append line settings (inputs) into modal
    const settingsHtml = `
        <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="fw-semibold text-primary mb-3">Satır Ayarları</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Açıklama</label>
                                <textarea id="modalDescription" class="form-control" rows="2" placeholder="Açıklama girin"></textarea>
                            </div>
                            ${item.has_color_variants && item.color_variants && item.color_variants.length > 0 ? `
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Renk</label>
                                <select id="modalColorVariant" class="form-select">
                                    <option value="">Renk Seçin</option>
                                    ${(item.color_variants || []).map(v => `<option value="${v.id}">${v.color}${v.stock_quantity ? ' ('+v.stock_quantity+' adet)' : ''}</option>`).join('')}
                                </select>
                            </div>` : ``}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Miktar</label>
                                <input type="number" id="modalQuantity" class="form-control" value="1" min="0.01" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">İndirim (Tutar)</label>
                                <input type="number" id="modalDiscountAmount" class="form-control" value="0" min="0" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Birim Fiyat</label>
                                <input type="number" id="modalUnitPrice" class="form-control" value="${parseFloat(item.price || 0).toFixed(2)}" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Para Birimi</label>
                                <select id="modalUnitCurrency" class="form-select">
                                    <option value="TRY" ${(item.currency || $('#currency').val()) === 'TRY' ? 'selected' : ''}>₺ TRY</option>
                                    <option value="USD" ${(item.currency || $('#currency').val()) === 'USD' ? 'selected' : ''}>$ USD</option>
                                    <option value="EUR" ${(item.currency || $('#currency').val()) === 'EUR' ? 'selected' : ''}>€ EUR</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">KDV</label>
                                <select id="modalTaxRate" class="form-select">
                                    <option value="0" ${(+item.vat_rate === 0 ? 'selected' : '')}>KDV %0</option>
                                    <option value="1" ${(+item.vat_rate === 1 ? 'selected' : '')}>KDV %1</option>
                                    <option value="10" ${(+item.vat_rate === 10 || item.vat_rate === undefined ? 'selected' : '')}>KDV %10</option>
                                    <option value="20" ${(+item.vat_rate === 20 ? 'selected' : '')}>KDV %20</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    $('#scannedProductContent').append(settingsHtml);
    // Auto-select preferred or single color
    if (item.has_color_variants && item.color_variants && item.color_variants.length > 0) {
        const preferred = item.preferred_color_variant_id || (item.color_variants.length === 1 ? item.color_variants[0].id : null);
        if (preferred) {
            setTimeout(() => { 
                $('#modalColorVariant').val(preferred).trigger('change');
                // Lock selection when color comes from scanned variant
                $('#modalColorVariant').prop('disabled', true).addClass('bg-light');
                if ($('#modalColorVariant').next('.form-text').length === 0) {
                    $('#modalColorVariant').after('<div class="form-text text-success">Renk barkoddan otomatik belirlendi.</div>');
                }
            }, 50);
        }
    }
}

function appendInvoiceItemFromResult(item){
    // Check if we're on mobile or desktop
    const isMobile = window.innerWidth < 992;
    
    if (isMobile) {
        addMobileInvoiceItemRow();
        const index = itemCounter - 1;
        const card = $(`.card[data-item-index="${index}"]`);
        
        // Set all fields exactly like manual selection
        card.find('input[name*="[product_service_name]"]').val(item.name);
        card.find('input[name*="[unit_price]"]').val(item.price);
        card.find('select[name*="[tax_rate]"]').val(item.vat_rate);
        card.find('input[name*="[product_id]"]').val(item.id.replace(/^(product_|series_|service_)/, ''));
        card.find('input[name*="[type]"]').val(item.type);
        
        // Handle color variants - EXACTLY like manual selection
        if (item.has_color_variants && item.color_variants && item.color_variants.length > 0) {
            // Show color selection in mobile
            card.find('.color-selection-mobile').show();
            
            // Populate color options
            const colorSelect = card.find('.color-variant-select');
            colorSelect.empty().append('<option value="">Renk Seçin</option>');
            
            item.color_variants.forEach(function(variant) {
                colorSelect.append(`<option value="${variant.id}" data-color="${variant.color}">${variant.color}</option>`);
            });
            
            // Store color variants data
            card.data('color-variants', item.color_variants);
            
            // Auto-select preferred color if provided
            if (item.preferred_color_variant_id) {
                colorSelect.val(item.preferred_color_variant_id).trigger('change');
                const selected = item.color_variants.find(v => v.id == item.preferred_color_variant_id);
                if (selected) {
                    card.find('input[name*="[selected_color]"]').val(selected.color);
                }
                // Lock color selection on mobile when scanned variant defines color
                colorSelect.prop('disabled', true).addClass('bg-light');
                // Add hidden mirror input so disabled select value is submitted
                const idx = card.data('item-index');
                if (card.find('input.color-variant-id-hidden').length === 0) {
                    card.find('.color-selection-mobile').append(`<input type="hidden" class="color-variant-id-hidden" name="items[${idx}][color_variant_id]" value="${item.preferred_color_variant_id}">`);
                } else {
                    card.find('input.color-variant-id-hidden').val(item.preferred_color_variant_id);
                }
            } else 
            // If there's only one color, auto-select it and show stock info
            if (item.color_variants.length === 1) {
                const variant = item.color_variants[0];
                colorSelect.val(variant.id);
                card.find('input[name*="[selected_color]"]').val(variant.color);
                
                // Show stock information
                const stockInfoHtml = `
                    <div class="mobile-stock-info mt-2 p-3" style="background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #28a745;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Mevcut Stok</small>
                                <strong class="text-success">${variant.stock_quantity} Adet</strong>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Kritik Stok</small>
                                <small class="text-warning">${variant.critical_stock} Adet</small>
                            </div>
                        </div>
                    </div>
                `;
                
                card.find('.color-selection-mobile').after(stockInfoHtml);
                
                // Validate stock
                const quantityInput = card.find('.quantity-input');
                const seriesSizeSelect = card.find('.series-size-select');
                validateMobileStock(card, variant, quantityInput, seriesSizeSelect);
            }
        }
        
        // Store stock information for validation
        card.data('stock-quantity', item.stock_quantity || 0);
        card.data('product-type', item.type);
        
        // Set unit currency from item currency and recalc
        if (item.currency) {
            card.find('.unit-currency').val(item.currency).trigger('change');
        }
        // Recalculate totals
        calculateMobileLineTotal.call(card.find('.unit-price')[0]);
    } else {
    addInvoiceItemRow();
    const index = itemCounter - 1;
    const row = $(`tr[data-item-index="${index}"]`);
        
        // Set all fields exactly like manual selection
    row.find('input[name*="[product_service_name]"]').val(item.name);
    row.find('input[name*="[unit_price]"]').val(item.price);
    row.find('select[name*="[tax_rate]"]').val(item.vat_rate);
    row.find('input[name*="[product_id]"]').val(item.id.replace(/^(product_|series_|service_)/, ''));
    row.find('input[name*="[type]"]').val(item.type);
    
        // Handle color variants - EXACTLY like manual selection
    if (item.has_color_variants && item.color_variants && item.color_variants.length > 0) {
        // Add color column to table header if not exists
        addColorColumnToTable();
        
            // Add color cell to current row - immediate like manual selection
            addColorCellToRow(row, item.color_variants);
        
        // Store color variants data
        row.data('color-variants', item.color_variants);
        // Auto-select preferred color if provided
        if (item.preferred_color_variant_id) {
            const colorSelect = row.find('.color-variant-select');
            if (colorSelect.length) {
                colorSelect.val(item.preferred_color_variant_id).trigger('change');
                // Lock color selection on desktop when scanned variant defines color
                colorSelect.prop('disabled', true).addClass('bg-light');
                // Add hidden mirror input so disabled select value is submitted
                const idx = row.data('item-index');
                if (row.find('input.color-variant-id-hidden').length === 0) {
                    row.find('.color-cell').append(`<input type="hidden" class="color-variant-id-hidden" name="items[${idx}][color_variant_id]" value="${item.preferred_color_variant_id}">`);
                } else {
                    row.find('input.color-variant-id-hidden').val(item.preferred_color_variant_id);
                }
            }
        }
    }
    
    // Set unit currency from item currency (EUR/USD/TRY)
    if (item.currency) {
        row.find('.unit-currency').val(item.currency).trigger('change');
    }
    // Store stock information for validation
    row.data('stock-quantity', item.stock_quantity || 0);
    row.data('product-type', item.type);
    
        // Recalculate totals
    calculateLineTotal.call(row.find('.unit-price')[0]);
    }
}

function searchCustomers(query) {
    console.log('Searching customers with query:', query);
    console.log('Search URL:', '{{ route("sales.invoices.search.customers") }}');
    
    $.get('{{ route("sales.invoices.search.customers") }}', { q: query })
        .done(function(customers) {
            console.log('Customers found:', customers);
            let html = '';
            if (customers.length > 0) {
                customers.forEach(function(customer) {
                    html += `
                        <div class="dropdown-item customer-option" data-customer-id="${customer.id}" data-customer-name="${customer.name}" data-customer-company="${customer.company_name || ''}" data-customer-email="${customer.email || ''}" data-customer-phone="${customer.phone || ''}" style="cursor: pointer; padding: 8px 16px;">
                            <div class="fw-semibold">${customer.name}</div>
                            ${customer.company_name ? `<small class="text-secondary-light">${customer.company_name}</small>` : ''}
                        </div>
                    `;
                });
            } else {
                html = '<div class="dropdown-item text-secondary-light" style="padding: 8px 16px;">Müşteri bulunamadı</div>';
            }
            $('#customerDropdown').html(html);
            // Ensure dropdown is visible and correctly positioned (especially on mobile)
            const inputEl = document.getElementById('customerSearch');
            const rect = inputEl.getBoundingClientRect();
            const isMobile = window.innerWidth < 992;
            const dd = $('#customerDropdown');
            
            console.log('Customer dropdown positioning:', {
                isMobile,
                inputRect: rect,
                windowWidth: window.innerWidth
            });
            
            if (isMobile) {
                // Move to body and position fixed like product dropdown logic
                if (!dd.data('moved-to-body')) {
                    dd.appendTo('body');
                    dd.data('moved-to-body', true);
                }
                
                // Calculate dropdown position - ensure it's visible on screen
                // Fixed position is relative to viewport, not document, so don't add scroll
                const isVerySmall = window.innerWidth < 576;
                
                // For very small screens, use almost full width
                let dropdownWidth;
                let dropdownLeft;
                
                if (isVerySmall) {
                    // At very small sizes, use almost full width (95% of viewport)
                    dropdownWidth = Math.max(window.innerWidth - 20, window.innerWidth * 0.95);
                    dropdownLeft = (window.innerWidth - dropdownWidth) / 2; // Center it
                    if (dropdownLeft < 10) dropdownLeft = 10;
                } else {
                    // For larger mobile screens, use input width or min 300px
                    dropdownWidth = Math.min(window.innerWidth - 20, Math.max(300, rect.width));
                    dropdownLeft = Math.max(10, Math.min(rect.left, window.innerWidth - dropdownWidth - 10));
                }
                
                // Fixed position: use viewport coordinates directly (rect is already relative to viewport)
                let dropdownTop = rect.bottom + 8;
                
                // Ensure dropdown doesn't go off bottom of screen
                const maxTop = window.innerHeight - 100;
                if (dropdownTop > maxTop) {
                    // If it would go off screen, show above input instead
                    dropdownTop = Math.max(10, rect.top - 8);
                }
                
                // Ensure dropdown doesn't go off top
                    if (dropdownTop < 10) {
                        dropdownTop = 10;
                }
                
                dd.css({
                    'position': 'fixed',
                    'top': dropdownTop + 'px',
                    'left': dropdownLeft + 'px',
                    'width': dropdownWidth + 'px',
                    'z-index': '99999',
                    'transform': 'none',
                    'margin-top': '0',
                    'margin-left': '0',
                    'display': 'block',
                    'visibility': 'visible',
                    'opacity': '1',
                    'max-height': isVerySmall ? '70vh' : '60vh',
                    'overflow-y': 'auto',
                    'background': 'white',
                    'border': '2px solid #007bff',
                    'border-radius': '12px',
                    'box-shadow': '0 8px 24px rgba(0, 0, 0, 0.2)',
                    'pointer-events': 'auto'
                });
                
                console.log('Mobile dropdown positioned:', {
                    top: dropdownTop + 'px',
                    left: dropdownLeft + 'px',
                    width: dropdownWidth + 'px',
                    inputRect: rect,
                    windowHeight: window.innerHeight,
                    windowWidth: window.innerWidth
                });
            } else {
                // Desktop – keep in original position and relative
                // If it was moved to body, move it back
                if (dd.data('moved-to-body')) {
                    const parent = $(inputEl).closest('.position-relative');
                    if (parent.length) {
                        parent.append(dd);
                        dd.removeData('moved-to-body');
                    }
                }
                
                dd.css({
                    'position': 'absolute',
                    'top': '100%',
                    'left': '0',
                    'right': '0',
                    'transform': 'none',
                    'margin-top': '0',
                    'display': 'block',
                    'visibility': 'visible',
                    'opacity': '1',
                    'z-index': '1020',
                    'max-height': '300px',
                    'overflow-y': 'auto',
                    'background': 'white',
                    'border': '1px solid #dee2e6',
                    'border-radius': '0.375rem',
                    'box-shadow': '0 0.5rem 1rem rgba(0, 0, 0, 0.15)',
                    'width': 'auto'
                });
            }
            
            // Explicitly show dropdown and ensure visibility on mobile
            dd.show();
            if (isMobile) {
                const isVerySmall = window.innerWidth < 576;
                
                // CRITICAL: Force visibility immediately on mobile
                dd.removeClass('hidden d-none');
                dd.addClass('force-visible mobile-dropdown-visible');
                
                // Ensure dropdown is visible and clickable on mobile - use !important via inline style
                dd.attr('style', dd.attr('style') + '; display: block !important; visibility: visible !important; opacity: 1 !important; pointer-events: auto !important;');
                
                // On very small screens, ensure it's positioned correctly
                if (isVerySmall) {
                    // Force update position for very small screens
                    setTimeout(function() {
                        const inputRect = inputEl.getBoundingClientRect();
                        let dropdownTop = inputRect.bottom + 8;
                        const maxTop = window.innerHeight - 100;
                        if (dropdownTop > maxTop) {
                            dropdownTop = Math.max(10, inputRect.top - 8);
                        }
                        if (dropdownTop < 10) dropdownTop = 10;
                        
                        // Force all styles with !important via inline style
                        const currentStyle = dd.attr('style') || '';
                        dd.attr('style', currentStyle + 
                            '; left: 10px !important; ' +
                            'width: calc(100vw - 20px) !important; ' +
                            'top: ' + dropdownTop + 'px !important; ' +
                            'display: block !important; ' +
                            'visibility: visible !important; ' +
                            'opacity: 1 !important; ' +
                            'z-index: 99999 !important; ' +
                            'position: fixed !important;');
                    }, 50);
                } else {
                    // For larger mobile screens, also force with setTimeout
                    setTimeout(function() {
                        const currentStyle = dd.attr('style') || '';
                        dd.attr('style', currentStyle + 
                            '; display: block !important; ' +
                            'visibility: visible !important; ' +
                            'opacity: 1 !important; ' +
                            'z-index: 99999 !important; ' +
                            'position: fixed !important;');
                    }, 50);
                }
            }
            console.log('Dropdown shown with HTML:', html);
        })
        .fail(function(xhr, status, error) {
            console.error('Customer search failed:', xhr.responseText, status, error);
            $('#customerDropdown').html('<div class="dropdown-item text-danger" style="padding: 8px 16px;">Arama sırasında hata oluştu</div>').show();
            // Ensure dropdown is positioned correctly
            $('#customerDropdown').css({
                'position': 'absolute',
                'top': '100%',
                'left': '0',
                'right': '0',
                'transform': 'none',
                'margin-top': '0'
            });
        });
}

// Handle customer option click - support both click and touch events for mobile
$(document).on('click touchend', '.customer-option', function(e) {
    // Prevent default and stop propagation on mobile to avoid double-firing
    if (e.type === 'touchend') {
        e.preventDefault();
        e.stopPropagation();
    }
    
    console.log('Customer option clicked/touched');
    const customerId = $(this).data('customer-id');
    const customerName = $(this).data('customer-name');
    const customerCompany = $(this).data('customer-company');
    const customerEmail = $(this).data('customer-email');
    const customerPhone = $(this).data('customer-phone');
    
    console.log('Selected customer:', { customerId, customerName, customerCompany, customerEmail, customerPhone });
    
    $('#customerId').val(customerId);
    $('#customerSearch').val(customerName);
    const dd = $('#customerDropdown');
    dd.hide();
    dd.removeClass('force-visible');
    
    // Show customer info
    let infoHtml = `<div class="text-success"><iconify-icon icon="solar:check-circle-outline" class="me-1"></iconify-icon>${customerName}`;
    if (customerCompany) infoHtml += ` - ${customerCompany}`;
    if (customerEmail) infoHtml += ` (${customerEmail})`;
    infoHtml += `</div>`;
    $('#customerInfo').html(infoHtml).show();
    
    console.log('Customer selected and info shown');
});

function addInvoiceItemRow() {
    console.log('Adding invoice item row, current counter:', itemCounter);
    
    // Check if color column exists in table header
    const hasColorColumn = $('#invoiceTableHeader th:contains("RENK")').length > 0;
    
    const rowHtml = `
        <tr data-item-index="${itemCounter}" style="border-bottom: 2px solid #f8f9fa;">
            <td style="padding: 20px 15px;">
                <div class="position-relative">
                    <input type="text" name="items[${itemCounter}][product_service_name]" class="form-control product-service-search" placeholder="Ürün/Hizmet ara..." data-row="${itemCounter}" required style="min-height: 30px; height: 30px; font-size: 14px; border-radius: 8px;">
                    <div id="productServiceDropdown${itemCounter}" class="product-service-dropdown" style="display: none;">
                        <!-- Search results will be populated here -->
                    </div>
                </div>
            </td>
            <td style="padding: 20px 15px;">
                <textarea name="items[${itemCounter}][description]" class="form-control" rows="2" placeholder="Açıklama" style="min-height: 45px; font-size: 14px; border-radius: 8px; resize: vertical;"></textarea>
            </td>
            ${hasColorColumn ? `
            <td class="color-cell" style="padding: 20px 15px;">
                <select name="items[${itemCounter}][color_variant_id]" class="form-select color-variant-select" style="min-height: 45px; font-size: 14px; border-radius: 8px;">
                    <option value="">Renk Seçin</option>
                </select>
                <input type="hidden" name="items[${itemCounter}][selected_color]" value="">
            </td>
            ` : ''}
            <td style="padding: 20px 15px;">
                <div class="input-group">
                    <input type="number" name="items[${itemCounter}][quantity]" class="form-control quantity-input" value="1" min="0.01" step="0.01" required style="min-height: 45px; font-size: 15px; border-radius: 8px 0 0 8px;">
                    <span class="input-group-text quantity-unit" style="min-height: 45px; font-size: 14px; border-radius: 0 8px 8px 0;">Adet</span>
                </div>
                <small class="text-danger stock-zero" style="display:none; font-size: 12px;">0 Adet</small>
            </td>
            <td style="padding: 20px 15px;">
                <div class="input-group">
                    <input type="number" name="items[${itemCounter}][unit_price]" class="form-control unit-price" value="0" min="0" step="0.01" required style="min-height: 45px; font-size: 15px; border-radius: 8px 0 0 8px;">
                    <select name="items[${itemCounter}][unit_currency]" class="form-select unit-currency" style="max-width: 70px; min-height: 45px; font-size: 14px; border-radius: 0 8px 8px 0;">
                        <option value="TRY" ${$('#currency').val() === 'TRY' ? 'selected' : ''}>₺</option>
                        <option value="USD" ${$('#currency').val() === 'USD' ? 'selected' : ''}>$</option>
                        <option value="EUR" ${$('#currency').val() === 'EUR' ? 'selected' : ''}>€</option>
                    </select>
                </div>
            </td>
            <td style="padding: 20px 15px;">
                <select name="items[${itemCounter}][tax_rate]" class="form-select tax-rate" style="min-height: 45px; font-size: 14px; border-radius: 8px;">
                    <option value="0">KDV %0</option>
                    <option value="1">KDV %1</option>
                    <option value="10" selected>KDV %10</option>
                    <option value="20">KDV %20</option>
                </select>
            </td>
            <td style="padding: 20px 15px;">
                <div class="input-group">
                    <input type="number" name="items[${itemCounter}][discount_rate]" class="form-control discount-rate" value="0" min="0" step="0.01" style="min-height: 45px; font-size: 15px; border-radius: 8px 0 0 8px;">
                    <span class="input-group-text discount-currency-symbol" style="min-height: 45px; font-size: 14px; border-radius: 0 8px 8px 0;">${$('#currency').val() === 'USD' ? '$' : $('#currency').val() === 'EUR' ? '€' : '₺'}</span>
                </div>
            </td>
            <td style="padding: 20px 15px;">
                <div class="input-group">
                    <input type="text" class="form-control line-total" readonly value="0,00" style="min-height: 45px; font-size: 15px; border-radius: 8px 0 0 8px; background-color: #f8f9fa;">
                    <span class="input-group-text invoice-currency-symbol" style="min-height: 45px; font-size: 14px; border-radius: 0 8px 8px 0; background-color: #f8f9fa;">${$('#currency').val() === 'USD' ? '$' : $('#currency').val() === 'EUR' ? '€' : '₺'}</span>
                </div>
            </td>
            <td style="padding: 20px 15px; text-align: center;">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item" style="border-radius: 8px; padding: 8px 12px;">
                    <iconify-icon icon="solar:trash-bin-minimalistic-outline"></iconify-icon>
                </button>
            </td>
            <!-- Hidden fields for product_id and type -->
            <input type="hidden" name="items[${itemCounter}][product_id]" value="">
            <input type="hidden" name="items[${itemCounter}][type]" value="">
        </tr>
    `;
    
    $('#invoiceItemsBody').append(rowHtml);
    itemCounter++;
    
    // Add event listeners for calculations
    const newRow = $(`tr[data-item-index="${itemCounter - 1}"]`);
    newRow.find('.unit-price, .discount-rate, .tax-rate, .unit-currency').on('input change', calculateLineTotal);
    newRow.find('input[name*="[quantity]"]').on('input', calculateLineTotal);
}

function addMobileInvoiceItemRow() {
    console.log('Adding mobile invoice item row, current counter:', itemCounter);
    
    const mobileCardHtml = `
        <div class="card mb-3" data-item-index="${itemCounter}" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-semibold text-primary">Ürün/Hizmet ${itemCounter + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-mobile-item" style="border-radius: 8px;">
                        <iconify-icon icon="solar:trash-bin-minimalistic-outline"></iconify-icon>
                    </button>
                </div>
                
                <!-- Product/Service Search -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Ürün/Hizmet</label>
                    <div class="position-relative">
                        <input type="text" name="items[${itemCounter}][product_service_name]" class="form-control product-service-search" placeholder="Ürün/Hizmet ara..." data-row="${itemCounter}" required style="min-height: 50px; font-size: 16px; border-radius: 10px;">
                        <div id="productServiceDropdown${itemCounter}" class="product-service-dropdown" style="display: none;">
                            <!-- Search results will be populated here -->
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Açıklama</label>
                    <textarea name="items[${itemCounter}][description]" class="form-control" rows="2" placeholder="Açıklama" style="min-height: 50px; font-size: 14px; border-radius: 10px; resize: vertical;"></textarea>
                </div>
                
                
                
                <!-- Color Selection (will be added dynamically) -->
                <div class="mb-3 color-selection-mobile" style="display: none;">
                    <label class="form-label fw-semibold">Renk</label>
                    <select name="items[${itemCounter}][color_variant_id]" class="form-select color-variant-select" style="min-height: 50px; font-size: 14px; border-radius: 10px;">
                        <option value="">Renk Seçin</option>
                    </select>
                    <input type="hidden" name="items[${itemCounter}][selected_color]" value="">
                </div>
                
                <!-- Quantity and Price Row -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Miktar</label>
                    <div class="input-group">
                        <input type="number" name="items[${itemCounter}][quantity]" class="form-control quantity-input" value="1" min="0.01" step="0.01" required style="min-height: 50px; font-size: 16px; border-radius: 10px 0 0 10px;">
                        <span class="input-group-text quantity-unit" style="min-height: 50px; font-size: 14px; border-radius: 0 10px 10px 0;">Adet</span>
                    </div>
                    <small class="text-danger stock-zero" style="display:none; font-size: 12px;">0 Adet</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Birim Fiyat</label>
                    <div class="input-group">
                        <input type="number" name="items[${itemCounter}][unit_price]" class="form-control unit-price" value="0" min="0" step="0.01" required style="min-height: 50px; font-size: 16px; border-radius: 10px 0 0 10px;">
                        <select name="items[${itemCounter}][unit_currency]" class="form-select unit-currency" style="max-width: 80px; min-height: 50px; font-size: 14px; border-radius: 0 10px 10px 0;">
                            <option value="TRY" ${$('#currency').val() === 'TRY' ? 'selected' : ''}>₺</option>
                            <option value="USD" ${$('#currency').val() === 'USD' ? 'selected' : ''}>$</option>
                            <option value="EUR" ${$('#currency').val() === 'EUR' ? 'selected' : ''}>€</option>
                        </select>
                    </div>
                </div>
                
                <!-- Tax and Discount Row -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">KDV</label>
                    <select name="items[${itemCounter}][tax_rate]" class="form-select tax-rate" style="min-height: 50px; font-size: 14px; border-radius: 10px;">
                        <option value="0">KDV %0</option>
                        <option value="1">KDV %1</option>
                        <option value="10" selected>KDV %10</option>
                        <option value="20">KDV %20</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">İndirim (Tutar)</label>
                    <div class="input-group">
                        <input type="number" name="items[${itemCounter}][discount_rate]" class="form-control discount-rate" value="0" min="0" step="0.01" style="min-height: 50px; font-size: 16px; border-radius: 10px 0 0 10px;">
                        <span class="input-group-text discount-currency-symbol" style="min-height: 50px; font-size: 14px; border-radius: 0 10px 10px 0;">${$('#currency').val() === 'USD' ? '$' : $('#currency').val() === 'EUR' ? '€' : '₺'}</span>
                    </div>
                </div>
                
                <!-- Total -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Toplam</label>
                    <div class="input-group">
                        <input type="text" class="form-control line-total" readonly value="0,00" style="min-height: 50px; font-size: 16px; border-radius: 10px 0 0 10px; background-color: #f8f9fa;">
                        <span class="input-group-text invoice-currency-symbol" style="min-height: 50px; font-size: 14px; border-radius: 0 10px 10px 0; background-color: #f8f9fa;">${$('#currency').val() === 'USD' ? '$' : $('#currency').val() === 'EUR' ? '€' : '₺'}</span>
                    </div>
                </div>
                
                <!-- Hidden fields for product_id and type -->
                <input type="hidden" name="items[${itemCounter}][product_id]" value="">
                <input type="hidden" name="items[${itemCounter}][type]" value="">
            </div>
        </div>
    `;
    
    $('#mobileInvoiceItems').append(mobileCardHtml);
    itemCounter++;
    
    // Add event listeners for calculations
    const newCard = $(`.card[data-item-index="${itemCounter - 1}"]`);
    newCard.find('.unit-price, .discount-rate, .tax-rate, .unit-currency').on('input change', calculateMobileLineTotal);
    newCard.find('input[name*="[quantity]"]').on('input', calculateMobileLineTotal);
}

function calculateMobileLineTotal() {
    const card = $(this).closest('.card');
    const quantity = parseFloat(card.find('input[name*="[quantity]"]').val()) || 0;
    let unitPrice = parseFloat(card.find('.unit-price').val()) || 0;
    const discountRate = parseFloat(card.find('.discount-rate').val()) || 0;
    const unitCurrency = card.find('.unit-currency').val();
    const taxRate = parseFloat(card.find('.tax-rate').val()) || 0;
    
    // Currency conversion if needed (use displayed exchange rate)
    if (unitCurrency !== $('#currency').val()) {
        const exchangeRate = parseFloat($('#exchangeRate').val()) || 1;
        unitPrice = unitPrice / (unitCurrency === 'USD' || unitCurrency === 'EUR' ? 1 : 1); // keep raw
        // Convert entered unit price to invoice currency via TRY pivot
        // unit price assumed in its own currency; convert to TRY then to invoice currency using API rates
        const invoiceCurrency = $('#currency').val();
        const rates = getExchangeRates();
        const priceInTRY = unitCurrency === 'TRY' ? unitPrice : (unitPrice * rates[unitCurrency]);
        unitPrice = invoiceCurrency === 'TRY' ? priceInTRY : (priceInTRY / rates[invoiceCurrency]);
    }
    
    // Calculate discount (fixed amount)
    const discountAmount = Math.max(0, Math.min(discountRate, unitPrice * quantity));
    const subtotal = (unitPrice * quantity) - discountAmount;
    
    // Calculate tax
    const taxAmount = (subtotal * taxRate) / 100;
    const total = subtotal + taxAmount;
    
    // Update line total
    card.find('.line-total').val(total.toFixed(2).replace('.', ','));
    
    // Update currency symbol
    card.find('.invoice-currency-symbol').text($('#currency').val() === 'USD' ? '$' : $('#currency').val() === 'EUR' ? '€' : '₺');
    
    // Calculate totals
    calculateTotals();
}

function calculateLineTotal() {
    const row = $(this).closest('tr');
    // Re-validate stock first; if invalid, zero the line and totals
    const valid = validateStock(row);
    const quantity = parseFloat(row.find('input[name*="[quantity]"]').val()) || 0;
    let unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
    const discountRate = parseFloat(row.find('.discount-rate').val()) || 0;
    const unitCurrency = row.find('.unit-currency').val();
    const invoiceCurrency = $('#currency').val();
    console.log('calcLine:', { quantity, unitPrice, discountRate, unitCurrency, invoiceCurrency });
    
    // Convert unit price from unit currency to invoice currency
    if (unitCurrency !== invoiceCurrency) {
        const exchangeRates = getExchangeRates();
        // Convert: unitPrice (in unit currency) -> TRY -> invoice currency
        const priceInTL = unitPrice * (exchangeRates[unitCurrency] || 1);
        unitPrice = priceInTL / (exchangeRates[invoiceCurrency] || 1);
    }
    
    let lineTotal = quantity * unitPrice;
    const discountAmount = Math.max(0, Math.min(discountRate, lineTotal));
    let lineTotalAfterDiscount = lineTotal - discountAmount;
    
    if (!valid || row.data('invalid-stock')) {
        lineTotalAfterDiscount = 0;
    }
    
    row.find('.line-total').val(lineTotalAfterDiscount.toFixed(2).replace('.', ','));
    
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let totalDiscount = 0;
    let totalVat = 0;
    
    // Calculate from desktop table rows
    $('#invoiceItemsBody tr').each(function() {
        const lineTotalVal = $(this).find('.line-total').val() || '0';
        const lineTotal = parseFloat(lineTotalVal.replace(',', '.')) || 0;
        const discountFixed = parseFloat($(this).find('.discount-rate').val()) || 0;
        const taxRate = parseFloat($(this).find('.tax-rate').val()) || 0;
        console.log('rowTotals:', { lineTotal, discountFixed, taxRate });
        
        // Line total already includes discount
        subtotal += lineTotal;
        totalDiscount += discountFixed;
        
        if ($('select[name="vat_status"]').val() === 'included') {
            totalVat += lineTotal * (taxRate / 100);
        }
    });
    
    // Calculate from mobile cards
    $('#mobileInvoiceItems .card').each(function() {
        const lineTotal = parseFloat($(this).find('.line-total').val().replace(',', '.')) || 0;
        const discountFixed = parseFloat($(this).find('.discount-rate').val()) || 0;
        const taxRate = parseFloat($(this).find('.tax-rate').val()) || 0;
        console.log('rowTotalsMobile:', { lineTotal, discountFixed, taxRate });
        // Line total already includes discount
        subtotal += lineTotal;
        totalDiscount += discountFixed;
        
        if ($('select[name="vat_status"]').val() === 'included') {
            totalVat += lineTotal * (taxRate / 100);
        }
    });
    
    const totalAmount = subtotal + totalVat;
    const selectedCurrency = $('#currency').val();
    const currencySymbol = selectedCurrency === 'USD' ? '$' : selectedCurrency === 'EUR' ? '€' : '₺';
    console.log('totals:', { subtotal, totalVat, totalAmount, selectedCurrency });
    
    // Always show totals in the selected currency
    $('#subtotal').text(subtotal.toFixed(2).replace('.', ',') + ' ' + currencySymbol);
    $('#discount').text(totalDiscount.toFixed(2).replace('.', ',') + ' ' + currencySymbol);
    $('#additionalDiscount').text('0,00 ' + currencySymbol);
    $('#vatAmount').text(totalVat.toFixed(2).replace('.', ',') + ' ' + currencySymbol);
    $('#totalAmount').text(totalAmount.toFixed(2).replace('.', ',') + ' ' + currencySymbol);
    
    // Do not show TL equivalent; totals are always shown in selected currency.
}

function updateCurrencySymbols(symbol) {
    // Update invoice currency symbols in totals
    $('.invoice-currency-symbol').each(function() {
        $(this).text(symbol);
    });
    // Update discount suffix symbols
    $('.discount-currency-symbol').each(function(){
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
        url: '{{ route("sales.invoices.currency.rates") }}',
        async: false,
        success: function(response) {
            if (response.success && response.rates) {
                currentRates['USD'] = response.rates['USD'];
                currentRates['EUR'] = response.rates['EUR'];
                
                // Show manual input fields if rates are null
                if (response.rates['USD'] === null || response.rates['EUR'] === null) {
                    showManualRateInputs(response.rates);
                }
            }
            console.log('rates:', response);
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
    $.get('{{ route("sales.invoices.currency.rates") }}')
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

function showManualRateInputs(rates) {
    let modalHtml = `
        <div class="modal fade" id="manualRatesModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Döviz Kurlarını Manuel Girin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="ri-warning-line me-2"></i>
                            Trunçgil API'den kur bilgisi alınamadı. Lütfen güncel kurları manuel olarak girin.
                        </div>`;
    
    if (rates['USD'] === null) {
        modalHtml += `
            <div class="mb-3">
                <label for="manualUsdRate" class="form-label">USD Kuru (1 USD = ? TRY)</label>
                <input type="number" step="0.0001" class="form-control" id="manualUsdRate" placeholder="41.29">
            </div>`;
    }
    
    if (rates['EUR'] === null) {
        modalHtml += `
            <div class="mb-3">
                <label for="manualEurRate" class="form-label">EUR Kuru (1 EUR = ? TRY)</label>
                <input type="number" step="0.0001" class="form-control" id="manualEurRate" placeholder="48.55">
            </div>`;
    }
    
    modalHtml += `
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="button" class="btn btn-primary" onclick="saveManualRates()">Kurları Kaydet</button>
                    </div>
                </div>
            </div>
        </div>`;
    
    // Remove existing modal if any
    $('#manualRatesModal').remove();
    
    // Add modal to body and show
    $('body').append(modalHtml);
    $('#manualRatesModal').modal('show');
}

function showManualRateInput(currency) {
    const rate = prompt(`${currency} kuru giriniz (1 ${currency} = ? TRY):`, currency === 'USD' ? '41.29' : '48.55');
    if (rate && !isNaN(rate)) {
        $('#exchangeRate').val(parseFloat(rate).toFixed(4));
    }
}

function saveManualRates() {
    const usdRate = $('#manualUsdRate').val();
    const eurRate = $('#manualEurRate').val();
    
    if (usdRate && !isNaN(usdRate)) {
        // Update the global rates object
        window.manualRates = window.manualRates || {};
        window.manualRates['USD'] = parseFloat(usdRate);
    }
    
    if (eurRate && !isNaN(eurRate)) {
        window.manualRates = window.manualRates || {};
        window.manualRates['EUR'] = parseFloat(eurRate);
    }
    
    $('#manualRatesModal').modal('hide');
    
    // Show success message
    toastr.success('Döviz kurları başarıyla kaydedildi!');
}

function saveNewCustomer() {
    console.log('saveNewCustomer function called');
    
    const name = $('#newCustomerName').val();
    const company = $('#newCustomerCompany').val();
    const email = $('#newCustomerEmail').val();
    const phone = $('#newCustomerPhone').val();
    const address = $('#newCustomerAddress').val();
    
    console.log('Customer data:', { name, company, email, phone, address });
    
    if (!name || !phone) {
        alert('Ad Soyad ve Telefon alanları zorunludur.');
        return;
    }
    
    console.log('Sending AJAX request to:', '{{ route("sales.customers.store") }}');
    
    $.post('{{ route("sales.customers.store") }}', {
        name: name,
        company_name: company,
        email: email,
        phone: phone,
        address: address,
        is_active: 1
    })
    .done(function(response) {
        console.log('Customer created successfully:', response);
        
        // Close modal
        $('#customerModal').modal('hide');
        
        // Clear form
        $('#newCustomerForm')[0].reset();
        
        // Set the new customer as selected
        $('#customerId').val(response.customer.id);
        $('#customerSearch').val(response.customer.name);
        
        // Show customer info
        let infoHtml = `<div class="text-success"><iconify-icon icon="solar:check-circle-outline" class="me-1"></iconify-icon>${response.customer.name}`;
        if (response.customer.company_name) infoHtml += ` - ${response.customer.company_name}`;
        if (response.customer.email) infoHtml += ` (${response.customer.email})`;
        infoHtml += `</div>`;
        $('#customerInfo').html(infoHtml).show();
        
        alert('Müşteri başarıyla eklendi!');
    })
    .fail(function(xhr, status, error) {
        console.error('Customer creation failed:', xhr.responseText);
        console.error('Status:', status, 'Error:', error);
        
        let errorMessage = 'Müşteri eklenirken bir hata oluştu.';
        
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
    
    $.get('{{ route("sales.invoices.search.products") }}', { q: query })
        .done(function(data) {
            const dropdown = $(`#productServiceDropdown${rowIndex}`);
            // Ensure dropdown can be shown again after force hide
            dropdown.removeClass('hidden');
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
                    } else if (item.type === 'series') {
                        if (item.product_code) details.push(`Kod: ${item.product_code}`);
                        if (item.category) details.push(`Kategori: ${item.category}`);
                        if (item.brand) details.push(`Marka: ${item.brand}`);
                        if (item.series_size) details.push(`Seri Boyutu: ${item.series_size} adet`);
                        if (item.stock_quantity !== undefined) details.push(`Stok: ${item.stock_quantity} seri`);
                    } else {
                        if (item.product_code) details.push(`Kod: ${item.product_code}`);
                        if (item.category) details.push(`Kategori: ${item.category}`);
                    }
                    
                    const itemHtml = `
                        <div class="dropdown-item product-service-item" 
                             data-name="${item.name}" 
                             data-price="${item.price}" 
                             data-currency="${item.currency || 'TRY'}"
                             data-vat-rate="${item.vat_rate}"
                             data-product-id="${item.product_id ? item.product_id : (item.id ? (''+item.id).replace('product_','') : '')}"
                             data-purchase-price="${item.purchase_price || ''}"
                             data-type="${item.type}"
                             data-code="${item.code || ''}"
                             data-category="${item.category || ''}"
                             data-brand="${item.brand || ''}"
                             data-size="${item.size || ''}"
                             data-color="${item.color || ''}"
                             data-sizes="${item.sizes ? JSON.stringify(item.sizes) : ''}"
                             data-stock-quantity="${item.stock_quantity || 0}"
                             data-has-color-variants="${item.has_color_variants || false}"
                             data-color-variants='${JSON.stringify(item.color_variants || [])}'
                             data-preferred-variant-id="${item.preferred_color_variant_id || ''}"
                             style="cursor: pointer; padding: 16px 20px; border-bottom: 1px solid #f0f0f0;">
                            <div class="row">
                                <div class="col-8">
                                    <div class="mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <strong class="me-2" style="font-size: 15px; color: #212529;">${item.name}</strong>
                                            <span class="badge bg-${item.type === 'product' ? 'primary' : item.type === 'series' ? 'info' : 'success'}" style="font-size: 11px; padding: 3px 8px;">${item.type === 'product' ? 'Ürün' : item.type === 'series' ? 'Seri' : 'Hizmet'}</span>
                                        </div>
                                        ${details.length > 0 ? `<div class="text-muted" style="font-size: 13px; line-height: 1.4; color: #6c757d;">${details.join(' • ')}</div>` : ''}
                                    </div>
                                    ${item.type === 'series' && item.sizes && item.sizes.length > 0 ? 
                                        `<div class="mt-2">
                                            ${item.sizes.map(size => `<span class="badge bg-info me-1" style="font-size: 10px; padding: 2px 6px; border-radius: 4px;">${size}</span>`).join('')}
                                        </div>` : ''}
                                </div>
                                <div class="col-4 text-end">
                                    <div class="fw-bold text-success" style="font-size: 16px;">${parseFloat(item.price).toFixed(2)} ${item.currency || 'TRY'}</div>
                                </div>
                            </div>
                        </div>
                    `;
                    dropdown.append(itemHtml);
                });
            }
            
            // Position dropdown as separate box below the row/card
            const input = $(`.product-service-search[data-row="${rowIndex}"]`);
            if (input.length > 0) {
                const inputRect = input[0].getBoundingClientRect();
                const row = input.closest('tr');
                const card = input.closest('.card[data-item-index]');
                
                // Move dropdown to body for better positioning
                dropdown.appendTo('body');
                
                // Check if mobile
                const isMobile = window.innerWidth < 992;
                const isTablet = window.innerWidth <= 1024 && window.innerWidth >= 768;
                
                let dropdownConfig = {};
                
                if (isMobile) {
                    // Mobile positioning - centered below input
                    dropdownConfig = {
                        'position': 'fixed',
                        'top': (inputRect.bottom + 10) + 'px',
                        'left': '5%',
                        'right': '5%',
                        'width': '90%',
                        'max-width': '100%',
                        'z-index': 9999,
                        'background': 'white',
                        'border': '2px solid #007bff',
                        'border-radius': '12px',
                        'box-shadow': '0 8px 24px rgba(0, 0, 0, 0.2)',
                        'max-height': '60vh',
                        'overflow-y': 'auto',
                        'margin-top': '0',
                        'padding': '0'
                    };
                } else if (isTablet) {
                    // Tablet positioning
                    const containerRect = row.length ? row[0].getBoundingClientRect() : (card.length ? card[0].getBoundingClientRect() : inputRect);
                    dropdownConfig = {
                        'position': 'fixed',
                        'top': (containerRect.bottom + 10) + 'px',
                        'left': Math.max(20, Math.min(inputRect.left, window.innerWidth - 470)) + 'px',
                        'width': '450px',
                        'z-index': 9999,
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
                    const containerRect = row.length ? row[0].getBoundingClientRect() : inputRect;
                    dropdownConfig = {
                        'position': 'fixed',
                        'top': (containerRect.bottom + 10) + 'px',
                        'left': Math.max(20, Math.min(inputRect.left, window.innerWidth - 520)) + 'px',
                        'width': '500px',
                        'z-index': 9999,
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
            
            // Ensure dropdown is visible even if it was force-hidden before
            dropdown.removeClass('hidden');
            // Clear any inline styles that might prevent showing
            dropdown.css({ 
                'visibility': 'visible', 
                'opacity': '1', 
                'display': 'block',
                'pointer-events': 'auto'
            });
            dropdown.show();
        })
        .fail(function() {
            console.error('Product/Service search failed');
        });
}

// Handle product/service selection (works for both table rows and mobile cards)
$(document).on('click', '.product-service-item', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    // Get the row index from the dropdown ID
    const dropdownId = $(this).closest('.product-service-dropdown').attr('id');
    const rowIndex = dropdownId.replace('productServiceDropdown', '');
    
    // Find the corresponding row or card
    const row = $(`tr[data-item-index="${rowIndex}"]`);
    const card = $(`.card[data-item-index="${rowIndex}"]`);
    const container = row.length ? row : card;
    
    if (container.length === 0) {
        console.error('Row/Card not found for index:', rowIndex);
        return;
    }
    
    const name = $(this).data('name');
    const price = $(this).data('price');
    const vatRate = $(this).data('vat-rate');
    const type = $(this).data('type');
    const productId = $(this).data('product-id');
    const hasColorVariants = $(this).data('has-color-variants');
    const colorVariants = $(this).data('color-variants');
    
    console.log('Product selected:', name, price, vatRate, type);
    
    // Set the product/service name
    container.find('input[name*="[product_service_name]"]').val(name);
    
    // Set the unit price as provided and select item's currency (no conversion)
    const itemCurrency = $(this).data('currency') || $('#currency').val();
    container.find('.unit-currency').val(itemCurrency);
    container.find('input[name*="[unit_price]"]').val(price);
    
    // Set the tax rate
    container.find('select[name*="[tax_rate]"]').val(vatRate);
    
    // Set hidden fields for product_id and type
    container.find('input[name*="[product_id]"]').val(productId);
    container.find('input[name*="[type]"]').val(type);
    
    // Handle color variants
    if (hasColorVariants && colorVariants && colorVariants.length > 0) {
        const preferredVariantId = $(this).data('preferred-variant-id') || null;
        if (row.length) {
            // Desktop table row
            addColorColumnToTable();
            addColorCellToRow(row, colorVariants);
            row.data('color-variants', colorVariants);
            if (preferredVariantId) {
                const colorSelect = row.find('.color-variant-select');
                if (colorSelect.length) {
                    colorSelect.val(preferredVariantId).trigger('change');
                }
            }
        } else if (card.length) {
            // Mobile card
            card.find('.color-selection-mobile').show();
            const colorSelect = card.find('.color-variant-select');
            colorSelect.empty().append('<option value="">Renk Seçin</option>');
            colorVariants.forEach(function(variant) {
                const stockText = variant.stock_quantity ? ` (${variant.stock_quantity} adet)` : '';
                colorSelect.append(`<option value="${variant.id}" data-stock="${variant.stock_quantity}">${variant.color}${stockText}</option>`);
            });
            card.data('color-variants', colorVariants);
            if (preferredVariantId) {
                colorSelect.val(preferredVariantId).trigger('change');
            }
        }
    }
    
    // Store stock information for validation
    const stockQuantity = $(this).data('stock-quantity');
    container.data('stock-quantity', stockQuantity);
    container.data('product-type', type);
    
    // Hizmetler için invalid-stock flag'ini temizle ve type input'unu set et
    if (type === 'service') {
        container.removeData('invalid-stock');
        container.find('input[name*="[type]"]').val('service');
    }

    // Show zero stock indicator if needed (but not for services)
    if (type !== 'service') {
        const parentRow = container.closest('tr');
        const parentCard = container.closest('.card');
        if (stockQuantity <= 0) {
            if (parentRow.length) {
                parentRow.find('.stock-zero').show();
            } else if (parentCard.length) {
                parentCard.find('.stock-zero').show();
            }
        } else {
            if (parentRow.length) {
                parentRow.find('.stock-zero').hide();
            } else if (parentCard.length) {
                parentCard.find('.stock-zero').hide();
            }
        }
    }
    
    // Hide dropdown immediately - hide the specific dropdown first
    const specificDropdown = $(`#productServiceDropdown${rowIndex}`);
    specificDropdown.addClass('hidden');
    specificDropdown.hide().fadeOut(0);
    // Ensure it's completely hidden
    specificDropdown.css({
        'display': 'none',
        'visibility': 'hidden',
        'opacity': '0'
    });
    
    // Then hide all dropdowns
    forceHideAllDropdowns();
    
    // Clear search input (but keep the selected name)
    container.find('input[name*="[product_service_name]"]').val(name);
    
    // Remove focus from the input to prevent immediate reopening
    container.find('input[name*="[product_service_name]"]').blur();
    
    // Recalculate totals
    if (row.length) {
        calculateLineTotal.call(row.find('.unit-price')[0]);
        // Validate stock only for non-service items
        if (type !== 'service') {
            setTimeout(() => validateStock(row), 100);
        }
    } else if (card.length) {
        calculateMobileLineTotal.call(card.find('.unit-price')[0]);
    }
    
    console.log('Product added successfully:', name);
});

// Force hide all dropdowns
function forceHideAllDropdowns() {
    // Get all dropdowns including those appended to body
    const dropdowns = $('.product-service-dropdown');
    
    dropdowns.each(function() {
        const $dropdown = $(this);
        // Add hidden class which has !important styles
        $dropdown.addClass('hidden');
        // Also force hide with jQuery
        $dropdown.hide();
        $dropdown.fadeOut(0);
        // Set inline styles as backup
        $dropdown.css({
            'display': 'none',
            'visibility': 'hidden',
            'opacity': '0',
            'pointer-events': 'none'
        });
    });
    
    // Additional cleanup - ensure no dropdowns are visible
    setTimeout(function() {
        $('.product-service-dropdown').each(function() {
            $(this).addClass('hidden');
            $(this).hide();
            $(this).css({
                'display': 'none',
                'visibility': 'hidden',
                'opacity': '0'
            });
        });
    }, 50);
}

// Handle product/service search input (works for table rows and mobile cards)
$(document).on('input', '.product-service-search', function() {
    const query = $(this).val();
    // Typing means the previous selection is no longer valid - clear hidden ids and color
    const container = $(this).closest('tr').length ? $(this).closest('tr') : $(this).closest('.card');
    if (container && container.length) {
        container.find('input[name*="[product_id]"]').val('');
        container.find('input[name*="[type]"]').val('');
        container.find('input[name*="[selected_color]"]').val('');
        container.find('.color-variant-select').val('');
        container.find('.color-cell').hide();
    }
    let rowIndex;
    const row = $(this).closest('tr');
    if (row.length) {
        rowIndex = row.data('item-index');
    } else {
        const card = $(this).closest('.card[data-item-index]');
        rowIndex = card.data('item-index');
    }
    if (rowIndex !== undefined) {
        searchProductsServices(query, rowIndex);
    }
});

// Trigger search on focus so it reappears after edits
$(document).on('focus', '.product-service-search', function() {
    const current = $(this).val();
    if (current && current.length >= 2) {
        let rowIndex;
        const row = $(this).closest('tr');
        if (row.length) {
            rowIndex = row.data('item-index');
        } else {
            const card = $(this).closest('.card[data-item-index]');
            rowIndex = card.data('item-index');
        }
        if (rowIndex !== undefined) {
            searchProductsServices(current, rowIndex);
        }
    }
});

// Hide dropdowns when clicking outside
$(document).on('click', function(e) {
    if (!$(e.target).closest('.position-relative').length && !$(e.target).closest('.product-service-dropdown').length) {
        forceHideAllDropdowns();
    }
});


// Function to add color column to table header
function addColorColumnToTable() {
    const header = $('#invoiceTableHeader');
    if (header.find('th:contains("RENK")').length === 0) {
        // Insert color column after description column
        header.find('th:nth-child(2)').after('<th style="min-width: 150px; width: 8%;">RENK</th>');
        
        // Adjust other column widths using header text instead of fixed indexes
        header.find('th:contains("ÜRÜN/HİZMET")').attr('style', 'min-width: 200px; width: 18%;');
        header.find('th:contains("AÇIKLAMA")').attr('style', 'min-width: 150px; width: 12%;');
        header.find('th:contains("MİKTAR")').attr('style', 'min-width: 140px; width: 10%;');
        header.find('th:contains("B. FİYAT")').attr('style', 'min-width: 160px; width: 12%;');
        header.find('th:contains("KDV")').attr('style', 'min-width: 120px; width: 8%;');
        header.find('th:contains("İNDİRİM")').attr('style', 'min-width: 120px; width: 8%;');
        header.find('th:contains("TOPLAM")').attr('style', 'min-width: 160px; width: 10%;');
        header.find('th:contains("İŞLEM")').attr('style', 'min-width: 80px; width: 5%;');
        
        // Update table min-width when color column is added
        $('#invoiceItemsTable').css('min-width', '1700px');
        
        // Add color cells to all existing rows that don't have them
        $('#invoiceItemsBody tr').each(function() {
            const row = $(this);
            if (row.find('.color-cell').length === 0) {
                const rowIndex = row.data('item-index');
                row.find('td:nth-child(2)').after(`
                    <td class="color-cell">
                        <select name="items[${rowIndex}][color_variant_id]" class="form-select color-variant-select" style="min-height: 50px; font-size: 14px;">
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
                <select name="items[${rowIndex}][color_variant_id]" class="form-select color-variant-select" style="min-height: 50px; font-size: 14px;">
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

    // If there is exactly one color variant, preselect it and persist its name
    if (colorVariants.length === 1) {
        const v = colorVariants[0];
        colorSelect.val(v.id).trigger('change');
    }
}

// Stock validation function
function validateStock(row) {
    // Support both desktop table rows and mobile cards
    const isCard = row.hasClass('card');
    const quantityInput = isCard ? row.find('input[name*="[quantity]"]') : row.find('input[name*="[quantity]"]');
    const quantity = parseFloat(quantityInput.val()) || 0;
    const productType = row.data('product-type') || row.find('input[name*="[type]"]').val() || 'product';
    
    // Hizmetler için stok kontrolü yapma
    if (productType === 'service') {
        // Clear any existing stock warnings for services
        row.find('.stock-warning').remove();
        row.next('.stock-warning-row').remove();
        row.removeClass('table-danger');
        row.find('td').removeClass('bg-danger-subtle');
        row.find('.stock-zero').hide();
        row.removeData('invalid-stock');
        return true;
    }
    
    const productName = row.find('input[name*="[product_service_name]"]').val() || 'Ürün';
    const productId = (row.find('input[name*="[product_id]"]').val() || '').toString();
    
    // Check if product has color variants
    const colorSelect = row.find('.color-variant-select');
    let stockQuantity = 0;
    let selectedColor = '';
    
    const colorVariantId = colorSelect.length > 0 ? (colorSelect.val() || '') : '';
    if (colorSelect.length > 0 && colorSelect.val()) {
        // Color variant selected - use color-specific stock
        const selectedOption = colorSelect.find('option:selected');
        stockQuantity = parseInt(selectedOption.data('stock')) || 0;
        selectedColor = selectedOption.text().split(' (')[0]; // Get color name without stock info
    } else {
        // No color variant - use general stock
        stockQuantity = row.data('stock-quantity') || 0;
    }
    
    // Sum quantities across all rows/cards for the same product and variant
    let totalRequested = 0;
    const keyVariant = colorVariantId || 'no_variant';
    // table rows
    $('tbody tr').each(function() {
        const r = $(this);
        const pid = (r.find('input[name*="[product_id]"]').val() || '').toString();
        const type = r.data('product-type') || 'product';
        const variantSel = r.find('.color-variant-select');
        const vid = variantSel.length > 0 ? (variantSel.val() || '') : '';
        if (pid && pid === productId && type === productType && (vid || 'no_variant') === keyVariant) {
            totalRequested += parseFloat(r.find('input[name*="[quantity]"]').val()) || 0;
        }
    });
    // mobile cards
    $('.card[data-item-index]').each(function() {
        const c = $(this);
        const pid = (c.find('input[name*="[product_id]"]').val() || '').toString();
        const type = c.data('product-type') || 'product';
        const variantSel = c.find('.color-variant-select');
        const vid = variantSel.length > 0 ? (variantSel.val() || '') : '';
        if (pid && pid === productId && type === productType && (vid || 'no_variant') === keyVariant) {
            totalRequested += parseFloat(c.find('input[name*="[quantity]"]').val()) || 0;
        }
    });
    
    // Remaining stock considering other lines
    const otherRequested = totalRequested - quantity;
    const remaining = Math.max(0, stockQuantity - otherRequested);
    
    // Clear previous warnings and styling
    row.find('.stock-warning').remove();
    row.next('.stock-warning-row').remove(); // Remove warning row below
    row.removeClass('table-danger');
    row.find('td').removeClass('bg-danger-subtle');
    row.find('.stock-zero').hide();
    row.removeData('invalid-stock');

    if (stockQuantity <= 0) {
        // Show zero stock indicator and mark invalid (line total forced to 0)
        row.find('.stock-zero').show();
        row.addClass('table-danger');
        row.find('td').addClass('bg-danger-subtle');
        row.data('invalid-stock', true);
        const warningHtml = `
            <tr class="stock-warning-row">
                <td colspan="8" class="p-0">
                    <div class="stock-warning alert alert-danger alert-sm m-2" style="padding: 8px 12px; font-size: 0.875rem; border: none; border-radius: 6px;">
                        <i class="ri-alert-line me-2"></i>
                        <strong>STOK YOK!</strong> Bu ürün${selectedColor ? ' (' + selectedColor + ')' : ''} için stok bulunmamaktadır.
                    </div>
                </td>
            </tr>
        `;
        row.after(warningHtml);
        // Force line total to 0 and refresh totals
        row.find('.line-total').val('0,00');
        calculateTotals();
        return false;
    }
    
    if (quantity > 0 && stockQuantity > 0 && quantity > remaining) {
        // Mark as invalid instead of capping quantity; force line total to 0
        row.addClass('table-danger');
        row.find('td').addClass('bg-danger-subtle');
        row.data('invalid-stock', true);
        
        // Add warning message below the row
        const colorInfo = selectedColor ? ` (${selectedColor} rengi)` : '';
        const warningHtml = `
            <tr class="stock-warning-row">
                <td colspan="8" class="p-0">
                    <div class="stock-warning alert alert-danger alert-sm m-2" style="padding: 8px 12px; font-size: 0.875rem; border: none; border-radius: 6px;">
                        <i class="ri-alert-line me-2"></i>
                        <strong>YETERSİZ STOK!</strong> 
                        ${productType === 'series' ? 'Seri' : 'Ürün'}${colorInfo} stokta: ${stockQuantity} ${productType === 'series' ? 'seri' : 'adet'}, 
                        mevcut satırlardaki toplam istek: ${otherRequested + quantity} ${productType === 'series' ? 'seri' : 'adet'}. Bu satır için en fazla ${remaining} ekleyebilirsiniz.
                    </div>
                </td>
            </tr>
        `;
        
        // Remove existing warning row if any
        row.next('.stock-warning-row').remove();
        // Add warning row after current row
        row.after(warningHtml);
        // Force line total to 0 and refresh totals
        row.find('.line-total').val('0,00');
        calculateTotals();
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

// Form submission validation - STOK KONTROLÜ
$('#invoiceForm').on('submit', function(e) {
    console.log('=== FORM SUBMIT HANDLER 2 (STOK KONTROLÜ) ===');
    console.log('=== STOK KONTROLÜ BAŞLADI ===');
    let hasStockError = false;
    let errorMessages = [];
    let rowCount = 0;
    
    $('tbody tr').each(function() {
        rowCount++;
        const row = $(this);
        const productTypeFromData = row.data('product-type');
        const productTypeFromInput = row.find('input[name*="[type]"]').val();
        const productType = productTypeFromData || productTypeFromInput || 'product';
        
        // Hizmetleri kontrol et - input değeri boş string olsa bile kontrol et
        const productName = row.find('input[name*="[product_service_name]"]').val() || '';
        const isService = productType === 'service' || productTypeFromInput === 'service' || 
                          (productName && productName.toLowerCase().includes('hizmet'));
        
        console.log(`Satır ${rowCount}:`, {
            productTypeFromData,
            productTypeFromInput,
            productType,
            productName,
            isService,
            invalidStock: row.data('invalid-stock')
        });
        
        // Hizmetler için stok kontrolü yapma ve invalid-stock flag'ini temizle
        if (isService) {
            console.log(`Satır ${rowCount}: Hizmet olduğu için stok kontrolü atlandı ve invalid-stock flag temizlendi`);
            row.removeData('invalid-stock');
            return true; // continue to next iteration
        }
        
        if (row.data('invalid-stock')) {
            console.log(`Satır ${rowCount}: invalid-stock flag bulundu`);
            hasStockError = true;
        }
        const quantity = parseFloat(row.find('input[name*="[quantity]"]').val()) || 0;
        const productId = (row.find('input[name*="[product_id]"]').val() || '').toString();
        
        console.log(`Satır ${rowCount}: Miktar=${quantity}, Ürün=${productName}, ID=${productId}`);
        
        // Check if product has color variants
        const colorSelect = row.find('.color-variant-select');
        let stockQuantity = 0;
        let selectedColor = '';
        
        const colorVariantId = colorSelect.length > 0 ? (colorSelect.val() || '') : '';
        if (colorSelect.length > 0 && colorSelect.val()) {
            // Color variant selected - use color-specific stock
            const selectedOption = colorSelect.find('option:selected');
            stockQuantity = parseInt(selectedOption.data('stock')) || 0;
            selectedColor = selectedOption.text().split(' (')[0]; // Get color name without stock info
            console.log(`Satır ${rowCount}: Renk varyantı seçilmiş, stok=${stockQuantity}`);
        } else {
            // No color variant - use general stock
            stockQuantity = row.data('stock-quantity') || 0;
            console.log(`Satır ${rowCount}: Genel stok=${stockQuantity}`);
        }
        
        // Sum quantities for same product (and selected color variant)
        let totalRequested = 0;
        const keyVariant = colorVariantId || 'no_variant';
        $('tbody tr').each(function() {
            const r = $(this);
            const pid = (r.find('input[name*="[product_id]"]').val() || '').toString();
            const type = r.data('product-type') || r.find('input[name*="[type]"]').val() || 'product';
            const variantSel = r.find('.color-variant-select');
            const vid = variantSel.length > 0 ? (variantSel.val() || '') : '';
            if (pid && pid === productId && type === productType && (vid || 'no_variant') === keyVariant) {
                totalRequested += parseFloat(r.find('input[name*="[quantity]"]').val()) || 0;
            }
        });
        const remaining = stockQuantity - (totalRequested - quantity);
        console.log(`Satır ${rowCount}: Stok=${stockQuantity}, Toplam İstenen=${totalRequested}, Kalan=${remaining}`);
        
        if (quantity > 0 && stockQuantity > 0 && quantity > remaining) {
            console.log(`Satır ${rowCount}: STOK HATASI! Miktar=${quantity}, Kalan=${remaining}`);
            hasStockError = true;
            const colorInfo = selectedColor ? ` (${selectedColor} rengi)` : '';
            errorMessages.push(`${productName}${colorInfo}: Stok ${stockQuantity}. Satırlardaki toplam istek ${totalRequested}. Bu satır için en fazla ${Math.max(0, remaining)} girilebilir.`);
        }
    });
    
    console.log('=== STOK KONTROLÜ SONU ===');
    console.log('Toplam Satır:', rowCount);
    console.log('Stok Hatası Var mı?', hasStockError);
    console.log('Hata Mesajları:', errorMessages);
    
    // Ek doğrulama: ürün seçimi ve renk seçimi
    let metaError = false;
    $('#invoiceItemsBody tr').each(function(idx){
        const r = $(this);
        const pName = r.find('input[name*="[product_service_name]"]').val() || '';
        const pId = (r.find('input[name*="[product_id]"]').val() || '').toString();
        const pType = r.find('input[name*="[type]"]').val() || r.data('product-type') || 'product';
        if (pName.trim() !== '' && pType !== 'service' && !pId) {
            errorMessages.push(`Satır ${idx+1}: Lütfen listeden bir ürün/seri seçin.`);
            metaError = true;
        }
        const colorSel = r.find('.color-variant-select');
        if (pType !== 'service' && colorSel.length > 0) {
            const hasOptions = colorSel.find('option').length > 1;
            const val = colorSel.val();
            if (hasOptions && !val) {
                errorMessages.push(`Satır ${idx+1}: Renk seçiniz.`);
                metaError = true;
            }
        }
    });

    if (hasStockError || metaError) {
        console.log('FORM SUBMIT ENGELLENDİ - Stok hatası var');
        e.preventDefault();
        alert(errorMessages.join('\n'));
        return false;
    }
    
    console.log('FORM SUBMIT ONAYLANDI - Stok kontrolü başarılı');
});
</script>

<script>
// Seri boyutu alanı kaldırıldı; hesaplamalar ve event'ler devre dışı bırakıldı

// Mobile color selection with stock validation
$(document).on('change', '.card .color-variant-select', function() {
    const card = $(this).closest('.card');
    const selectedColorId = $(this).val();
    const selectedColor = $(this).find('option:selected').data('color');
    const colorVariants = card.data('color-variants');
    const quantityInput = card.find('.quantity-input');
    const seriesSizeSelect = card.find('.series-size-select');
    
    // Update hidden field
    card.find('input[name*="[selected_color]"]').val(selectedColor);
    
    if (selectedColorId && colorVariants) {
        const variant = colorVariants.find(v => v.id == selectedColorId);
        if (variant) {
            // Remove existing stock info
            card.find('.mobile-stock-info').remove();
            
            // Add stock information
            const stockInfoHtml = `
                <div class="mobile-stock-info mt-2 p-3" style="background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #28a745;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Mevcut Stok</small>
                            <strong class="text-success">${variant.stock_quantity} Adet</strong>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Kritik Stok</small>
                            <small class="text-warning">${variant.critical_stock} Adet</small>
                        </div>
                    </div>
                </div>
            `;
            
            card.find('.color-selection-mobile').after(stockInfoHtml);
            
            // Validate stock
            validateMobileStock(card, variant, quantityInput, seriesSizeSelect);
        }
    } else {
        // Remove stock info if no color selected
        card.find('.mobile-stock-info').remove();
    }
});

// Mobile stock validation function (series size removed)
function validateMobileStock(card, variant, quantityInput) {
    const quantity = parseFloat(quantityInput.val()) || 0;
    const actualQuantity = quantity;
    
    // Remove existing warnings
    card.find('.mobile-stock-warning').remove();
    card.removeClass('border-danger');
    
    // Add 10% tolerance for mobile (more lenient than desktop)
    const toleranceFactor = 1.1;
    const maxAllowedQuantity = Math.floor(variant.stock_quantity * toleranceFactor);
    
    if (actualQuantity > maxAllowedQuantity) {
        const warningHtml = `
            <div class="mobile-stock-warning mt-2 p-3" style="background-color: #f8d7da; border-radius: 8px; border-left: 4px solid #dc3545;">
                <div class="d-flex align-items-center">
                    <iconify-icon icon="solar:danger-triangle-outline" class="text-danger me-2" style="font-size: 20px;"></iconify-icon>
                    <div>
                        <strong class="text-danger d-block">Stok Yetersiz!</strong>
                        <small class="text-muted">Mevcut: ${variant.stock_quantity} Adet | İstenen: ${actualQuantity} Adet</small>
                    </div>
                </div>
            </div>
        `;
        
        card.find('.mobile-stock-info').after(warningHtml);
        card.addClass('border-danger');
    } else if (variant.critical_stock && actualQuantity > variant.critical_stock) {
        const warningHtml = `
            <div class="mobile-stock-warning mt-2 p-3" style="background-color: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                <div class="d-flex align-items-center">
                    <iconify-icon icon="solar:warning-outline" class="text-warning me-2" style="font-size: 20px;"></iconify-icon>
                    <div>
                        <strong class="text-warning d-block">Kritik Stok!</strong>
                        <small class="text-muted">Kritik seviye: ${variant.critical_stock ?? 0} Adet</small>
                    </div>
                </div>
            </div>
        `;
        
        card.find('.mobile-stock-info').after(warningHtml);
    }
}

// Mobile quantity change validation
$(document).on('input', '.card .quantity-input', function() {
    const card = $(this).closest('.card');
    const selectedColorId = card.find('.color-variant-select').val();
    const colorVariants = card.data('color-variants');
    
    if (selectedColorId && colorVariants) {
        const variant = colorVariants.find(v => v.id == selectedColorId);
        if (variant) {
            validateMobileStock(card, variant, $(this));
        }
    }
});

// Mobile series size change validation
$(document).on('change', '.card .series-size-select', function() {
    const card = $(this).closest('.card');
    const selectedColorId = card.find('.color-variant-select').val();
    const colorVariants = card.data('color-variants');
    
    if (selectedColorId && colorVariants) {
        const variant = colorVariants.find(v => v.id == selectedColorId);
        if (variant) {
            const quantityInput = card.find('.quantity-input');
            validateMobileStock(card, variant, quantityInput);
        }
    }
});
</script>

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

/* Responsive invoice table improvements */
@media (min-width: 992px) {
    #invoiceItemsTable {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    #invoiceItemsTable th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
    }
    
    #invoiceItemsTable td {
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f4;
    }
    
    #invoiceItemsTable tr:hover {
        background-color: #f8f9fa;
    }
    
    .form-control, .form-select {
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
}

/* Mobile card improvements */
@media (max-width: 991.98px) {
    .card {
        border: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 24px;
        border-radius: 16px;
    }
    
    .card-body {
        padding: 24px;
    }
    
    .form-label {
        font-size: 15px;
        font-weight: 600;
        color: #495057;
        margin-bottom: 10px;
        display: block;
    }
    
    .form-control, .form-select {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        transition: all 0.2s ease;
        font-size: 16px;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .input-group-text {
        background-color: #f8f9fa;
        border-color: #e9ecef;
        font-weight: 500;
        font-size: 14px;
    }
    
    .btn-outline-danger {
        border-radius: 12px;
        padding: 10px 14px;
    }
    
    .mb-3 {
        margin-bottom: 20px !important;
    }
    
    .calculated-series {
        margin-top: 6px;
        display: block;
    }
    
    /* Mobile stock info and warnings */
    .mobile-stock-info {
        font-size: 14px;
        border: 1px solid #e9ecef;
    }
    
    .mobile-stock-warning {
        font-size: 14px;
        animation: fadeIn 0.3s ease-in;
    }
    
    .border-danger {
        border: 2px solid #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
}

/* General improvements */
.calculated-series {
    font-weight: 500;
    color: #6c757d;
}

.product-service-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
}

/* Mobile specific dropdown styles */
@media (max-width: 991px) {
    .product-service-dropdown {
        position: fixed !important;
        z-index: 9999 !important;
        background: white !important;
        border: 2px solid #007bff !important;
        border-radius: 12px !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2) !important;
        max-height: 400px !important;
        overflow-y: auto !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    .product-service-dropdown.hidden {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
    
    /* Customer dropdown mobile improvements - AGGRESSIVE STYLING */
    #customerDropdown,
    #customerDropdown.force-visible,
    #customerDropdown.mobile-dropdown-visible {
        position: fixed !important;
        z-index: 99999 !important;
        max-height: 60vh !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2) !important;
        border: 2px solid #007bff !important;
        border-radius: 12px !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: auto !important;
        background: white !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
        transform: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Override any hidden classes on mobile */
    #customerDropdown.hidden,
    #customerDropdown.d-none {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Customer dropdown items - make touch-friendly on mobile */
    #customerDropdown .customer-option {
        min-height: 48px !important;
        padding: 12px 16px !important;
        cursor: pointer !important;
        -webkit-tap-highlight-color: rgba(0, 123, 255, 0.2) !important;
        touch-action: manipulation !important;
        user-select: none !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
    }
    
    #customerDropdown .customer-option:active {
        background-color: #f0f0f0 !important;
    }
    
    #customerDropdown .customer-option:hover {
        background-color: #f8f9fa !important;
    }
    
    /* Force visibility class for mobile - AGGRESSIVE */
    #customerDropdown.force-visible,
    #customerDropdown.mobile-dropdown-visible {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: auto !important;
        position: fixed !important;
        z-index: 99999 !important;
    }
    
    /* Override Bootstrap's hidden class on mobile */
    #customerDropdown.hidden.force-visible,
    #customerDropdown.d-none.force-visible {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
}

/* Extra small screens - very small mobile devices - AGGRESSIVE */
@media (max-width: 575px) {
    /* Customer dropdown - full width on very small screens */
    #customerDropdown,
    #customerDropdown.force-visible,
    #customerDropdown.mobile-dropdown-visible {
        width: calc(100vw - 20px) !important;
        max-width: calc(100vw - 20px) !important;
        min-width: calc(100vw - 20px) !important;
        left: 10px !important;
        right: 10px !important;
        position: fixed !important;
        z-index: 99999 !important;
        max-height: 70vh !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3) !important;
        border: 2px solid #007bff !important;
        border-radius: 12px !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: auto !important;
        background: white !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
        transform: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Override hidden states on very small screens */
    #customerDropdown.hidden,
    #customerDropdown.d-none {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Customer dropdown items - larger touch targets on very small screens */
    #customerDropdown .customer-option {
        min-height: 52px !important;
        padding: 14px 16px !important;
    }
    
    /* Force visibility class for very small screens - AGGRESSIVE */
    #customerDropdown.force-visible,
    #customerDropdown.mobile-dropdown-visible {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: auto !important;
        width: calc(100vw - 20px) !important;
        left: 10px !important;
        position: fixed !important;
        z-index: 99999 !important;
    }
    
    /* Override any hidden classes */
    #customerDropdown.hidden.force-visible,
    #customerDropdown.d-none.force-visible,
    #customerDropdown.hidden.mobile-dropdown-visible,
    #customerDropdown.d-none.mobile-dropdown-visible {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
}

/* Ensure all inputs are touch-friendly on mobile */
@media (max-width: 991px) {
    .form-control, .form-select {
        min-height: 44px !important;
        font-size: 16px !important;
    }
    
    /* Mobile card improvements */
    .card-body {
        padding: 20px !important;
    }
    
    /* Product/service search input on mobile - keep 30px as requested but ensure touch-friendly */
    .product-service-search {
        min-height: 44px !important;
        height: 44px !important;
        font-size: 16px !important;
    }
    
    /* Action buttons full width on mobile */
    .btn {
        min-height: 44px !important;
        font-size: 16px !important;
    }
    
    /* Textarea mobile improvements */
    textarea.form-control {
        min-height: 80px !important;
    }
}

.product-service-dropdown .dropdown-item {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f3f4;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.product-service-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
}

.product-service-dropdown .dropdown-item:last-child {
    border-bottom: none;
}
</style>
@endpush
@endsection
