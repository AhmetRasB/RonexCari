@extends('layout.layout')

@section('title', 'Rapor Al')
@section('subTitle', 'Kapsamlı İş Analitikleri')

@section('content')
<div class="row g-4">

    <!-- Branch Statistics (Admin Only) -->
    @if($branchStatistics && count($branchStatistics) > 0)
        @foreach($branchStatistics as $branchData)
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-building-line me-2"></i>{{ $branchData['branch']->company_name ?? $branchData['branch']->name }}
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary">{{ $branchData['branch']->name }}</span>
                            <small class="text-muted">Bu Ay İstatistikleri</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Sales Summary -->
                            <div class="col-md-3">
                                <div class="p-3 bg-success-50 rounded-3 border-start border-success border-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="text-secondary-light text-sm mb-1">Satışlar</div>
                                            <div class="h5 mb-1 fw-semibold text-success">₺{{ number_format($branchData['sales']['total_try'], 2) }}</div>
                                            <div class="text-xs text-muted">{{ $branchData['sales']['count'] }} fatura</div>
                                            <div class="text-xs text-muted mt-1">
                                                @if($branchData['sales']['by_currency']['USD'] > 0) ${{ number_format($branchData['sales']['by_currency']['USD'], 2) }} @endif
                                                @if($branchData['sales']['by_currency']['EUR'] > 0) €{{ number_format($branchData['sales']['by_currency']['EUR'], 2) }} @endif
                                            </div>
                                        </div>
                                        <div class="text-success">
                                            <i class="ri-line-chart-line fs-20"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cost of Goods Sold (Admin Only) -->
                            @if(auth()->user()->isAdmin())
                            <div class="col-md-3">
                                <div class="p-3 bg-info-50 rounded-3 border-start border-info border-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="text-secondary-light text-sm mb-1">Maliyet (COGS)</div>
                                            <div class="h5 mb-1 fw-semibold text-info">₺{{ number_format($branchData['purchases']['total_try'], 2) }}</div>
                                            <div class="text-xs text-muted">Satılan malların maliyeti</div>
                                        </div>
                                        <div class="text-info">
                                            <i class="ri-calculator-line fs-20"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Expenses -->
                            <div class="col-md-3">
                                <div class="p-3 bg-danger-50 rounded-3 border-start border-danger border-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="text-secondary-light text-sm mb-1">Giderler</div>
                                            <div class="h5 mb-1 fw-semibold text-danger">₺{{ number_format($branchData['expenses'], 2) }}</div>
                                            <div class="text-xs text-muted">Operasyonel</div>
                                        </div>
                                        <div class="text-danger">
                                            <i class="ri-file-list-3-line fs-20"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Profit/Loss -->
                            <div class="col-md-3">
                                <div class="p-3 {{ $branchData['profit'] >= 0 ? 'bg-success-50 border-success' : 'bg-danger-50 border-danger' }} rounded-3 border-start border-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="text-secondary-light text-sm mb-1">{{ $branchData['profit'] >= 0 ? 'Kar' : 'Zarar' }}</div>
                                            <div class="h5 mb-1 fw-semibold {{ $branchData['profit'] >= 0 ? 'text-success' : 'text-danger' }}">₺{{ number_format(abs($branchData['profit']), 2) }}</div>
                                            <div class="text-xs text-muted">Net Sonuç</div>
                                        </div>
                                        <div class="{{ $branchData['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            <i class="ri-{{ $branchData['profit'] >= 0 ? 'arrow-up' : 'arrow-down' }}-line fs-20"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Branch Info -->
                        <div class="row g-3 mt-3">
                            <div class="col-md-4">
                                <div class="p-3 bg-primary-50 rounded-3 text-center">
                                    <div class="text-secondary-light text-sm">Tahsilatlar</div>
                                    <div class="h6 mb-0 fw-semibold text-primary">₺{{ number_format($branchData['collections'], 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-warning-50 rounded-3 text-center">
                                    <div class="text-secondary-light text-sm">Ödenmemiş Faturalar</div>
                                    <div class="h6 mb-0 fw-semibold text-warning">₺{{ number_format($branchData['unpaid_invoices'], 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-secondary-50 rounded-3 text-center">
                                    <div class="text-secondary-light text-sm">Toplam Müşteri</div>
                                    <div class="h6 mb-0 fw-semibold text-secondary">{{ $branchData['top_customers']->count() }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Customers & Products for this Branch -->
                        <div class="row g-4 mt-3">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-header bg-transparent border-0 pb-0">
                                        <h6 class="card-title mb-0">En İyi Müşteriler</h6>
                                    </div>
                                    <div class="card-body pt-2">
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <tbody>
                                                    @forelse($branchData['top_customers'] as $customer)
                                                        <tr>
                                                            <td class="fw-medium">{{ $customer->customer->name ?? $customer->customer->company_name ?? 'Bilinmeyen' }}</td>
                                                            <td class="text-end fw-semibold">{{ number_format($customer->total_amount, 2) }} ₺</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="2" class="text-center text-muted py-2">Müşteri verisi yok</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-header bg-transparent border-0 pb-0">
                                        <h6 class="card-title mb-0">En Çok Satan Ürünler</h6>
                                    </div>
                                    <div class="card-body pt-2">
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <tbody>
                                                    @forelse($branchData['top_products'] as $product)
                                                        <tr>
                                                            <td class="fw-medium">{{ Str::limit($product->product_service_name, 25) }}</td>
                                                            <td class="text-end">
                                                                <span class="badge bg-info-subtle text-info">{{ number_format($product->total_quantity, 0) }}</span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="2" class="text-center text-muted py-2">Ürün verisi yok</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
    
    <!-- Sales Overview Cards -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ri-line-chart-line me-2"></i>Satış Özeti</h5>
                <small class="text-muted">Farklı periyotlarda satış performansı</small>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="p-3 bg-primary-50 rounded-3 border-start border-primary border-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-secondary-light text-sm mb-1">Bugün</div>
                                    <div class="h5 mb-1 fw-semibold text-primary">{{ number_format($salesToday, 2) }} TRY</div>
                                    <div class="text-xs text-muted">{{ $salesCountToday }} satış</div>
                                    <div class="text-xs text-muted mt-1">
                                        @if($salesTodayByCurrency['USD'] > 0) ${{ number_format($salesTodayByCurrency['USD'], 2) }} @endif
                                        @if($salesTodayByCurrency['EUR'] > 0) €{{ number_format($salesTodayByCurrency['EUR'], 2) }} @endif
                                    </div>
                                </div>
                                <div class="text-primary">
                                    <i class="ri-calendar-line fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-success-50 rounded-3 border-start border-success border-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-secondary-light text-sm mb-1">Bu Hafta</div>
                                    <div class="h5 mb-1 fw-semibold text-success">{{ number_format($salesThisWeek, 2) }} TRY</div>
                                    <div class="text-xs text-muted">{{ $salesCountWeek }} satış</div>
                                    <div class="text-xs text-muted mt-1">
                                        @if($salesThisWeekByCurrency['USD'] > 0) ${{ number_format($salesThisWeekByCurrency['USD'], 2) }} @endif
                                        @if($salesThisWeekByCurrency['EUR'] > 0) €{{ number_format($salesThisWeekByCurrency['EUR'], 2) }} @endif
                                    </div>
                                </div>
                                <div class="text-success">
                                    <i class="ri-calendar-week-line fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-info-50 rounded-3 border-start border-info border-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-secondary-light text-sm mb-1">Bu Ay</div>
                                    <div class="h5 mb-1 fw-semibold text-info">{{ number_format($salesThisMonth, 2) }} TRY</div>
                                    <div class="text-xs text-muted">{{ $salesCountMonth }} satış</div>
                                    <div class="text-xs text-muted mt-1">
                                        @if($salesThisMonthByCurrency['USD'] > 0) ${{ number_format($salesThisMonthByCurrency['USD'], 2) }} @endif
                                        @if($salesThisMonthByCurrency['EUR'] > 0) €{{ number_format($salesThisMonthByCurrency['EUR'], 2) }} @endif
                                    </div>
                                </div>
                                <div class="text-info">
                                    <i class="ri-calendar-month-line fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-warning-50 rounded-3 border-start border-warning border-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-secondary-light text-sm mb-1">Son 6 Ay</div>
                                    <div class="h5 mb-1 fw-semibold text-warning">{{ number_format($salesLast6Months, 2) }} TRY</div>
                                    <div class="text-xs text-muted">{{ $salesCount6Months }} satış</div>
                                    <div class="text-xs text-muted mt-1">
                                        @if($salesLast6MonthsByCurrency['USD'] > 0) ${{ number_format($salesLast6MonthsByCurrency['USD'], 2) }} @endif
                                        @if($salesLast6MonthsByCurrency['EUR'] > 0) €{{ number_format($salesLast6MonthsByCurrency['EUR'], 2) }} @endif
                                    </div>
                                </div>
                                <div class="text-warning">
                                    <i class="ri-calendar-2-line fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit/Loss Analysis -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ri-money-dollar-circle-line me-2"></i>Kar/Zarar Analizi</h5>
                <small class="text-muted">Gelir - Gider = Kar/Zarar</small>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="card-title mb-0">Bu Ay (TRY)</h6>
                            </div>
                            <div class="card-body pt-2">
                                <div class="row g-3 mb-3">
                                    <div class="col-4">
                                        <div class="text-center">
                                            <div class="text-success fw-semibold">{{ number_format($monthlyRevenue, 0) }}</div>
                                            <div class="text-xs text-muted">Gelir</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center">
                                            <div class="text-danger fw-semibold">{{ number_format($monthlyPurchases + $monthlyTotalExpenses, 0) }}</div>
                                            <div class="text-xs text-muted">Gider</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center">
                                            <div class="fw-bold {{ $monthlyProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($monthlyProfit, 0) }}</div>
                                            <div class="text-xs text-muted">{{ $monthlyProfit >= 0 ? 'Kar' : 'Zarar' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    @if($monthlyRevenue > 0)
                                        <div class="progress-bar bg-success" style="width: {{ min(($monthlyRevenue / ($monthlyRevenue + $monthlyPurchases + $monthlyTotalExpenses)) * 100, 100) }}%"></div>
                                        <div class="progress-bar bg-danger" style="width: {{ min((($monthlyPurchases + $monthlyTotalExpenses) / ($monthlyRevenue + $monthlyPurchases + $monthlyTotalExpenses)) * 100, 100) }}%"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="card-title mb-0">Son 6 Ay (TRY)</h6>
                            </div>
                            <div class="card-body pt-2">
                                <div class="row g-3 mb-3">
                                    <div class="col-4">
                                        <div class="text-center">
                                            <div class="text-success fw-semibold">{{ number_format($sixMonthRevenue, 0) }}</div>
                                            <div class="text-xs text-muted">Gelir</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center">
                                            <div class="text-danger fw-semibold">{{ number_format($sixMonthPurchases + $sixMonthTotalExpenses, 0) }}</div>
                                            <div class="text-xs text-muted">Gider</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center">
                                            <div class="fw-bold {{ $sixMonthProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($sixMonthProfit, 0) }}</div>
                                            <div class="text-xs text-muted">{{ $sixMonthProfit >= 0 ? 'Kar' : 'Zarar' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    @if($sixMonthRevenue > 0)
                                        <div class="progress-bar bg-success" style="width: {{ min(($sixMonthRevenue / ($sixMonthRevenue + $sixMonthPurchases + $sixMonthTotalExpenses)) * 100, 100) }}%"></div>
                                        <div class="progress-bar bg-danger" style="width: {{ min((($sixMonthPurchases + $sixMonthTotalExpenses) / ($sixMonthRevenue + $sixMonthPurchases + $sixMonthTotalExpenses)) * 100, 100) }}%"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Exchange Rates -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ri-exchange-line me-2"></i>Güncel Döviz Kurları</h5>
                <small class="text-muted">Anlık kurlar (5 dakikada bir güncellenir)</small>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between p-3 bg-success-50 rounded-3">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-success me-3">USD</div>
                                <div>
                                    <div class="fw-semibold">1 USD</div>
                                    <div class="text-xs text-muted">Amerikan Doları</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="h5 mb-0 fw-bold text-success">{{ number_format($exchangeRates['USD'] ?? 0, 4) }} ₺</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between p-3 bg-warning-50 rounded-3">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-warning me-3">EUR</div>
                                <div>
                                    <div class="fw-semibold">1 EUR</div>
                                    <div class="text-xs text-muted">Avrupa Euro</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="h5 mb-0 fw-bold text-warning">{{ number_format($exchangeRates['EUR'] ?? 0, 4) }} ₺</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Top 10 Most Sold Products -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ri-trophy-line me-2"></i>En Çok Satan 10 Ürün/Hizmet</h5>
                <small class="text-muted">Tüm zamanlar bazında</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover bordered-table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">#</th>
                                <th class="border-0">Ürün/Hizmet Adı</th>
                                <th class="border-0 text-center">Toplam Miktar</th>
                                <th class="border-0 text-center">Satış Sayısı</th>
                                <th class="border-0 text-end">Toplam Gelir</th>
                                <th class="border-0 text-end">Toplam Maliyet</th>
                                <th class="border-0 text-end">Kar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellingProducts as $index => $product)
                                <tr>
                                    <td class="fw-semibold">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="badge bg-primary-subtle text-primary me-2">{{ $index + 1 }}</div>
                                            <span class="fw-medium">{{ $product->product_service_name }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info-subtle text-info">{{ number_format($product->total_quantity, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success">{{ $product->sale_count }}</span>
                                    </td>
                                    <td class="text-end fw-semibold">{{ number_format($product->total_revenue, 2) }} TRY</td>
                                    <td class="text-end fw-semibold text-danger">{{ number_format($product->total_cost ?? 0, 2) }} TRY</td>
                                    <td class="text-end fw-bold {{ ($product->total_revenue - ($product->total_cost ?? 0)) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($product->total_revenue - ($product->total_cost ?? 0), 2) }} TRY
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Henüz satış verisi bulunmuyor</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Charts -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ri-bar-chart-line me-2"></i>Aylık Satış Trendi</h5>
                <div>
                    <select id="salesPeriod" class="form-select form-select-sm bg-base border text-secondary-light">
                        <option value="monthly" selected>Aylık (12 ay)</option>
                        <option value="weekly">Haftalık (12 hafta)</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div id="salesChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Revenue by Currency -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-pie-chart-line me-2"></i>Bu Ay Gelir (Para Birimi)</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @php
                        $currencySymbols = ['TRY' => '₺', 'USD' => '$', 'EUR' => '€'];
                        $currencyColors = ['TRY' => 'primary', 'USD' => 'success', 'EUR' => 'warning'];
                    @endphp
                    @foreach(['TRY', 'USD', 'EUR'] as $currency)
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded-3 {{ $monthlyRevenueByCurrency[$currency] > 0 ? 'border-'.$currencyColors[$currency] : '' }}">
                                <div class="h4 mb-2 fw-bold text-{{ $currencyColors[$currency] }}">
                                    {{ $currencySymbols[$currency] }}{{ number_format($monthlyRevenueByCurrency[$currency], 2) }}
                                </div>
                                <div class="text-sm text-muted">{{ $currency }}</div>
                                @if($monthlyRevenueByCurrency[$currency] > 0 && $currency !== 'TRY')
                                    <div class="text-xs text-muted mt-1">
                                        ≈ {{ number_format($monthlyRevenueByCurrency[$currency] * ($exchangeRates[$currency] ?? 1), 2) }} TRY
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Overview -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-wallet-line me-2"></i>Finansal Durum</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <div class="p-3 bg-info-50 rounded-3 text-center">
                            <div class="text-secondary-light text-sm">Bu Ay Tahsilat</div>
                            <div class="h6 mb-0 fw-semibold">{{ number_format($collectionsThisMonth, 2) }} TRY</div>
                            <div class="text-xs text-muted mt-1">
                                @if($collectionsThisMonthByCurrency['USD'] > 0) ${{ number_format($collectionsThisMonthByCurrency['USD'], 2) }} @endif
                                @if($collectionsThisMonthByCurrency['EUR'] > 0) €{{ number_format($collectionsThisMonthByCurrency['EUR'], 2) }} @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 bg-danger-50 rounded-3 text-center">
                            <div class="text-secondary-light text-sm">Ödenmemiş Faturalar</div>
                            <div class="h6 mb-0 fw-semibold">{{ number_format($unpaidInvoicesTotal, 2) }} TRY</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 bg-primary-50 rounded-3 text-center">
                            <div class="text-secondary-light text-sm">Müşteri Borcu (TRY)</div>
                            <div class="h6 mb-0 fw-semibold {{ $customerDebtTry >= 0 ? 'text-danger' : 'text-success' }}">{{ number_format($customerDebtTry, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 bg-success-50 rounded-3 text-center">
                            <div class="text-secondary-light text-sm">Müşteri Borcu (USD)</div>
                            <div class="h6 mb-0 fw-semibold {{ $customerDebtUsd >= 0 ? 'text-danger' : 'text-success' }}">{{ number_format($customerDebtUsd, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 bg-warning-50 rounded-3 text-center">
                            <div class="text-secondary-light text-sm">Müşteri Borcu (EUR)</div>
                            <div class="h6 mb-0 fw-semibold {{ $customerDebtEur >= 0 ? 'text-danger' : 'text-success' }}">{{ number_format($customerDebtEur, 2) }}</div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-user-star-line me-2"></i>En İyi Müşteriler</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm bordered-table mb-0">
                        <thead>
                            <tr>
                                <th>Müşteri</th>
                                <th class="text-end">Toplam Satış</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCustomers as $customer)
                                <tr>
                                    <td>{{ $customer->customer->name ?? $customer->customer->company_name ?? 'Bilinmeyen Müşteri' }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($customer->total_amount, 2) }} TRY</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Müşteri verisi bulunamadı</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Due Invoices -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-alarm-warning-line me-2"></i>Vadesi Yaklaşan Faturalar</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm bordered-table mb-0">
                        <thead>
                            <tr>
                                <th>Fatura No</th>
                                <th>Müşteri</th>
                                <th>Vade</th>
                                <th class="text-end">Tutar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingDueInvoices as $invoice)
                                <tr>
                                    <td><span class="badge bg-primary-subtle text-primary">{{ $invoice->invoice_number }}</span></td>
                                    <td>{{ $invoice->customer->name ?? $invoice->customer->company_name ?? 'Bilinmeyen' }}</td>
                                    <td>
                                        <span class="badge bg-warning-subtle text-warning">
                                            {{ $invoice->due_date ? $invoice->due_date->format('d.m.Y') : '-' }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Vadesi yaklaşan fatura yok</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Expenses by Category -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-file-list-3-line me-2"></i>Bu Ay Gider Kategorileri</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover bordered-table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">Gider Kategorisi</th>
                                <th class="border-0 text-end">Toplam Tutar</th>
                                <th class="border-0 text-center">Oran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalExpenses = $monthlyExpensesByCategory->sum('total_amount'); @endphp
                            @forelse($monthlyExpensesByCategory as $expense)
                                <tr>
                                    <td class="fw-medium">{{ $expense->name }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($expense->total_amount, 2) }} TRY</td>
                                    <td class="text-center">
                                        @if($totalExpenses > 0)
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-danger" 
                                                     style="width: {{ ($expense->total_amount / $totalExpenses) * 100 }}%">
                                                    {{ number_format(($expense->total_amount / $totalExpenses) * 100, 1) }}%
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">0%</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Bu ay gider verisi bulunmuyor</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    (function(){
        // Sales chart data
        const monthlyLabels = @json($monthlyLabels);
        const monthlySalesData = @json($monthlySalesData);
        const weeklyLabels = @json($weeklyLabels);
        const weeklySalesData = @json($weeklySalesData);

        let currentLabels = monthlyLabels;
        let currentData = monthlySalesData;

        // Initialize sales chart
        const salesChartEl = document.querySelector('#salesChart');
        const salesChartOptions = {
            chart: { 
                type: 'bar', 
                height: 300, 
                toolbar: { show: false },
                background: 'transparent'
            },
            series: [{ 
                name: 'Satış', 
                data: currentData,
                color: '#0d6efd'
            }],
            xaxis: { 
                categories: currentLabels,
                labels: {
                    style: {
                        fontSize: '11px'
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return new Intl.NumberFormat('tr-TR').format(val) + ' TRY';
                    }
                }
            },
            dataLabels: { enabled: false },
            plotOptions: { 
                bar: { 
                    borderRadius: 4,
                    columnWidth: '60%'
                } 
            },
            grid: {
                borderColor: '#e7eef7',
                strokeDashArray: 3
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return new Intl.NumberFormat('tr-TR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(val) + ' TRY';
                    }
                }
            }
        };

        const salesChart = new ApexCharts(salesChartEl, salesChartOptions);
        salesChart.render();

        // Handle period change
        document.getElementById('salesPeriod').addEventListener('change', (e) => {
            const period = e.target.value;
            if (period === 'weekly') {
                currentLabels = weeklyLabels;
                currentData = weeklySalesData;
            } else {
                currentLabels = monthlyLabels;
                currentData = monthlySalesData;
            }
            
            salesChart.updateOptions({ 
                xaxis: { categories: currentLabels } 
            });
            salesChart.updateSeries([{ data: currentData }]);
        });

    })();
</script>
@endpush
