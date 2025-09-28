@extends('layout.layout')

@section('title', 'Tedarikçi Ödeme Detayı')
@section('subTitle', 'Tedarikçi Ödeme Bilgileri')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tedarikçi Ödeme Detayı</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('finance.supplier-payments.edit', $supplierPayment) }}" class="btn btn-primary">
                        <iconify-icon icon="lucide:edit" class="me-2"></iconify-icon>
                        Düzenle
                    </a>
                    <a href="{{ route('finance.supplier-payments.index') }}" class="btn btn-secondary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                        Geri Dön
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h6 class="fw-semibold text-primary-600 mb-2">
                                <iconify-icon icon="solar:buildings-outline" class="me-2"></iconify-icon>
                                Tedarikçi Bilgileri
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Tedarikçi:</div>
                                    <div class="col-8">{{ $supplierPayment->supplier->name ?? 'Tedarikçi Silinmiş' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Şirket:</div>
                                    <div class="col-8">{{ $supplierPayment->supplier->company_name ?? '-' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">E-posta:</div>
                                    <div class="col-8">
                                        @if($supplierPayment->supplier && $supplierPayment->supplier->email)
                                            <a href="mailto:{{ $supplierPayment->supplier->email }}" class="text-primary-600">{{ $supplierPayment->supplier->email }}</a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Telefon:</div>
                                    <div class="col-8">
                                        @if($supplierPayment->supplier && $supplierPayment->supplier->phone)
                                            <a href="tel:{{ $supplierPayment->supplier->phone }}" class="text-primary-600">{{ $supplierPayment->supplier->phone }}</a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h6 class="fw-semibold text-primary-600 mb-2">
                                <iconify-icon icon="solar:wallet-outline" class="me-2"></iconify-icon>
                                Ödeme Bilgileri
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Ödeme Türü:</div>
                                    <div class="col-8">
                                        <span class="badge bg-soft-info text-info">
                                            <iconify-icon icon="solar:wallet-outline" class="me-1"></iconify-icon>
                                            {{ $supplierPayment->payment_type_text }}
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Tutar:</div>
                                    <div class="col-8">
                                        <span class="fw-semibold fs-5">{{ number_format($supplierPayment->amount, 2) }}</span>
                                        <span class="badge bg-soft-secondary text-secondary ms-1">{{ $supplierPayment->currency }}</span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Tarih:</div>
                                    <div class="col-8">{{ $supplierPayment->transaction_date->format('d.m.Y') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Durum:</div>
                                    <div class="col-8">
                                        <span class="bg-{{ $supplierPayment->is_active ? 'success' : 'danger' }}-focus text-{{ $supplierPayment->is_active ? 'success' : 'danger' }}-main px-24 py-4 rounded-pill fw-medium text-sm">
                                            {{ $supplierPayment->is_active ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Oluşturulma:</div>
                                    <div class="col-8">{{ $supplierPayment->created_at->format('d.m.Y H:i') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Güncellenme:</div>
                                    <div class="col-8">{{ $supplierPayment->updated_at->format('d.m.Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($supplierPayment->description)
                <div class="mb-4">
                    <h6 class="fw-semibold text-primary-600 mb-2">
                        <iconify-icon icon="solar:notes-outline" class="me-2"></iconify-icon>
                        Açıklama
                    </h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $supplierPayment->description }}</p>
                    </div>
                </div>
                @endif

                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('finance.supplier-payments.edit', $supplierPayment) }}" class="btn btn-primary">
                        <iconify-icon icon="lucide:edit" class="me-2"></iconify-icon>
                        Düzenle
                    </a>
                    <form action="{{ route('finance.supplier-payments.destroy', $supplierPayment) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu ödemeyi silmek istediğinizden emin misiniz?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <iconify-icon icon="mingcute:delete-2-line" class="me-2"></iconify-icon>
                            Sil
                        </button>
                    </form>
                    <a href="{{ route('finance.supplier-payments.index') }}" class="btn btn-secondary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                        Geri Dön
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
