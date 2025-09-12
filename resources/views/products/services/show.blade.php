@extends('layout.layout')

@section('title', 'Hizmetler')
@section('subTitle', 'Hizmet Detayları')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Hizmet Detayları</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('services.edit', $service) }}" class="btn btn-primary">
                        <iconify-icon icon="solar:pen-outline" class="me-2"></iconify-icon>
                        Düzenle
                    </a>
                    <a href="{{ route('services.index') }}" class="btn btn-secondary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                        Geri Dön
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Hizmet Bilgileri -->
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-3">Hizmet Bilgileri</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-medium text-secondary-light" style="width: 40%;">Hizmet Adı:</td>
                                <td>{{ $service->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium text-secondary-light">Hizmet Kodu:</td>
                                <td>{{ $service->code ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium text-secondary-light">Kategori:</td>
                                <td>{{ $service->category ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium text-secondary-light">Açıklama:</td>
                                <td>{{ $service->description ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Fiyat Bilgileri -->
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-3">Fiyat Bilgileri</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-medium text-secondary-light" style="width: 40%;">Satış Fiyatı:</td>
                                <td>
                                    <span class="fw-semibold text-primary">
                                        {{ number_format($service->price, 2) }} {{ $service->currency ?? 'TRY' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium text-secondary-light">Para Birimi:</td>
                                <td>{{ $service->currency ?? 'TRY' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium text-secondary-light">KDV Oranı:</td>
                                <td>%{{ $service->vat_rate ?? 20 }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium text-secondary-light">KDV Tutarı:</td>
                                <td>
                                    <span class="fw-semibold text-success">
                                        {{ number_format($service->price * (($service->vat_rate ?? 20) / 100), 2) }} {{ $service->currency ?? 'TRY' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium text-secondary-light">Toplam Tutar:</td>
                                <td>
                                    <span class="fw-semibold text-primary fs-5">
                                        {{ number_format($service->price * (1 + ($service->vat_rate ?? 20) / 100), 2) }} {{ $service->currency ?? 'TRY' }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
                    <!-- Durum Bilgileri -->
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-3">Durum Bilgileri</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-medium text-secondary-light" style="width: 40%;">Durum:</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="switchActive" {{ $service->is_active ? 'checked' : '' }} disabled>
                                        <label class="form-check-label" for="switchActive">
                                            {{ $service->is_active ? 'Aktif' : 'Pasif' }}
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium text-secondary-light">Oluşturulma:</td>
                                <td>{{ $service->created_at->format('d.m.Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium text-secondary-light">Son Güncelleme:</td>
                                <td>{{ $service->updated_at->format('d.m.Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
