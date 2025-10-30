@extends('layout.layout')

@php
    $currentAccount = session('current_account_id') ? \App\Models\Account::find(session('current_account_id')) : null;
    $accountName = $currentAccount ? $currentAccount->name : 'Tüm Hesaplar';
    $accountCode = $currentAccount ? $currentAccount->code : 'ADMIN';
    
    $title='Dashboard';
    $subTitle = $accountName . ' (' . $accountCode . ')';
    $script= '<script src="' . asset('assets/js/homeOneChart.js') . '"></script>';
@endphp

@section('content')

<!-- Hızlı İşlemler -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
                <h5 class="card-title mb-3 mb-md-4 d-flex align-items-center">
                    <iconify-icon icon="heroicons:bolt" class="text-primary me-2"></iconify-icon>
                    <span class="d-none d-sm-inline">Hızlı İşlemler</span>
                    <span class="d-sm-none">Hızlı İşlemler</span>
                </h5>
                <div class="row g-2 g-md-3">
                    <div class="col-6 col-sm-4 col-md-2">
                        <a href="{{ route('sales.invoices.create') }}" class="btn btn-outline-success w-100 d-flex flex-column align-items-center py-2 py-md-3 text-decoration-none">
                            <iconify-icon icon="heroicons:document-plus" class="text-lg text-md-xl mb-1"></iconify-icon>
                            <span class="fw-medium text-xs text-md-sm">Satış Faturası</span>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <a href="{{ route('purchases.invoices.create') }}" class="btn btn-outline-primary w-100 d-flex flex-column align-items-center py-2 py-md-3 text-decoration-none">
                            <iconify-icon icon="heroicons:document-text" class="text-lg text-md-xl mb-1"></iconify-icon>
                            <span class="fw-medium text-xs text-md-sm">Alış Faturası</span>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <a href="{{ route('finance.collections.create') }}" class="btn btn-outline-warning w-100 d-flex flex-column align-items-center py-2 py-md-3 text-decoration-none">
                            <iconify-icon icon="heroicons:banknotes" class="text-lg text-md-xl mb-1"></iconify-icon>
                            <span class="fw-medium text-xs text-md-sm">Tahsilat Yap</span>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <a href="{{ route('products.create') }}" class="btn btn-outline-info w-100 d-flex flex-column align-items-center py-2 py-md-3 text-decoration-none">
                            <iconify-icon icon="heroicons:plus-circle" class="text-lg text-md-xl mb-1"></iconify-icon>
                            <span class="fw-medium text-xs text-md-sm">Yeni Ürün</span>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <a href="{{ route('barcode.index') }}" class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center py-2 py-md-3 text-decoration-none">
                            <iconify-icon icon="heroicons:printer" class="text-lg text-md-xl mb-1"></iconify-icon>
                            <span class="fw-medium text-xs text-md-sm">Barkod Yazdır</span>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <a href="{{ route('expenses.expenses.create') }}" class="btn btn-outline-danger w-100 d-flex flex-column align-items-center py-2 py-md-3 text-decoration-none">
                            <iconify-icon icon="heroicons:currency-dollar" class="text-lg text-md-xl mb-1"></iconify-icon>
                            <span class="fw-medium text-xs text-md-sm">Gider Ekle</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kritik Uyarılar -->
            @if(isset($lowStockProducts) || isset($lowStockSeries) || isset($dueSales) || isset($duePurchases))
