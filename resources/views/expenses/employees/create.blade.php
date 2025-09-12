@extends('layout.layout')

@section('title', 'Yeni Çalışan')
@section('subTitle', 'Çalışan Ekle')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Yeni Çalışan Ekle</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('expenses.employees.store') }}" method="POST">
                    @csrf
                    <div class="row gy-3">
                        <div class="col-12">
                            <label class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:user-outline"></iconify-icon>
                                </span>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       placeholder="Ad Soyad girin" value="{{ old('name') }}" required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Telefon</label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:phone-calling-linear"></iconify-icon>
                                </span>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       placeholder="Telefon numarası girin" value="{{ old('phone') }}">
                            </div>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Yakın Adı</label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:user-plus-outline"></iconify-icon>
                                </span>
                                <input type="text" name="emergency_contact_name" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                       placeholder="Yakın adı girin" value="{{ old('emergency_contact_name') }}">
                            </div>
                            @error('emergency_contact_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Yakın Telefonu</label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:phone-calling-linear"></iconify-icon>
                                </span>
                                <input type="text" name="emergency_contact_phone" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                       placeholder="Yakın telefonu girin" value="{{ old('emergency_contact_phone') }}">
                            </div>
                            @error('emergency_contact_phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Aylık Maaş (₺) <span class="text-danger">*</span></label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:dollar-minimalistic-outline"></iconify-icon>
                                </span>
                                <input type="number" step="0.01" min="0" name="monthly_salary" class="form-control @error('monthly_salary') is-invalid @enderror" 
                                       placeholder="Aylık maaş girin" value="{{ old('monthly_salary') }}" required>
                            </div>
                            @error('monthly_salary')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Maaş Günü (1-28) <span class="text-danger">*</span></label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:calendar-outline"></iconify-icon>
                                </span>
                                <select name="salary_day" class="form-control @error('salary_day') is-invalid @enderror" required>
                                    <option value="">Seçiniz</option>
                                    @for($i = 1; $i <= 28; $i++)
                                        <option value="{{ $i }}" {{ old('salary_day') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            @error('salary_day')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <iconify-icon icon="solar:check-circle-outline" class="me-2"></iconify-icon>
                                    Aktif
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary-600">
                                <iconify-icon icon="solar:check-circle-outline" class="me-2"></iconify-icon>
                                Kaydet
                            </button>
                            <a href="{{ route('expenses.employees.index') }}" class="btn btn-secondary ms-2">
                                <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                                Geri Dön
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
