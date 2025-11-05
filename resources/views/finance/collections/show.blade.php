@extends('layout.layout')

@section('title', 'Tahsilat Detayı')
@section('subTitle', 'Tahsilat Bilgileri')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tahsilat Detayı</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('finance.collections.edit', $collection) }}" class="btn btn-primary">
                        <iconify-icon icon="lucide:edit" class="me-2"></iconify-icon>
                        Düzenle
                    </a>
                    <div class="btn-group">
                        <a href="{{ route('finance.collections.print', $collection) }}" target="_blank" class="btn btn-warning">
                            <iconify-icon icon="basil:printer-outline" class="me-2"></iconify-icon>
                            Yazdır
                        </a>
                        <button type="button" class="btn btn-warning dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" target="_blank" href="{{ route('finance.collections.print', $collection) }}?lang=tr">Türkçe</a></li>
                            <li><a class="dropdown-item" target="_blank" href="{{ route('finance.collections.print', $collection) }}?lang=en">English</a></li>
                            <li><a class="dropdown-item" target="_blank" href="{{ route('finance.collections.print', $collection) }}?lang=ar">العربية</a></li>
                            <li><a class="dropdown-item" target="_blank" href="{{ route('finance.collections.print', $collection) }}?lang=ru">Русский</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('finance.collections.index') }}" class="btn btn-secondary">
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
                                Müşteri Bilgileri
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Müşteri:</div>
                                    <div class="col-8">{{ $collection->customer->name ?? 'Müşteri Silinmiş' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">E-posta:</div>
                                    <div class="col-8">
                                        @if($collection->customer && $collection->customer->email)
                                            <a href="mailto:{{ $collection->customer->email }}" class="text-primary-600">{{ $collection->customer->email }}</a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Telefon:</div>
                                    <div class="col-8">
                                        @if($collection->customer && $collection->customer->phone)
                                            <a href="tel:{{ $collection->customer->phone }}" class="text-primary-600">{{ $collection->customer->phone }}</a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Şirket:</div>
                                    <div class="col-8">{{ $collection->customer->company_name ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h6 class="fw-semibold text-primary-600 mb-2">
                                <iconify-icon icon="solar:wallet-outline" class="me-2"></iconify-icon>
                                Tahsilat Bilgileri
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Tahsilat Türü:</div>
                                    <div class="col-8">
                                        <span class="badge bg-soft-info text-info">
                                            <iconify-icon icon="solar:wallet-outline" class="me-1"></iconify-icon>
                                            {{ $collection->collection_type_text }}
                                        </span>
                                    </div>
                                </div>
                                @if($collection->discount > 0)
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Toplam Borç:</div>
                                    <div class="col-8">
                                        <span class="fw-semibold">{{ number_format($collection->amount + $collection->discount, 2) }}</span>
                                        <span class="badge bg-soft-secondary text-secondary ms-1">{{ $collection->currency }}</span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Yapılan İndirim:</div>
                                    <div class="col-8">
                                        <span class="fw-semibold text-danger">-{{ number_format($collection->discount, 2) }}</span>
                                        <span class="badge bg-soft-danger text-danger ms-1">{{ $collection->currency }}</span>
                                    </div>
                                </div>
                                <div class="row mb-2" style="border-top: 2px solid #dee2e6; padding-top: 10px; margin-top: 10px;">
                                    <div class="col-4 fw-bold">Tahsil Edilen Tutar:</div>
                                    <div class="col-8">
                                        <span class="fw-bold fs-5 text-success">{{ number_format($collection->amount, 2) }}</span>
                                        <span class="badge bg-soft-success text-success ms-1">{{ $collection->currency }}</span>
                                    </div>
                                </div>
                                @else
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Tahsil Edilen Tutar:</div>
                                    <div class="col-8">
                                        <span class="fw-semibold fs-5">{{ number_format($collection->amount, 2) }}</span>
                                        <span class="badge bg-soft-secondary text-secondary ms-1">{{ $collection->currency }}</span>
                                    </div>
                                </div>
                                @endif
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Tarih:</div>
                                    <div class="col-8">{{ $collection->transaction_date->format('d.m.Y') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Durum:</div>
                                    <div class="col-8">
                                        <span class="bg-{{ $collection->is_active ? 'success' : 'danger' }}-focus text-{{ $collection->is_active ? 'success' : 'danger' }}-main px-24 py-4 rounded-pill fw-medium text-sm">
                                            {{ $collection->is_active ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Oluşturulma:</div>
                                    <div class="col-8">{{ $collection->created_at->format('d.m.Y H:i') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Güncellenme:</div>
                                    <div class="col-8">{{ $collection->updated_at->format('d.m.Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($collection->description)
                <div class="mb-4">
                    <h6 class="fw-semibold text-primary-600 mb-2">
                        <iconify-icon icon="solar:notes-outline" class="me-2"></iconify-icon>
                        Açıklama
                    </h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $collection->description }}</p>
                    </div>
                </div>
                @endif

                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('finance.collections.edit', $collection) }}" class="btn btn-primary">
                        <iconify-icon icon="lucide:edit" class="me-2"></iconify-icon>
                        Düzenle
                    </a>
                    <form action="{{ route('finance.collections.destroy', $collection) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu tahsilatı silmek istediğinizden emin misiniz?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <iconify-icon icon="mingcute:delete-2-line" class="me-2"></iconify-icon>
                            Sil
                        </button>
                    </form>
                    <a href="{{ route('finance.collections.index') }}" class="btn btn-secondary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                        Geri Dön
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection