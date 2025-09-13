@extends('layout.layout')

@section('title', 'Çalışan Detayı')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Çalışan Detayı</h6>
    <ul class="d-flex align-items-center gap-2">
        <li class="fw-medium">
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                Dashboard
            </a>
        </li>
        <li>-</li>
        <li class="fw-medium">
            <a href="{{ route('management.employees.index') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                Çalışanlar
            </a>
        </li>
        <li>-</li>
        <li class="fw-medium">{{ $employee->name }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:user-outline" class="text-xl"></iconify-icon>
                    <h6 class="mb-0">Çalışan Bilgileri</h6>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('management.employees.edit', $employee->id) }}" class="btn btn-warning btn-sm">
                        <iconify-icon icon="solar:pen-outline" class="icon text-lg"></iconify-icon>
                        Düzenle
                    </a>
                    <a href="{{ route('management.employees.index') }}" class="btn btn-outline-secondary btn-sm">
                        <iconify-icon icon="solar:arrow-left-outline" class="icon text-lg"></iconify-icon>
                        Geri Dön
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row gy-4">
                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary-50 p-3 rounded-8">
                                <iconify-icon icon="solar:user-outline" class="icon text-xl text-primary"></iconify-icon>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-semibold">İsim</h6>
                                <p class="mb-0 text-muted">{{ $employee->name }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success-50 p-3 rounded-8">
                                <iconify-icon icon="solar:letter-unread-outline" class="icon text-xl text-success"></iconify-icon>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-semibold">Email</h6>
                                <p class="mb-0 text-muted">{{ $employee->email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning-50 p-3 rounded-8">
                                <iconify-icon icon="solar:phone-outline" class="icon text-xl text-warning"></iconify-icon>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-semibold">Telefon</h6>
                                <p class="mb-0 text-muted">{{ $employee->phone ?? 'Belirtilmemiş' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-info-50 p-3 rounded-8">
                                <iconify-icon icon="solar:case-outline" class="icon text-xl text-info"></iconify-icon>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-semibold">Pozisyon</h6>
                                <p class="mb-0 text-muted">{{ $employee->position ?? 'Belirtilmemiş' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-danger-50 p-3 rounded-8">
                                <iconify-icon icon="solar:dollar-minimalistic-outline" class="icon text-xl text-danger"></iconify-icon>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-semibold">Maaş</h6>
                                <p class="mb-0 text-muted">{{ $employee->salary ? number_format($employee->salary, 2) . ' ₺' : 'Belirtilmemiş' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-secondary-50 p-3 rounded-8">
                                <iconify-icon icon="solar:calendar-outline" class="icon text-xl text-secondary"></iconify-icon>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-semibold">Başlangıç Tarihi</h6>
                                <p class="mb-0 text-muted">{{ $employee->start_date ? \Carbon\Carbon::parse($employee->start_date)->format('d.m.Y') : 'Belirtilmemiş' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-primary-50 p-3 rounded-8">
                                <iconify-icon icon="solar:map-point-outline" class="icon text-xl text-primary"></iconify-icon>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-semibold">Adres</h6>
                                <p class="mb-0 text-muted">{{ $employee->address ?? 'Belirtilmemiş' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-{{ $employee->is_active ? 'success' : 'danger' }}-50 p-3 rounded-8">
                                <iconify-icon icon="solar:{{ $employee->is_active ? 'check-circle' : 'close-circle' }}-outline" class="icon text-xl text-{{ $employee->is_active ? 'success' : 'danger' }}"></iconify-icon>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-semibold">Durum</h6>
                                <span class="badge bg-{{ $employee->is_active ? 'success' : 'danger' }} text-sm fw-semibold px-20 py-9 radius-4 text-white">
                                    {{ $employee->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row">
                    <div class="col-12 col-md-6">
                        <h6 class="fw-semibold mb-3">Oluşturulma Tarihi</h6>
                        <p class="text-muted">{{ $employee->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                    <div class="col-12 col-md-6">
                        <h6 class="fw-semibold mb-3">Son Güncelleme</h6>
                        <p class="text-muted">{{ $employee->updated_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
