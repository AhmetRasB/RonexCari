@extends('layout.layout')

@section('title', 'Müşteri Detayı')
@section('subTitle', 'Müşteri Bilgileri')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Müşteri Detayı</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('sales.customers.edit', $customer) }}" class="btn btn-primary">
                        <iconify-icon icon="lucide:edit" class="me-2"></iconify-icon>
                        Düzenle
                    </a>
                    <a href="{{ route('sales.customers.index') }}" class="btn btn-secondary">
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
                                    <div class="col-8">{{ $customer->name }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Şirket:</div>
                                    <div class="col-8">{{ $customer->company_name ?? '-' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">E-posta:</div>
                                    <div class="col-8">
                                        @if($customer->email)
                                            <a href="mailto:{{ $customer->email }}" class="text-primary-600">{{ $customer->email }}</a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Telefon:</div>
                                    <div class="col-8">
                                        @if($customer->phone)
                                            <a href="tel:{{ $customer->phone }}" class="text-primary-600">{{ $customer->phone }}</a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">İletişim Kişisi:</div>
                                    <div class="col-8">{{ $customer->contact_person ?? '-' }}</div>
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
                                    <div class="col-8">{{ $customer->tax_number ?? '-' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Bakiye (TL):</div>
                                    <div class="col-8">
                                        @if($customer->balance_try > 0)
                                            <span class="text-danger fw-semibold fs-5">+{{ number_format($customer->balance_try, 2) }} ₺</span>
                                            <small class="text-muted d-block">Borç</small>
                                        @elseif($customer->balance_try < 0)
                                            <span class="text-success fw-semibold fs-5">{{ number_format($customer->balance_try, 2) }} ₺</span>
                                            <small class="text-muted d-block">Alacak</small>
                                        @else
                                            <span class="text-muted fs-5">0,00 ₺</span>
                                            <small class="text-muted d-block">Bakiye yok</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Bakiye (USD):</div>
                                    <div class="col-8">
                                        @if($customer->balance_usd > 0)
                                            <span class="text-danger fw-semibold fs-5">+${{ number_format($customer->balance_usd, 2) }}</span>
                                            <small class="text-muted d-block">Borç</small>
                                        @elseif($customer->balance_usd < 0)
                                            <span class="text-success fw-semibold fs-5">${{ number_format($customer->balance_usd, 2) }}</span>
                                            <small class="text-muted d-block">Alacak</small>
                                        @else
                                            <span class="text-muted fs-5">$0.00</span>
                                            <small class="text-muted d-block">Bakiye yok</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Bakiye (EUR):</div>
                                    <div class="col-8">
                                        @if($customer->balance_eur > 0)
                                            <span class="text-danger fw-semibold fs-5">+€{{ number_format($customer->balance_eur, 2) }}</span>
                                            <small class="text-muted d-block">Borç</small>
                                        @elseif($customer->balance_eur < 0)
                                            <span class="text-success fw-semibold fs-5">€{{ number_format($customer->balance_eur, 2) }}</span>
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
                                        <span class="bg-{{ $customer->is_active ? 'success' : 'danger' }}-focus text-{{ $customer->is_active ? 'success' : 'danger' }}-main px-24 py-4 rounded-pill fw-medium text-sm">
                                            {{ $customer->is_active ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Oluşturulma:</div>
                                    <div class="col-8">{{ $customer->created_at->format('d.m.Y H:i') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Güncellenme:</div>
                                    <div class="col-8">{{ $customer->updated_at->format('d.m.Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($customer->address)
                <div class="mb-4">
                    <h6 class="fw-semibold text-primary-600 mb-2">
                        <iconify-icon icon="solar:map-point-outline" class="me-2"></iconify-icon>
                        Adres Bilgileri
                    </h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $customer->address }}</p>
                    </div>
                </div>
                @endif

                @if($customer->notes)
                <div class="mb-4">
                    <h6 class="fw-semibold text-primary-600 mb-2">
                        <iconify-icon icon="solar:notes-outline" class="me-2"></iconify-icon>
                        Notlar
                    </h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $customer->notes }}</p>
                    </div>
                </div>
                @endif

                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('sales.customers.edit', $customer) }}" class="btn btn-primary">
                        <iconify-icon icon="lucide:edit" class="me-2"></iconify-icon>
                        Düzenle
                    </a>
                    <form action="{{ route('sales.customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu müşteriyi silmek istediğinizden emin misiniz?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <iconify-icon icon="mingcute:delete-2-line" class="me-2"></iconify-icon>
                            Sil
                        </button>
                    </form>
                    <a href="{{ route('sales.customers.index') }}" class="btn btn-secondary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                        Geri Dön
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
