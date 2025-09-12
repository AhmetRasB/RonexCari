@extends('layout.layout')

@section('title', 'Alış Faturası Detayı')
@section('subTitle', 'Alış Faturası Bilgileri')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h5 class="card-title mb-0">Alış Faturası Detayı</h5>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('purchases.invoices.preview', $invoice) }}" class="btn btn-sm btn-primary-600 radius-8 d-inline-flex align-items-center gap-1">
                        <iconify-icon icon="iconamoon:eye-light" class="text-xl"></iconify-icon>
                        Önizle
                    </a>
                    <a href="{{ route('purchases.invoices.edit', $invoice) }}" class="btn btn-sm btn-success radius-8 d-inline-flex align-items-center gap-1">
                        <iconify-icon icon="lucide:edit" class="text-xl"></iconify-icon>
                        Düzenle
                    </a>
                    <a href="{{ route('purchases.invoices.print', $invoice) }}" target="_blank" class="btn btn-sm btn-warning radius-8 d-inline-flex align-items-center gap-1">
                        <iconify-icon icon="basil:printer-outline" class="text-xl"></iconify-icon>
                        Yazdır
                    </a>
                    <a href="{{ route('purchases.invoices.index') }}" class="btn btn-sm btn-secondary radius-8 d-inline-flex align-items-center gap-1">
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
                                <td>{{ $invoice->invoice_time }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Vade Tarihi:</td>
                                <td>{{ $invoice->due_date->format('d.m.Y') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Para Birimi:</td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $invoice->currency === 'TRY' ? '₺ TRY' : ($invoice->currency === 'USD' ? '$ USD' : '€ EUR') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium">KDV Durumu:</td>
                                <td>
                                    <span class="badge bg-{{ $invoice->vat_status === 'included' ? 'success' : 'warning' }}">
                                        {{ $invoice->vat_status === 'included' ? 'Dahil' : 'Hariç' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Durum:</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'pending' => 'warning',
                                            'approved' => 'info',
                                            'paid' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusTexts = [
                                            'draft' => 'Taslak',
                                            'pending' => 'Beklemede',
                                            'approved' => 'Onaylandı',
                                            'paid' => 'Ödendi',
                                            'cancelled' => 'İptal'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }}">
                                        {{ $statusTexts[$invoice->status] ?? $invoice->status }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Ödeme Durumu:</td>
                                <td>
                                    @if($invoice->payment_completed)
                                        <span class="badge bg-success">
                                            <iconify-icon icon="solar:check-circle-outline" class="me-1"></iconify-icon>
                                            Ödeme Yapıldı
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <iconify-icon icon="solar:clock-circle-outline" class="me-1"></iconify-icon>
                                            Ödeme Bekleniyor
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-3">Tedarikçi Bilgileri</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-medium">Tedarikçi Adı:</td>
                                <td>{{ $invoice->supplier->name }}</td>
                            </tr>
                            @if($invoice->supplier->company_name)
                            <tr>
                                <td class="fw-medium">Şirket Adı:</td>
                                <td>{{ $invoice->supplier->company_name }}</td>
                            </tr>
                            @endif
                            @if($invoice->supplier->email)
                            <tr>
                                <td class="fw-medium">E-posta:</td>
                                <td>{{ $invoice->supplier->email }}</td>
                            </tr>
                            @endif
                            @if($invoice->supplier->phone)
                            <tr>
                                <td class="fw-medium">Telefon:</td>
                                <td>{{ $invoice->supplier->phone }}</td>
                            </tr>
                            @endif
                            @if($invoice->supplier->address)
                            <tr>
                                <td class="fw-medium">Adres:</td>
                                <td>{{ $invoice->supplier->address }}</td>
                            </tr>
                            @endif
                            @if($invoice->supplier->tax_number)
                            <tr>
                                <td class="fw-medium">Vergi No:</td>
                                <td>{{ $invoice->supplier->tax_number }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                @if($invoice->description)
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="fw-semibold mb-3">Açıklama</h6>
                        <div class="alert alert-light">
                            {{ $invoice->description }}
                        </div>
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
                                        <th>Ürün/Hizmet</th>
                                        <th>Açıklama</th>
                                        <th>Miktar</th>
                                        <th>Birim Fiyat</th>
                                        <th>Para Birimi</th>
                                        <th>KDV %</th>
                                        <th>İndirim %</th>
                                        <th>Toplam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->items as $item)
                                    <tr>
                                        <td>{{ $item->product_service_name }}</td>
                                        <td>{{ $item->description ?? '-' }}</td>
                                        <td>{{ number_format($item->quantity, 2) }}</td>
                                        <td>{{ number_format($item->unit_price, 2) }}</td>
                                        <td>{{ $item->unit_currency }}</td>
                                        <td>{{ $item->tax_rate }}%</td>
                                        <td>{{ $item->discount_rate }}%</td>
                                        <td class="fw-semibold">{{ number_format($item->line_total, 2) }} {{ $item->unit_currency }}</td>
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
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3">Fatura Toplamları</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Ara Toplam:</span>
                                    <span class="fw-semibold">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>KDV:</span>
                                    <span class="fw-semibold">{{ number_format($invoice->vat_amount, 2) }} {{ $invoice->currency }}</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold fs-5">
                                    <span>Genel Toplam:</span>
                                    <span class="text-success">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
