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
                                    <h3 class="text-xl">Alış Faturası #{{ $invoice->invoice_number }}</h3>
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
                                        <h6 class="text-sm fw-semibold mb-2">Tedarikçi Bilgileri</h6>
                                        <p class="mb-1 text-sm fw-medium">{{ $invoice->supplier->name ?? 'Tedarikçi Silinmiş' }}</p>
                                        @if($invoice->supplier && $invoice->supplier->company_name)
                                            <p class="mb-1 text-sm">{{ $invoice->supplier->company_name }}</p>
                                        @endif
                                        @if($invoice->supplier && $invoice->supplier->address)
                                            <p class="mb-1 text-sm">{{ $invoice->supplier->address }}</p>
                                        @endif
                                        @if($invoice->supplier && $invoice->supplier->phone)
                                            <p class="mb-1 text-sm">Tel: {{ $invoice->supplier->phone }}</p>
                                        @endif
                                        @if($invoice->supplier && $invoice->supplier->email)
                                            <p class="mb-0 text-sm">E-posta: {{ $invoice->supplier->email }}</p>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <h6 class="text-sm fw-semibold mb-2">Fatura Bilgileri</h6>
                                        <p class="mb-1 text-sm">Para Birimi: {{ $invoice->currency }}</p>
                                        <p class="mb-1 text-sm">KDV Durumu: {{ $invoice->vat_status === 'included' ? 'KDV Dahil' : 'KDV Hariç' }}</p>
                                        <p class="mb-0 text-sm">Ödeme Durumu: 
                                            @if($invoice->payment_completed)
                                                <span class="badge bg-success-100 text-success-600 px-2 py-1 rounded-pill text-xs fw-medium">
                                                    Ödendi
                                                </span>
                                            @else
                                                <span class="badge bg-warning-100 text-warning-600 px-2 py-1 rounded-pill text-xs fw-medium">
                                                    Beklemede
                                                </span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                
                                @if($invoice->description)
                                <div class="mt-20">
                                    <h6 class="text-sm fw-semibold mb-2">Açıklama</h6>
                                    <p class="text-sm">{{ $invoice->description }}</p>
                                </div>
                                @endif

                                <div class="mt-20">
                                    <h6 class="text-sm fw-semibold mb-2">Fatura Kalemleri</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="text-sm fw-semibold">Ürün/Hizmet</th>
                                                    <th class="text-sm fw-semibold">Açıklama</th>
                                                    <th class="text-sm fw-semibold">Miktar</th>
                                                    <th class="text-sm fw-semibold">Birim Fiyat</th>
                                                    <th class="text-sm fw-semibold">KDV %</th>
                                                    <th class="text-sm fw-semibold">İndirim %</th>
                                                    <th class="text-sm fw-semibold">Toplam</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($invoice->items as $item)
                                                <tr>
                                                    <td class="text-sm">{{ $item->product_service_name }}</td>
                                                    <td class="text-sm">{{ $item->description ?? '-' }}</td>
                                                    <td class="text-sm">{{ number_format($item->quantity, 2) }}</td>
                                                    <td class="text-sm">{{ number_format($item->unit_price, 2) }} {{ $item->unit_currency }}</td>
                                                    <td class="text-sm">{{ number_format($item->tax_rate, 1) }}%</td>
                                                    <td class="text-sm">{{ number_format($item->discount_rate, 1) }}%</td>
                                                    <td class="text-sm fw-semibold">{{ number_format($item->line_total, 2) }} {{ $item->unit_currency }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="mt-20">
                                    <div class="row">
                                        <div class="col-md-6"></div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <span class="text-sm fw-medium">Ara Toplam:</span>
                                                <span class="text-sm fw-semibold">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</span>
                                            </div>
                                            @if($invoice->discount_amount > 0)
                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <span class="text-sm fw-medium">İndirim:</span>
                                                <span class="text-sm fw-semibold text-danger">-{{ number_format($invoice->discount_amount, 2) }} {{ $invoice->currency }}</span>
                                            </div>
                                            @endif
                                            @if($invoice->additional_discount > 0)
                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <span class="text-sm fw-medium">Ek İndirim:</span>
                                                <span class="text-sm fw-semibold text-danger">-{{ number_format($invoice->additional_discount, 2) }} {{ $invoice->currency }}</span>
                                            </div>
                                            @endif
                                            @if($invoice->vat_amount > 0)
                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <span class="text-sm fw-medium">KDV:</span>
                                                <span class="text-sm fw-semibold">{{ number_format($invoice->vat_amount, 2) }} {{ $invoice->currency }}</span>
                                            </div>
                                            @endif
                                            <div class="d-flex justify-content-between align-items-center py-2">
                                                <span class="text-lg fw-bold">Genel Toplam:</span>
                                                <span class="text-lg fw-bold text-primary">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</span>
                                            </div>
                                        </div>
                                    </div>
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
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    const invoiceContent = document.getElementById('invoice').innerHTML;
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Alış Faturası - {{ $invoice->invoice_number }}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .table { width: 100%; border-collapse: collapse; }
                .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .table th { background-color: #f5f5f5; }
                .text-end { text-align: right; }
                .fw-semibold { font-weight: 600; }
                .fw-bold { font-weight: bold; }
                .text-sm { font-size: 0.875rem; }
                .text-lg { font-size: 1.125rem; }
                .text-primary { color: #007bff; }
                .text-danger { color: #dc3545; }
                .border-bottom { border-bottom: 1px solid #ddd; }
                .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
                .mb-0 { margin-bottom: 0; }
                .mb-1 { margin-bottom: 0.25rem; }
                .mb-2 { margin-bottom: 0.5rem; }
                .mb-8 { margin-bottom: 2rem; }
                .mt-20 { margin-top: 1.25rem; }
                .p-20 { padding: 1.25rem; }
                .py-28 { padding-top: 1.75rem; padding-bottom: 1.75rem; }
                .px-20 { padding-left: 1.25rem; padding-right: 1.25rem; }
                .shadow-4 { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
                .border { border: 1px solid #dee2e6; }
                .radius-8 { border-radius: 0.5rem; }
                .d-flex { display: flex; }
                .justify-content-between { justify-content: space-between; }
                .align-items-center { align-items: center; }
                .flex-wrap { flex-wrap: wrap; }
                .gap-3 { gap: 1rem; }
                .bg-light { background-color: #f8f9fa; }
                .table-responsive { overflow-x: auto; }
            </style>
        </head>
        <body>
            ${invoiceContent}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}
</script>
@endpush
@endsection