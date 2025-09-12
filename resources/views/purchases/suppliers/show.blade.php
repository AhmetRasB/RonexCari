@extends('layout.layout')

@section('title', 'Tedarikçi Detayı')
@section('subTitle', 'Tedarikçi Bilgileri')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tedarikçi Detayı</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('purchases.suppliers.edit', $supplier) }}" class="btn btn-primary">
                        <iconify-icon icon="lucide:edit" class="me-2"></iconify-icon>
                        Düzenle
                    </a>
                    <a href="{{ route('purchases.suppliers.index') }}" class="btn btn-secondary">
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
                                <iconify-icon icon="f7:person" class="me-2"></iconify-icon>
                                Kişisel Bilgiler
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Ad Soyad:</div>
                                    <div class="col-8">{{ $supplier->name }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Şirket:</div>
                                    <div class="col-8">{{ $supplier->company_name ?? '-' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">E-posta:</div>
                                    <div class="col-8">
                                        @if($supplier->email)
                                            <a href="mailto:{{ $supplier->email }}" class="text-primary-600">{{ $supplier->email }}</a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Telefon:</div>
                                    <div class="col-8">
                                        @if($supplier->phone)
                                            <a href="tel:{{ $supplier->phone }}" class="text-primary-600">{{ $supplier->phone }}</a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">İletişim Kişisi:</div>
                                    <div class="col-8">{{ $supplier->contact_person ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h6 class="fw-semibold text-primary-600 mb-2">
                                <iconify-icon icon="solar:document-outline" class="me-2"></iconify-icon>
                                Resmi Bilgiler
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Vergi No:</div>
                                    <div class="col-8">{{ $supplier->tax_number ?? '-' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Borç Bakiyesi (TL):</div>
                                    <div class="col-8">
                                        @if($supplier->balance_try > 0)
                                            <span class="text-danger fw-semibold fs-5">{{ number_format($supplier->balance_try, 2) }} ₺</span>
                                            <small class="text-muted d-block">Borç</small>
                                        @elseif($supplier->balance_try < 0)
                                            <span class="text-success fw-semibold fs-5">{{ number_format(abs($supplier->balance_try), 2) }} ₺</span>
                                            <small class="text-muted d-block">Alacak</small>
                                        @else
                                            <span class="text-muted fs-5">0,00 ₺</span>
                                            <small class="text-muted d-block">Bakiye yok</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Borç Bakiyesi (USD):</div>
                                    <div class="col-8">
                                        @if($supplier->balance_usd > 0)
                                            <span class="text-danger fw-semibold fs-5">${{ number_format($supplier->balance_usd, 2) }}</span>
                                            <small class="text-muted d-block">Borç</small>
                                        @elseif($supplier->balance_usd < 0)
                                            <span class="text-success fw-semibold fs-5">${{ number_format(abs($supplier->balance_usd), 2) }}</span>
                                            <small class="text-muted d-block">Alacak</small>
                                        @else
                                            <span class="text-muted fs-5">$0.00</span>
                                            <small class="text-muted d-block">Bakiye yok</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Borç Bakiyesi (EUR):</div>
                                    <div class="col-8">
                                        @if($supplier->balance_eur > 0)
                                            <span class="text-danger fw-semibold fs-5">€{{ number_format($supplier->balance_eur, 2) }}</span>
                                            <small class="text-muted d-block">Borç</small>
                                        @elseif($supplier->balance_eur < 0)
                                            <span class="text-success fw-semibold fs-5">€{{ number_format(abs($supplier->balance_eur), 2) }}</span>
                                            <small class="text-muted d-block">Alacak</small>
                                        @else
                                            <span class="text-muted fs-5">€0.00</span>
                                            <small class="text-muted d-block">Bakiye yok</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Durum:</div>
                                    <div class="col-8">
                                        <span class="bg-{{ $supplier->is_active ? 'success' : 'danger' }}-focus text-{{ $supplier->is_active ? 'success' : 'danger' }}-main px-24 py-4 rounded-pill fw-medium text-sm">
                                            {{ $supplier->is_active ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Oluşturulma:</div>
                                    <div class="col-8">{{ $supplier->created_at->format('d.m.Y H:i') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Güncellenme:</div>
                                    <div class="col-8">{{ $supplier->updated_at->format('d.m.Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($supplier->address)
                <div class="mb-4">
                    <h6 class="fw-semibold text-primary-600 mb-2">
                        <iconify-icon icon="solar:map-point-outline" class="me-2"></iconify-icon>
                        Adres Bilgileri
                    </h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $supplier->address }}</p>
                    </div>
                </div>
                @endif

                @if($supplier->notes)
                <div class="mb-4">
                    <h6 class="fw-semibold text-primary-600 mb-2">
                        <iconify-icon icon="solar:notes-outline" class="me-2"></iconify-icon>
                        Notlar
                    </h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $supplier->notes }}</p>
                    </div>
                </div>
                @endif

                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('purchases.suppliers.edit', $supplier) }}" class="btn btn-primary">
                        <iconify-icon icon="lucide:edit" class="me-2"></iconify-icon>
                        Düzenle
                    </a>
                    <form action="{{ route('purchases.suppliers.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu tedarikçiyi silmek istediğinizden emin misiniz?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <iconify-icon icon="mingcute:delete-2-line" class="me-2"></iconify-icon>
                            Sil
                        </button>
                    </form>
                    <a href="{{ route('purchases.suppliers.index') }}" class="btn btn-secondary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                        Geri Dön
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
