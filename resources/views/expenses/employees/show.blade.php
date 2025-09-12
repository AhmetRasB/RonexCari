@extends('layout.layout')

@section('title', 'Çalışan Detayı')
@section('subTitle', 'Çalışan Bilgileri')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Çalışan Detayı</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('expenses.employees.edit', $employee) }}" class="btn btn-primary">
                        <iconify-icon icon="lucide:edit" class="me-2"></iconify-icon>
                        Düzenle
                    </a>
                    @if($employee->remaining_salary > 0)
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paySalaryModal">
                            <iconify-icon icon="solar:dollar-minimalistic-outline" class="me-2"></iconify-icon>
                            Maaş Öde
                        </button>
                    @endif
                    <a href="{{ route('expenses.employees.index') }}" class="btn btn-secondary">
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
                                <iconify-icon icon="solar:user-outline" class="me-2"></iconify-icon>
                                Kişisel Bilgiler
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Ad Soyad:</div>
                                    <div class="col-8">{{ $employee->name }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Telefon:</div>
                                    <div class="col-8">
                                        @if($employee->phone)
                                            <a href="tel:{{ $employee->phone }}" class="text-primary-600">{{ $employee->phone }}</a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Yakın Adı:</div>
                                    <div class="col-8">{{ $employee->emergency_contact_name ?? '-' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Yakın Telefonu:</div>
                                    <div class="col-8">
                                        @if($employee->emergency_contact_phone)
                                            <a href="tel:{{ $employee->emergency_contact_phone }}" class="text-primary-600">{{ $employee->emergency_contact_phone }}</a>
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
                                <iconify-icon icon="solar:dollar-minimalistic-outline" class="me-2"></iconify-icon>
                                Maaş Bilgileri
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Aylık Maaş:</div>
                                    <div class="col-8">
                                        <span class="text-primary-600 fw-semibold fs-5">{{ number_format($employee->monthly_salary, 2) }} ₺</span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Maaş Günü:</div>
                                    <div class="col-8">
                                        <span class="bg-info-focus text-info-main px-24 py-4 rounded-pill fw-medium text-sm">{{ $employee->salary_day }}</span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Biriken Maaş:</div>
                                    <div class="col-8">
                                        <span class="text-warning-600 fw-semibold fs-5">{{ number_format($employee->accumulated_salary, 2) }} ₺</span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Ödenen Maaş:</div>
                                    <div class="col-8">
                                        <span class="text-success-600 fw-semibold fs-5">{{ number_format($employee->paid_amount, 2) }} ₺</span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Kalan Borç:</div>
                                    <div class="col-8">
                                        @if($employee->remaining_salary > 0)
                                            <span class="text-danger-600 fw-semibold fs-5">{{ number_format($employee->remaining_salary, 2) }} ₺</span>
                                        @else
                                            <span class="text-success-600 fw-semibold fs-5">0 ₺</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h6 class="fw-semibold text-primary-600 mb-2">
                                <iconify-icon icon="solar:check-circle-outline" class="me-2"></iconify-icon>
                                Durum Bilgileri
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Durum:</div>
                                    <div class="col-8">
                                        <span class="bg-{{ $employee->is_active ? 'success' : 'danger' }}-focus text-{{ $employee->is_active ? 'success' : 'danger' }}-main px-24 py-4 rounded-pill fw-medium text-sm">
                                            {{ $employee->is_active ? 'Aktif' : 'Pasif' }}
                                        </span>
                                        @if($employee->salary_status === 'pending')
                                            <br><span class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm mt-1">Maaş Bekliyor</span>
                                        @elseif($employee->salary_status === 'paid')
                                            <br><span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm mt-1">Ödendi</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Son Ödeme:</div>
                                    <div class="col-8">{{ $employee->last_payment_date ? $employee->last_payment_date->format('d.m.Y') : '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h6 class="fw-semibold text-primary-600 mb-2">
                                <iconify-icon icon="solar:clock-circle-outline" class="me-2"></iconify-icon>
                                Sistem Bilgileri
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Oluşturulma:</div>
                                    <div class="col-8">{{ $employee->created_at->format('d.m.Y H:i') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Güncellenme:</div>
                                    <div class="col-8">{{ $employee->updated_at->format('d.m.Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('expenses.employees.edit', $employee) }}" class="btn btn-primary">
                        <iconify-icon icon="lucide:edit" class="me-2"></iconify-icon>
                        Düzenle
                    </a>
                    @if($employee->remaining_salary > 0)
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paySalaryModal">
                            <iconify-icon icon="solar:dollar-minimalistic-outline" class="me-2"></iconify-icon>
                            Maaş Öde
                        </button>
                    @endif
                    <form action="{{ route('expenses.employees.destroy', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu çalışanı silmek istediğinizden emin misiniz?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <iconify-icon icon="mingcute:delete-2-line" class="me-2"></iconify-icon>
                            Sil
                        </button>
                    </form>
                    <a href="{{ route('expenses.employees.index') }}" class="btn btn-secondary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                        Geri Dön
                    </a>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<!-- Pay Salary Modal -->
<div class="modal fade" id="paySalaryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Maaş Ödemesi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('expenses.employees.paySalary', $employee) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Çalışan</label>
                        <p class="form-control-plaintext fw-bold">{{ $employee->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kalan Borç</label>
                        <p class="form-control-plaintext text-danger fw-bold">{{ number_format($employee->remaining_salary, 2) }} ₺</p>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Ödeme Tutarı (₺) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="{{ $employee->remaining_salary }}" 
                               class="form-control" id="amount" name="amount" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">
                        <iconify-icon icon="solar:dollar-minimalistic-outline" class="me-2"></iconify-icon>
                        Ödeme Yap
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
