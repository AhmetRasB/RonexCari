@extends('layout.layout')

@section('title', 'Yeni Çalışan')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Yeni Çalışan</h6>
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
        <li class="fw-medium">Yeni</li>
    </ul>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center gap-2">
            <iconify-icon icon="solar:user-plus-outline" class="text-xl"></iconify-icon>
            <h6 class="mb-0">Yeni Çalışan Oluştur</h6>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('management.employees.store') }}" method="POST">
            @csrf
            
            <div class="row gy-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">İsim <span class="text-danger-600">*</span></label>
                    <input type="text" class="form-control radius-8 @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Çalışan adını girin" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Email <span class="text-danger-600">*</span></label>
                    <input type="email" class="form-control radius-8 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Email adresini girin" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Telefon</label>
                    <input type="tel" class="form-control radius-8 @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" placeholder="Telefon numarasını girin">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Pozisyon</label>
                    <input type="text" class="form-control radius-8 @error('position') is-invalid @enderror" name="position" value="{{ old('position') }}" placeholder="Pozisyonu girin">
                    @error('position')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Maaş (₺)</label>
                    <input type="number" step="0.01" class="form-control radius-8 @error('salary') is-invalid @enderror" name="salary" value="{{ old('salary') }}" placeholder="Maaş miktarını girin">
                    @error('salary')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Departman</label>
                    <input type="text" class="form-control radius-8 @error('department') is-invalid @enderror" name="department" value="{{ old('department') }}" placeholder="Departmanı girin">
                    @error('department')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">İşe Başlama Tarihi</label>
                    <input type="date" class="form-control radius-8 @error('hire_date') is-invalid @enderror" name="hire_date" value="{{ old('hire_date', \Carbon\Carbon::now()->format('Y-m-d')) }}">
                    @error('hire_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Aktif Çalışan
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-3 mt-4">
                <a href="{{ route('management.employees.index') }}" class="btn btn-outline-secondary">
                    <iconify-icon icon="solar:arrow-left-outline" class="icon text-lg"></iconify-icon>
                    Geri Dön
                </a>
                <button type="submit" class="btn btn-primary">
                    <iconify-icon icon="solar:check-circle-outline" class="icon text-lg"></iconify-icon>
                    Çalışan Oluştur
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
