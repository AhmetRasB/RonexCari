@extends('layout.layout')

@section('title', 'Alış Faturası Önizleme')
@section('subTitle', 'Alış Faturası Yazdırma Önizleme')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                    <a href="{{ route('purchases.invoices.show', $invoice) }}" class="btn btn-sm btn-secondary radius-8 d-inline-flex align-items-center gap-1">
                        <iconify-icon icon="solar:arrow-left-outline" class="text-xl"></iconify-icon>
                        Geri Dön
                    </a>
                    <a href="{{ route('purchases.invoices.edit', $invoice) }}" class="btn btn-sm btn-success radius-8 d-inline-flex align-items-center gap-1">
                        <iconify-icon icon="lucide:edit" class="text-xl"></iconify-icon>
                        Düzenle
                    </a>
                    <button type="button" class="btn btn-sm btn-warning radius-8 d-inline-flex align-items-center gap-1" onclick="downloadInvoice()">
                        <iconify-icon icon="solar:download-linear" class="text-xl"></iconify-icon>
                        İndir
                    </button>
                    <a href="{{ route('purchases.invoices.print', $invoice) }}" target="_blank" class="btn btn-sm btn-danger radius-8 d-inline-flex align-items-center gap-1">
                        <iconify-icon icon="basil:printer-outline" class="text-xl"></iconify-icon>
                        Yazdır
                    </a>
                </div>
            </div>
            <div class="card-body py-40">
                <div class="row justify-content-center" id="invoice">
                    <div class="col-lg-8">
                        <div class="shadow-4 border radius-8">
                            <div class="p-20 d-flex flex-wrap justify-content-between gap-3 border-bottom">
                                <div>
                                    <h3 class="text-xl">Fatura #{{ $invoice->invoice_number }}</h3>
                                    <p class="mb-1 text-sm">Fatura Tarihi: {{ $invoice->invoice_date->format('d/m/Y') }}</p>
                                    <p class="mb-0 text-sm">Vade Tarihi: {{ $invoice->due_date->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <img src="{{ asset('assets/images/logo.png') }}" alt="Ronex Logo" class="mb-8" style="max-height: 60px; max-width: 168px; display: block;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div style="display:none; font-size: 24px; font-weight: bold; color: #333; margin-bottom: 10px;">RONEX</div>
                                    <p class="mb-1 text-sm">Ronex Tekstil San. Tic. Ltd. Şti.</p>
                                    <p class="mb-1 text-sm">Adres: Örnek Mahallesi, Tekstil Caddesi No:123</p>
                                    <p class="mb-0 text-sm">İstanbul, Türkiye</p>
                                    <p class="mb-0 text-sm">Tel: +90 212 123 45 67</p>
                                </div>
                            </div>
                            <div class="py-28 px-20">
                                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                                    <div>
                                        <h6 class="text-md">Fatura Edilen (Alıcı):</h6>
                                        <table class="text-sm text-secondary-light">
                                            <tbody>
                                                <tr>
                                                    <td>Unvan</td>
                                                    <td class="ps-8">: Ronex Tekstil San. Tic. Ltd. Şti.</td>
                                                </tr>
                                                <tr>
                                                    <td>Adres</td>
                                                    <td class="ps-8">: Örnek Mahallesi, Tekstil Caddesi No:123, İstanbul / Türkiye</td>
                                                </tr>
                                                <tr>
                                                    <td>Telefon</td>
                                                    <td class="ps-8">: +90 212 123 45 67</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div>
                                        <table class="text-sm text-secondary-light">
                                            <tbody>
                                                <tr>
                                                    <td>Fatura Tarihi</td>
                                                    <td class="ps-8">: {{ $invoice->invoice_date->format('d.m.Y') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Fatura Saati</td>
                                                    <td class="ps-8">: {{ $invoice->invoice_time }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Vade Tarihi</td>
                                                    <td class="ps-8">: {{ $invoice->due_date->format('d.m.Y') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Para Birimi</td>
                                                    <td class="ps-8">: 
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
                                                    <td>Durum</td>
                                                    <td class="ps-8">: 
                                                        @switch($invoice->status)
                                                            @case('draft') Taslak @break
                                                            @case('sent') Gönderildi @break
                                                            @case('paid') Ödendi @break
                                                            @case('overdue') Vadesi Geçti @break
                                                            @case('cancelled') İptal @break
                                                        @endswitch
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="mt-16">
                                            <h6 class="text-md">Tedarikçi (Satıcı):</h6>
                                            <table class="text-sm text-secondary-light">
                                                <tbody>
                                                    <tr>
                                                        <td>Ad / Ünvan</td>
                                                        <td class="ps-8">: {{ optional($invoice->supplier)->name ?? '-' }}</td>
                                                    </tr>
                                                    @if(optional($invoice->supplier)->company_name)
                                                    <tr>
                                                        <td>Şirket</td>
                                                        <td class="ps-8">: {{ $invoice->supplier->company_name }}</td>
                                                    </tr>
                                                    @endif
                                                    @if(optional($invoice->supplier)->address)
                                                    <tr>
                                                        <td>Adres</td>
                                                        <td class="ps-8">: {{ $invoice->supplier->address }}</td>
                                                    </tr>
                                                    @endif
                                                    @if(optional($invoice->supplier)->phone)
                                                    <tr>
                                                        <td>Telefon</td>
                                                        <td class="ps-8">: {{ $invoice->supplier->phone }}</td>
                                                    </tr>
                                                    @endif
                                                    @if(optional($invoice->supplier)->email)
                                                    <tr>
                                                        <td>E-posta</td>
                                                        <td class="ps-8">: {{ $invoice->supplier->email }}</td>
                                                    </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-24">
                                    <div class="table-responsive scroll-sm">
                                        <table class="table bordered-table text-sm">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="text-sm">Sıra</th>
                                                    <th scope="col" class="text-sm">Ürün/Hizmet</th>
                                                    <th scope="col" class="text-sm">Açıklama</th>
                                                    <th scope="col" class="text-sm">Miktar</th>
                                                    <th scope="col" class="text-sm">Birim Fiyat</th>
                                                    <th scope="col" class="text-sm">KDV %</th>
                                                    <th scope="col" class="text-sm">İndirim %</th>
                                                    <th scope="col" class="text-end text-sm">Toplam</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($invoice->items as $index => $item)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $item->product_service_name }}</td>
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
                                                    <td class="text-end">
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
                                    <div class="d-flex flex-wrap justify-content-between gap-3">
                                        <div>
                                            @if($invoice->description)
                                            <p class="text-sm mb-0"><span class="text-primary-light fw-semibold">Açıklama:</span> {{ $invoice->description }}</p>
                                            @endif
                                            <p class="text-sm mb-0">İşlem tarihi: {{ $invoice->created_at->format('d.m.Y H:i') }}</p>
                                        </div>
                                        <div>
                                            <table class="text-sm">
                                                <tbody>
                                                    <tr>
                                                        <td class="pe-64">Ara Toplam:</td>
                                                        <td class="pe-16">
                                                            <span class="text-primary-light fw-semibold">
                                                                {{ number_format($invoice->subtotal, 2) }}
                                                                @if($invoice->currency === 'USD')
                                                                    $
                                                                @elseif($invoice->currency === 'EUR')
                                                                    €
                                                                @else
                                                                    ₺
                                                                @endif
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pe-64">KDV:</td>
                                                        <td class="pe-16">
                                                            <span class="text-primary-light fw-semibold">
                                                                {{ number_format($invoice->vat_amount, 2) }}
                                                                @if($invoice->currency === 'USD')
                                                                    $
                                                                @elseif($invoice->currency === 'EUR')
                                                                    €
                                                                @else
                                                                    ₺
                                                                @endif
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pe-64 border-bottom pb-4">
                                                            <span class="text-primary-light fw-semibold">Genel Toplam:</span>
                                                        </td>
                                                        <td class="pe-16 border-bottom pb-4">
                                                            <span class="text-primary-light fw-semibold">
                                                                {{ number_format($invoice->total_amount, 2) }}
                                                                @if($invoice->currency === 'USD')
                                                                    $
                                                                @elseif($invoice->currency === 'EUR')
                                                                    €
                                                                @else
                                                                    ₺
                                                                @endif
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @if($invoice->currency !== 'TRY')
                                                    <tr>
                                                        <td class="pe-64">
                                                            <span class="text-secondary-light">Toplam (TL):</span>
                                                        </td>
                                                        <td class="pe-16">
                                                            <span class="text-secondary-light" id="totalAmountTRY">-</span>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-64">
                                    <p class="text-center text-secondary-light text-sm fw-semibold">İşleminiz için teşekkür ederiz!</p>
                                </div>

                                <div class="d-flex flex-wrap justify-content-between align-items-end mt-64">
                                    <div class="text-sm border-top d-inline-block px-12">Müşteri İmzası</div>
                                    <div class="text-sm border-top d-inline-block px-12">Yetkili İmzası</div>
                                </div>
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
function downloadInvoice() {
    // PDF indirme işlemi için gelecekte implement edilebilir
    alert('PDF indirme özelliği yakında eklenecek!');
}

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
    $.get('{{ route("purchases.invoices.currency.rates") }}')
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
