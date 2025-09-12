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
                <p class="text-center py-4">Fatura düzenleme sayfası geliştiriliyor...</p>
                <div class="text-center">
                    <a href="{{ route('purchases.invoices.show', $invoice) }}" class="btn btn-primary me-2">
                        <iconify-icon icon="iconamoon:eye-light" class="me-2"></iconify-icon>
                        Görüntüle
                    </a>
                    <a href="{{ route('purchases.invoices.index') }}" class="btn btn-secondary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                        Geri Dön
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