<div class="row mb-4">
    <div class="col-12">
                @if((!empty($lowStockProducts) && $lowStockProducts->count()) || (!empty($lowStockColorVariants) && $lowStockColorVariants->count()) || (!empty($lowStockSeries) && $lowStockSeries->count()) || (!empty($lowStockSeriesColorVariants) && $lowStockSeriesColorVariants->count()))
        <div class="alert alert-danger d-flex flex-column flex-md-row align-items-start align-items-md-center mb-3" role="alert">
            <iconify-icon icon="solar:danger-triangle-outline" class="text-xl me-2 mb-2 mb-md-0 flex-shrink-0"></iconify-icon>
                    <div class="flex-grow-1">
                <strong>🚨 Kritik Stok Uyarısı ({{ ($lowStockProducts->count() ?? 0) + ($lowStockColorVariants->count() ?? 0) + ($lowStockSeries->count() ?? 0) + ($lowStockSeriesColorVariants->count() ?? 0) }} ürün):</strong>
                <div class="mt-2 d-flex flex-wrap gap-1">
                        @if(!empty($lowStockProducts))
                        @foreach($lowStockProducts as $p)
                            <a href="{{ route('products.edit', $p->id) }}?focus=stock" class="badge bg-danger text-decoration-none text-white critical-stock-badge" title="Stok güncellemek için tıklayın - {{ $p->name }} ({{ $p->category }})">
                                {{ Str::limit($p->name, 15) }} ({{ $p->initial_stock }}/{{ $p->critical_stock }}) [{{ $p->category }}] ✏️
                            </a>
                            @endforeach
                        @endif
                        
                        @if(!empty($lowStockColorVariants))
                            @foreach($lowStockColorVariants as $cv)
                            <a href="{{ route('products.edit', $cv->product_id) }}?focus=stock" class="badge bg-warning text-decoration-none text-dark critical-stock-badge" title="Renk stok güncellemek için tıklayın - {{ $cv->product->name }} ({{ $cv->color }})">
                                {{ Str::limit($cv->product->name, 12) }} ({{ $cv->color }}) ({{ $cv->stock_quantity }}/{{ $cv->critical_stock }}) [{{ $cv->product->category }}] 🎨
                            </a>
                            @endforeach
                        @endif
                        
                        @if(!empty($lowStockSeries))
                            @foreach($lowStockSeries as $s)
                            <a href="{{ route('products.series.edit', $s->id) }}?focus=stock" class="badge bg-danger text-decoration-none text-white critical-stock-badge" title="Seri stok güncellemek için tıklayın - {{ $s->name }} ({{ $s->category }})">
                                {{ Str::limit($s->name, 15) }} ({{ $s->stock_quantity }}/{{ $s->critical_stock }}) [{{ $s->category }}] 📦
                            </a>
                        @endforeach
                        @endif
                        
                        @if(!empty($lowStockSeriesColorVariants))
                            @foreach($lowStockSeriesColorVariants as $scv)
                            <a href="{{ route('products.series.show', $scv->product_series_id) }}?focus=stock" class="badge bg-warning text-decoration-none text-dark critical-stock-badge" title="Seri renk stok güncellemek için tıklayın - {{ $scv->productSeries->name }} ({{ $scv->color }})">
                                {{ Str::limit($scv->productSeries->name, 12) }} ({{ $scv->color }}) ({{ $scv->stock_quantity }}/{{ $scv->critical_stock }}) [{{ $scv->productSeries->category }}] 🎨📦
                            </a>
                            @endforeach
                        @endif
                </div>
            </div>
                    </div>
                @endif

                @if(!empty($customersToCollect) && $customersToCollect->count())
        <div class="alert alert-warning d-flex flex-column flex-md-row align-items-start align-items-md-center mb-3" role="alert">
            <iconify-icon icon="solar:hand-money-outline" class="text-xl me-2 mb-2 mb-md-0 flex-shrink-0"></iconify-icon>
                    <div class="flex-grow-1">
                <strong>💳 Tahsilat Yapılması Gereken Müşteriler ({{ $customersToCollect->count() }} kişi):</strong>
                <div class="mt-2 d-flex flex-wrap gap-1">
                        @foreach($customersToCollect as $cust)
                        <a href="{{ route('sales.customers.show', $cust->id) }}" class="badge bg-warning text-dark text-decoration-none" title="Müşteri detayına git">
                            {{ Str::limit($cust->name ?? $cust->company_name, 18) }}
                            @if(($cust->nearest_due_date ?? null))
                                — {{ \Carbon\Carbon::parse($cust->nearest_due_date)->format('d.m.Y') }}
                            @endif
                        </a>
                        @endforeach
                </div>
            </div>
                    </div>
                @endif
        
                @if(!empty($dueAndOverdueSales) && $dueAndOverdueSales->count())
        <div class="alert alert-warning d-flex flex-column flex-md-row align-items-start align-items-md-center mb-3" role="alert">
            <iconify-icon icon="solar:clock-circle-outline" class="text-xl me-2 mb-2 mb-md-0 flex-shrink-0"></iconify-icon>
                    <div class="flex-grow-1">
                <strong>⏰ Vadesi Yaklaşan/Geçmiş Satış Faturaları ({{ $dueAndOverdueSales->count() }} fatura):</strong>
                <div class="mt-2 d-flex flex-wrap gap-1">
                        @foreach($dueAndOverdueSales as $inv)
                        <a href="{{ route('sales.customers.show', $inv->customer_id) }}" class="badge bg-warning text-dark text-decoration-none">{{ $inv->invoice_number }} - {{ $inv->due_date->format('d.m.Y') }} - {{ number_format($inv->total_amount,2) }} {{ $inv->currency }}</a>
                        @endforeach
                </div>
            </div>
                    </div>
                @endif
        
                @if(!empty($duePurchases) && $duePurchases->count())
        <div class="alert alert-info d-flex flex-column flex-md-row align-items-start align-items-md-center mb-3" role="alert">
            <iconify-icon icon="solar:calendar-outline" class="text-xl me-2 mb-2 mb-md-0 flex-shrink-0"></iconify-icon>
                    <div class="flex-grow-1">
                <strong>📅 Vadesi Yaklaşan Alış Faturaları ({{ $duePurchases->count() }} fatura):</strong>
                <div class="mt-2 d-flex flex-wrap gap-1">
                        @foreach($duePurchases as $pinv)
                        <span class="badge bg-info text-white">{{ $pinv->invoice_number }} - {{ $pinv->due_date->format('d.m.Y') }} - {{ number_format($pinv->total_amount,2) }} {{ $pinv->currency }}</span>
                        @endforeach
                    </div>
            </div>
                    </div>
                @endif
    </div>
            </div>
            @endif

