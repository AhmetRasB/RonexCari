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
                    <a href="{{ route('sales.invoices.print', $invoice) }}" target="_blank" class="btn btn-sm btn-warning radius-8 d-inline-flex align-items-center gap-1">
                        <iconify-icon icon="basil:printer-outline" class="text-xl"></iconify-icon>
                        Barkod Yazdır
                    </a>
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
                        <h6 class="fw-semibold mb-3">Fatura Kalemleri</h6>
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
                                        <th>İndirim Oranı</th>
                                        <th>Toplam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            {{ $item->product_service_name }}
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
                                        <td>%{{ $item->discount_rate }}</td>
                                        <td>
                                            {{ number_format($item->line_total, 2) }}
                                            @if($invoice->currency === 'USD')
                                                $
                                            @elseif($invoice->currency === 'EUR')
                                                €
                                            @else
                                                ₺
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
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Ara Toplam:</span>
                                    <span>
                                        {{ number_format($invoice->subtotal, 2) }}
                                        @if($invoice->currency === 'USD')
                                            $
                                        @elseif($invoice->currency === 'EUR')
                                            €
                                        @else
                                            ₺
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>KDV:</span>
                                    <span>
                                        {{ number_format($invoice->vat_amount, 2) }}
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
                                        {{ number_format($invoice->total_amount, 2) }}
                                        @if($invoice->currency === 'USD')
                                            $
                                        @elseif($invoice->currency === 'EUR')
                                            €
                                        @else
                                            ₺
                                        @endif
                                    </span>
                                </div>
                                @if($invoice->currency !== 'TRY')
                                <hr>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Döviz Kuru:</span>
                                    <span id="exchangeRateDisplay">-</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Toplam (TL):</span>
                                    <span id="totalAmountTRY">-</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Calculate TL equivalent for foreign currency invoices
@if($invoice->currency !== 'TRY')
$(document).ready(function() {
    const invoiceCurrency = '{{ $invoice->currency }}';
    const totalAmount = {{ $invoice->total_amount }};
    
    // Get unit prices for TL conversion
    const unitPrices = [
        @foreach($invoice->items as $index => $item)
            {{ $item->unit_price }}{{ $index < count($invoice->items) - 1 ? ',' : '' }}
        @endforeach
    ];
    
    // Get exchange rates
    $.get('{{ route("sales.invoices.currency.rates") }}')
        .done(function(response) {
            let exchangeRate;
            if (response.success && response.rates[invoiceCurrency]) {
                exchangeRate = response.rates[invoiceCurrency];
            } else {
                // Fallback rates
                const fallbackRates = {
                    'USD': 41.29,
                    'EUR': 48.55
                };
                exchangeRate = fallbackRates[invoiceCurrency] || 1;
            }
            
            // Calculate total amount in TL
            const totalAmountTRY = totalAmount * exchangeRate;
            $('#exchangeRateDisplay').text(exchangeRate.toFixed(4));
            $('#totalAmountTRY').text(totalAmountTRY.toFixed(2).replace('.', ',') + ' ₺');
            
            // Calculate unit prices in TL
            unitPrices.forEach((unitPrice, index) => {
                const unitPriceTRY = unitPrice * exchangeRate;
                $('#unitPriceTRY_' + index).text('(' + unitPriceTRY.toFixed(2).replace('.', ',') + ' ₺)');
            });
        })
        .fail(function() {
            // Fallback rates if API fails
            const fallbackRates = {
                'USD': 41.29,
                'EUR': 48.55
            };
            const exchangeRate = fallbackRates[invoiceCurrency] || 1;
            const totalAmountTRY = totalAmount * exchangeRate;
            
            $('#exchangeRateDisplay').text(exchangeRate.toFixed(4));
            $('#totalAmountTRY').text(totalAmountTRY.toFixed(2).replace('.', ',') + ' ₺');
            
            // Calculate unit prices in TL with fallback rates
            unitPrices.forEach((unitPrice, index) => {
                const unitPriceTRY = unitPrice * exchangeRate;
                $('#unitPriceTRY_' + index).text('(' + unitPriceTRY.toFixed(2).replace('.', ',') + ' ₺)');
            });
        });
});
@endif
</script>
@endpush
@endsection