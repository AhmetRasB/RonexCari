@extends('layout.layout')

@section('title', 'Borç Ekle')
@section('subTitle', 'Müşteri Borç İşlemleri')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">Müşteriye Borç Ekle</h5>
                    <p class="mb-0 text-muted">
                        {{ $customer->name }}
                        @if($customer->company_name)
                            - {{ $customer->company_name }}
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('sales.customers.show', $customer) }}" class="btn btn-secondary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                        Müşteri Detayına Dön
                    </a>
                    <a href="{{ route('sales.customers.index') }}" class="btn btn-light">
                        <iconify-icon icon="solar:users-group-rounded-outline" class="me-2"></iconify-icon>
                        Müşteri Listesi
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-semibold text-primary-600 mb-2">
                                <iconify-icon icon="solar:wallet-outline" class="me-2"></iconify-icon>
                                Mevcut Bakiye (TL)
                            </h6>
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
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-semibold text-primary-600 mb-2">
                                <iconify-icon icon="solar:wallet-outline" class="me-2"></iconify-icon>
                                Mevcut Bakiye (USD)
                            </h6>
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
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-semibold text-primary-600 mb-2">
                                <iconify-icon icon="solar:wallet-outline" class="me-2"></iconify-icon>
                                Mevcut Bakiye (EUR)
                            </h6>
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
                </div>

                <form action="{{ route('sales.customers.debt.store', $customer) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="currency" class="form-label">Para Birimi</label>
                            <select name="currency" id="currency" class="form-select" required>
                                <option value="">Seçiniz</option>
                                <option value="TRY" {{ old('currency') === 'TRY' ? 'selected' : '' }}>TRY (₺)</option>
                                <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD ($)</option>
                                <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="amount" class="form-label">Borç Tutarı</label>
                            <input type="number" name="amount" id="amount" step="0.01" min="0.01"
                                   value="{{ old('amount') }}"
                                   class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Durum</label>
                            <div class="form-control bg-light">
                                <span class="text-danger fw-semibold">Borç Artırma İşlemi</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama (İsteğe bağlı)</label>
                        <textarea name="description" id="description" rows="3" class="form-control"
                                  placeholder="Borç nedeni, fatura bilgisi vb.">{{ old('description') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('sales.customers.show', $customer) }}" class="btn btn-secondary">
                            İptal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <iconify-icon icon="solar:wallet-minimalistic-outline" class="me-2"></iconify-icon>
                            Borç Ekle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