<!-- KPI Cards -->
            <div class="row row-cols-xxxl-5 row-cols-xl-4 row-cols-lg-3 row-cols-md-2 row-cols-1 gy-3 gy-md-4">
    
    <!-- Bu Ay Satışlar TRY -->
                <div class="col">
                    <div class="card shadow-none border bg-gradient-start-1 h-100">
                        <div class="card-body p-3 p-md-20">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 gap-md-3">
                                <div>
                        <p class="fw-medium text-primary-light mb-1 text-sm text-md-base">Bu Ay Satışlar (TRY)</p>
                        <h6 class="mb-0 text-lg text-md-xl">₺{{ number_format($stats['thisMonthSalesTRY'] ?? 0, 2) }}</h6>
                                </div>
                                <div class="w-40-px h-40-px w-md-50-px h-md-50-px bg-cyan rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:chart-square-outline" class="text-white text-xl text-md-2xl mb-0"></iconify-icon>
                                </div>
                            </div>
                            <p class="fw-medium text-xs text-md-sm text-primary-light mt-2 mt-md-12 mb-0 d-flex align-items-center gap-1 gap-md-2">
                    <span class="d-inline-flex align-items-center gap-1 {{ ($stats['salesGrowthTRY'] ?? 0) >= 0 ? 'text-success-main' : 'text-danger-main' }}">
                        <iconify-icon icon="{{ ($stats['salesGrowthTRY'] ?? 0) >= 0 ? 'bxs:up-arrow' : 'bxs:down-arrow' }}" class="text-xs"></iconify-icon> 
                        %{{ number_format(abs($stats['salesGrowthTRY'] ?? 0), 1) }}
                                </span>
                    <span class="d-none d-md-inline">Geçen aya göre</span>
                    <span class="d-md-none">vs önceki</span>
                            </p>
                        </div>
        </div>
                </div>
    
    <!-- Bu Ay Satışlar USD -->
                <div class="col">
                    <div class="card shadow-none border bg-gradient-start-3 h-100">
                        <div class="card-body p-20">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                        <p class="fw-medium text-primary-light mb-1">Bu Ay Satışlar (USD)</p>
                        <h6 class="mb-0">${{ number_format($stats['thisMonthSalesUSD'] ?? 0, 2) }}</h6>
                                </div>
                                <div class="w-50-px h-50-px bg-success rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:dollar-outline" class="text-white text-2xl mb-0"></iconify-icon>
                                </div>
                            </div>
                            <p class="fw-medium text-sm text-primary-light mt-12 mb-0 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center gap-1 {{ ($stats['salesGrowthUSD'] ?? 0) >= 0 ? 'text-success-main' : 'text-danger-main' }}">
                        <iconify-icon icon="{{ ($stats['salesGrowthUSD'] ?? 0) >= 0 ? 'bxs:up-arrow' : 'bxs:down-arrow' }}" class="text-xs"></iconify-icon> 
                        %{{ number_format(abs($stats['salesGrowthUSD'] ?? 0), 1) }}
                                </span>
                    Geçen aya göre
                            </p>
                        </div>
        </div>
                </div>
    
    <!-- Bu Ay Satışlar EUR -->
                <div class="col">
                    <div class="card shadow-none border bg-gradient-start-4 h-100">
                        <div class="card-body p-20">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                        <p class="fw-medium text-primary-light mb-1">Bu Ay Satışlar (EUR)</p>
                        <h6 class="mb-0">€{{ number_format($stats['thisMonthSalesEUR'] ?? 0, 2) }}</h6>
                                </div>
                                <div class="w-50-px h-50-px bg-warning rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:euro-outline" class="text-white text-2xl mb-0"></iconify-icon>
                                </div>
                            </div>
                            <p class="fw-medium text-sm text-primary-light mt-12 mb-0 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center gap-1 {{ ($stats['salesGrowthEUR'] ?? 0) >= 0 ? 'text-success-main' : 'text-danger-main' }}">
                        <iconify-icon icon="{{ ($stats['salesGrowthEUR'] ?? 0) >= 0 ? 'bxs:up-arrow' : 'bxs:down-arrow' }}" class="text-xs"></iconify-icon> 
                        %{{ number_format(abs($stats['salesGrowthEUR'] ?? 0), 1) }}
                                </span>
                    Geçen aya göre
                            </p>
                        </div>
        </div>
                </div>

    <!-- Toplam Müşteriler -->
                <div class="col">
                    <div class="card shadow-none border bg-gradient-start-2 h-100">
                        <div class="card-body p-20">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                        <p class="fw-medium text-primary-light mb-1">Toplam Müşteriler</p>
                        <h6 class="mb-0">{{ number_format($stats['totalCustomers'] ?? 0) }}</h6>
                                </div>
                                <div class="w-50-px h-50-px bg-purple rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="gridicons:multiple-users" class="text-white text-2xl mb-0"></iconify-icon>
                                </div>
                            </div>
                            <p class="fw-medium text-sm text-primary-light mt-12 mb-0 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center gap-1 text-success-main">
                        <iconify-icon icon="bxs:up-arrow" class="text-xs"></iconify-icon> +{{ $stats['newCustomers'] ?? 0 }}
                                </span>
                    Bu ay yeni müşteri
                            </p>
                        </div>
        </div>
                </div>

    <!-- Toplam Ürünler -->
                <div class="col">
                    <div class="card shadow-none border bg-gradient-start-3 h-100">
                        <div class="card-body p-20">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                        <p class="fw-medium text-primary-light mb-1">Toplam Ürünler</p>
                        <h6 class="mb-0">{{ number_format($stats['totalProducts'] ?? 0) }}</h6>
                                </div>
                                <div class="w-50-px h-50-px bg-info rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:box-outline" class="text-white text-2xl mb-0"></iconify-icon>
                                </div>
                            </div>
                            <p class="fw-medium text-sm text-primary-light mt-12 mb-0 d-flex align-items-center gap-2">
                    @if(($stats['criticalStockCount'] ?? 0) > 0)
                    <span class="d-inline-flex align-items-center gap-1 text-danger-main">
                        <iconify-icon icon="solar:danger-triangle-outline" class="text-xs"></iconify-icon> {{ $stats['criticalStockCount'] }}
                    </span>
                    Kritik stok uyarısı
                    @else
                                <span class="d-inline-flex align-items-center gap-1 text-success-main">
                        <iconify-icon icon="solar:check-circle-outline" class="text-xs"></iconify-icon> Stok OK
                                </span>
                    Kritik stok yok
                    @endif
                            </p>
                        </div>
        </div>
                </div>

    <!-- Bu Ay Alışlar TRY -->
                <div class="col">
                    <div class="card shadow-none border bg-gradient-start-4 h-100">
                        <div class="card-body p-20">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                        <p class="fw-medium text-primary-light mb-1">Bu Ay Alışlar (TRY)</p>
                        <h6 class="mb-0">₺{{ number_format($stats['thisMonthPurchasesTRY'] ?? 0, 2) }}</h6>
                                </div>
                                <div class="w-50-px h-50-px bg-info rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:shopping-cart-outline" class="text-white text-2xl mb-0"></iconify-icon>
                                </div>
                            </div>
                            <p class="fw-medium text-sm text-primary-light mt-12 mb-0 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center gap-1 text-primary-light">
                        <iconify-icon icon="solar:calculator-outline" class="text-xs"></iconify-icon>
                                </span>
                    Maliyet giderleri
                            </p>
                        </div>
        </div>
                </div>
    
    <!-- Bu Ay Alışlar USD -->
                <div class="col">
                    <div class="card shadow-none border bg-gradient-start-5 h-100">
                        <div class="card-body p-20">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                        <p class="fw-medium text-primary-light mb-1">Bu Ay Alışlar (USD)</p>
                        <h6 class="mb-0">${{ number_format($stats['thisMonthPurchasesUSD'] ?? 0, 2) }}</h6>
                                </div>
                                <div class="w-50-px h-50-px bg-danger rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:cart-plus-outline" class="text-white text-2xl mb-0"></iconify-icon>
                                </div>
                            </div>
                            <p class="fw-medium text-sm text-primary-light mt-12 mb-0 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center gap-1 text-primary-light">
                        <iconify-icon icon="solar:calculator-outline" class="text-xs"></iconify-icon>
                                </span>
                    Maliyet giderleri
                            </p>
                        </div>
        </div>
                </div>
    
    <!-- Bu Ay Alışlar EUR -->
                <div class="col">
                    <div class="card shadow-none border bg-gradient-start-6 h-100">
                        <div class="card-body p-20">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                        <p class="fw-medium text-primary-light mb-1">Bu Ay Alışlar (EUR)</p>
                        <h6 class="mb-0">€{{ number_format($stats['thisMonthPurchasesEUR'] ?? 0, 2) }}</h6>
                                </div>
                                <div class="w-50-px h-50-px bg-secondary rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:bag-smile-outline" class="text-white text-2xl mb-0"></iconify-icon>
                                </div>
                            </div>
                            <p class="fw-medium text-sm text-primary-light mt-12 mb-0 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center gap-1 text-primary-light">
                        <iconify-icon icon="solar:calculator-outline" class="text-xs"></iconify-icon>
                                </span>
                    Maliyet giderleri
                            </p>
                        </div>
        </div>
                </div>

    <!-- Ödenmemiş Faturalar -->
                <div class="col">
                    <div class="card shadow-none border bg-gradient-start-7 h-100">
                        <div class="card-body p-20">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                        <p class="fw-medium text-primary-light mb-1">Ödenmemiş Faturalar</p>
                        <h6 class="mb-0">₺{{ number_format($stats['unpaidInvoices'] ?? 0, 2) }}</h6>
                                </div>
                                <div class="w-50-px h-50-px bg-red rounded-circle d-flex justify-content-center align-items-center">
                                    <iconify-icon icon="fa6-solid:file-invoice-dollar" class="text-white text-2xl mb-0"></iconify-icon>
                                </div>
                            </div>
                            <p class="fw-medium text-sm text-primary-light mt-12 mb-0 d-flex align-items-center gap-2">
                    @if(($stats['overdueInvoices'] ?? 0) > 0)
                    <span class="d-inline-flex align-items-center gap-1 text-danger-main">
                        <iconify-icon icon="solar:clock-circle-outline" class="text-xs"></iconify-icon> {{ $stats['overdueInvoices'] }}
                    </span>
                    Vadesi geçmiş
                    @else
                                <span class="d-inline-flex align-items-center gap-1 text-success-main">
                        <iconify-icon icon="solar:check-circle-outline" class="text-xs"></iconify-icon>
                                </span>
                    Vadesi geçmiş yok
                    @endif
                            </p>
                        </div>
        </div>
                </div>
            </div>

            <div class="row gy-4 mt-1">
    <!-- Satış İstatistikleri -->
                <div class="col-xxl-6 col-xl-12">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <h6 class="text-lg mb-0">Satış İstatistikleri</h6>
                                <select class="form-select bg-base form-select-sm w-auto">
                        <option>Bu Ay</option>
                        <option>Geçen Ay</option>
                        <option>Bu Yıl</option>
                                </select>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-8">
                    <h6 class="mb-0">₺{{ number_format($stats['thisMonthSales'] ?? 0, 0) }}</h6>
                    <span class="text-sm fw-semibold rounded-pill {{ ($stats['salesGrowth'] ?? 0) >= 0 ? 'bg-success-focus text-success-main border br-success' : 'bg-danger-focus text-danger-main border br-danger' }} px-8 py-4 line-height-1 d-flex align-items-center gap-1">
                        %{{ number_format(abs($stats['salesGrowth'] ?? 0), 1) }} 
                        <iconify-icon icon="{{ ($stats['salesGrowth'] ?? 0) >= 0 ? 'bxs:up-arrow' : 'bxs:down-arrow' }}" class="text-xs"></iconify-icon>
                                </span>
                    <span class="text-xs fw-medium">Aylık ortalama</span>
                            </div>
                            <div id="chart" class="pt-28 apexcharts-tooltip-style-1"></div>
                        </div>
                    </div>
                </div>

    <!-- En Çok Satan Ürünler -->
                <div class="col-xxl-3 col-xl-6">
                    <div class="card h-100 radius-8 border">
                        <div class="card-body p-24">
                <h6 class="mb-12 fw-semibold text-lg mb-16">En Çok Satan Ürünler</h6>
                            <div class="d-flex align-items-center gap-2 mb-20">
                    <h6 class="fw-semibold mb-0">{{ $topProducts->sum('invoice_items_count') ?? 0 }}</h6>
                                <p class="text-sm mb-0">
                        <span class="bg-success-focus border br-success px-8 py-2 rounded-pill fw-semibold text-success-main text-sm d-inline-flex align-items-center gap-1">
                            Bu Ay
                            <iconify-icon icon="iconamoon:arrow-up-2-fill" class="icon"></iconify-icon>
                                    </span>
                        Toplam Satış
                    </p>
                </div>

                <div class="space-y-3">
                    @forelse($topProducts as $product)
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-12">
                        <div class="flex-grow-1">
                            <h6 class="text-sm mb-0 fw-medium">{{ Str::limit($product->name, 25) }}</h6>
                            <span class="text-xs text-secondary-light fw-medium">{{ $product->invoice_items_count }} satış</span>
                        </div>
                        <div class="w-50 ms-auto">
                            @php $percentage = $topProducts->max('invoice_items_count') > 0 ? ($product->invoice_items_count / $topProducts->max('invoice_items_count')) * 100 : 0; @endphp
                            <div class="progress progress-sm rounded-pill" role="progressbar">
                                <div class="progress-bar bg-primary-600 rounded-pill" style="width: {{ $percentage }}%;"></div>
                            </div>
                        </div>
                        <span class="text-secondary-light text-xs fw-semibold">{{ number_format($percentage, 0) }}%</span>
                    </div>
                    @empty
                    <div class="text-center text-secondary-light">
                        <iconify-icon icon="solar:box-outline" class="text-4xl mb-2"></iconify-icon>
                        <p class="text-sm">Bu ay henüz satış yok</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Kritik Stok Uyarıları -->
                <div class="col-xxl-3 col-xl-6">
                    <div class="card h-100 radius-8 border-0 overflow-hidden">
                        <div class="card-body p-24">
                            <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between">
                    <h6 class="mb-2 fw-bold text-lg">Kritik Stok</h6>
                                <div class="">
                        <span class="badge {{ ($stats['criticalStockCount'] ?? 0) > 0 ? 'bg-danger' : 'bg-success' }} text-sm">
                            {{ $stats['criticalStockCount'] ?? 0 }} Uyarı
                        </span>
                                </div>
                            </div>

                <div class="mt-3">
                    @forelse($lowStockProducts as $product)
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-12 p-2 bg-danger-focus rounded">
                        <div class="flex-grow-1">
                            <h6 class="text-sm mb-0 fw-medium text-danger-main">{{ Str::limit($product->name, 18) }}</h6>
                            <span class="text-xs text-danger-600 fw-medium">Stok: {{ $product->initial_stock }} / Kritik: {{ $product->critical_stock }} [{{ $product->category }}]</span>
                        </div>
                        <iconify-icon icon="solar:danger-triangle-outline" class="text-danger-main text-lg"></iconify-icon>
                    </div>
                    @empty
                    @endforelse
                    
                    @forelse($lowStockColorVariants as $cv)
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-12 p-2 bg-warning-focus rounded">
                        <div class="flex-grow-1">
                            <h6 class="text-sm mb-0 fw-medium text-warning-main">{{ Str::limit($cv->product->name, 15) }} ({{ $cv->color }})</h6>
                            <span class="text-xs text-warning-600 fw-medium">Renk Stok: {{ $cv->stock_quantity }} / Kritik: {{ $cv->critical_stock }} [{{ $cv->product->category }}]</span>
                        </div>
                        <iconify-icon icon="solar:danger-triangle-outline" class="text-warning-main text-lg"></iconify-icon>
                    </div>
                    @empty
                    @endforelse
                    
                    @forelse($lowStockSeries as $series)
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-12 p-2 bg-danger-focus rounded">
                        <div class="flex-grow-1">
                            <h6 class="text-sm mb-0 fw-medium text-danger-main">{{ Str::limit($series->name, 18) }} ({{ $series->series_size }}li)</h6>
                            <span class="text-xs text-danger-600 fw-medium">Seri Stok: {{ $series->stock_quantity }} / Kritik: {{ $series->critical_stock }} [{{ $series->category }}]</span>
                        </div>
                        <iconify-icon icon="solar:danger-triangle-outline" class="text-danger-main text-lg"></iconify-icon>
                    </div>
                    @empty
                    @endforelse
                    
                    @forelse($lowStockSeriesColorVariants as $scv)
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-12 p-2 bg-warning-focus rounded">
                        <div class="flex-grow-1">
                            <h6 class="text-sm mb-0 fw-medium text-warning-main">{{ Str::limit($scv->productSeries->name, 15) }} ({{ $scv->color }})</h6>
                            <span class="text-xs text-warning-600 fw-medium">Seri Renk Stok: {{ $scv->stock_quantity }} / Kritik: {{ $scv->critical_stock }} [{{ $scv->productSeries->category }}]</span>
                        </div>
                        <iconify-icon icon="solar:danger-triangle-outline" class="text-warning-main text-lg"></iconify-icon>
                    </div>
                    @empty
                    @endforelse
                    
                    @if(($lowStockProducts->count() ?? 0) == 0 && ($lowStockColorVariants->count() ?? 0) == 0 && ($lowStockSeries->count() ?? 0) == 0 && ($lowStockSeriesColorVariants->count() ?? 0) == 0)
                    <div class="text-center text-success-main">
                        <iconify-icon icon="solar:check-circle-outline" class="text-4xl mb-2"></iconify-icon>
                        <p class="text-sm">Tüm stoklar yeterli seviyede</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Son Faturalar -->
                <div class="col-xxl-9 col-xl-12">
                    <div class="card h-100">
                        <div class="card-body p-24">
                            <div class="d-flex flex-wrap align-items-center gap-1 justify-content-between mb-16">
                    <h6 class="mb-2 fw-bold text-lg mb-0">Son Faturalar</h6>
                    <a href="{{ route('sales.invoices.index') }}" class="text-primary-600 hover-text-primary d-flex align-items-center gap-1">
                        Tümünü Gör
                                    <iconify-icon icon="solar:alt-arrow-right-linear" class="icon"></iconify-icon>
                                </a>
                            </div>

                                    <div class="table-responsive scroll-sm">
                                        <table class="table bordered-table sm-table mb-0">
                                            <thead>
                                                <tr>
                                <th scope="col">Fatura No</th>
                                <th scope="col">Müşteri</th>
                                <th scope="col">Tarih</th>
                                <th scope="col">Tutar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                            @forelse($recentInvoices as $invoice)
                                                <tr>
                                                    <td>
                                    <h6 class="text-md mb-0 fw-medium"><a href="{{ route('sales.customers.show', $invoice->customer_id) }}" class="text-decoration-none">{{ $invoice->invoice_number }}</a></h6>
                                                    </td>
                                <td>
                                    <span class="text-sm text-secondary-light fw-medium">
                                        {{ $invoice->customer->name ?? 'Müşteri Bulunamadı' }}
                                    </span>
                                                    </td>
                                <td>{{ $invoice->created_at->format('d.m.Y') }}</td>
                                <td>₺{{ number_format($invoice->total_amount, 2) }}</td>
                                                </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary-light">
                                    <iconify-icon icon="solar:file-text-outline" class="text-3xl mb-2"></iconify-icon>
                                    <p>Henüz fatura oluşturulmamış</p>
                                                    </td>
                                                </tr>
                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

    <!-- Yaklaşan Tahsilatlar -->
                <div class="col-xxl-3 col-xl-12">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between">
                    <h6 class="mb-2 fw-bold text-lg mb-0">Yaklaşan/Geçmiş Tahsilatlar</h6>
                    <span class="badge bg-warning text-sm">Geçmiş + 7 Gün</span>
                            </div>

                            <div class="mt-32">
                    @forelse($dueAndOverdueSales as $invoice)
                                <div class="d-flex align-items-center justify-content-between gap-3 mb-24">
                                        <div class="flex-grow-1">
                            <h6 class="text-md mb-0 fw-medium"><a href="{{ route('sales.customers.show', $invoice->customer_id) }}" class="text-decoration-none">{{ $invoice->invoice_number }}</a></h6>
                            <span class="text-sm text-secondary-light fw-medium">{{ $invoice->customer->name ?? 'N/A' }}</span>
                            <div class="text-xs text-warning-main">{{ $invoice->due_date->format('d.m.Y') }}</div>
                                        </div>
                        <div class="text-end">
                            <span class="text-primary-light text-md fw-medium">₺{{ number_format($invoice->total_amount, 0) }}</span>
                            <div class="text-xs text-secondary-light">{{ $invoice->currency }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-secondary-light">
                        <iconify-icon icon="solar:calendar-outline" class="text-4xl mb-2"></iconify-icon>
                        <p class="text-sm">7 gün içinde vadesi gelen fatura yok</p>
                    </div>
                    @endforelse
                </div>
                        </div>
                    </div>
                </div>
            </div>

@endsection