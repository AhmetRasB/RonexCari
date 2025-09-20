@extends('layout.layout')

@section('title', 'Maaş Ödemesi')
@section('subTitle', 'Yeni Maaş Ödemesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Maaş Ödemesi - {{ $employee->name }}</h5>
                <a href="{{ route('management.employees.index') }}" class="btn btn-outline-secondary">
                    <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                    Geri Dön
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Employee Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Çalışan Bilgileri</h6>
                                <p class="mb-1"><strong>İsim:</strong> {{ $employee->name }}</p>
                                <p class="mb-1"><strong>Pozisyon:</strong> {{ $employee->position ?? '-' }}</p>
                                <p class="mb-1"><strong>Departman:</strong> {{ $employee->department ?? '-' }}</p>
                                <p class="mb-0"><strong>Aylık Maaş:</strong> {{ $employee->salary ? number_format($employee->salary, 2) . ' ₺' : '-' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-info-100">
                            <div class="card-body">
                                <h6 class="card-title text-info-600">Ödeme Bilgileri</h6>
                                <p class="mb-1"><strong>Dönem:</strong> {{ \Carbon\Carbon::parse($currentMonth . '-01')->format('F Y') }}</p>
                                <p class="mb-1"><strong>Kalan Maaş:</strong> <span class="fw-bold text-success">{{ number_format($remainingSalary, 2) }} ₺</span></p>
                                <p class="mb-0"><strong>Maksimum Ödeme:</strong> <span class="fw-bold text-primary">{{ number_format($remainingSalary, 2) }} ₺</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('management.employees.salary-payments.store', $employee) }}" method="POST">
                    @csrf
                    
                    <div class="row gy-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Ödeme Tutarı (₺) <span class="text-danger-600">*</span></label>
                            <input type="number" step="0.01" max="{{ $remainingSalary }}" class="form-control radius-8 @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount') }}" placeholder="Ödeme tutarını girin" required>
                            <small class="text-muted">Maksimum: {{ number_format($remainingSalary, 2) }} ₺</small>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Ödeme Tarihi <span class="text-danger-600">*</span></label>
                            <input type="date" class="form-control radius-8 @error('payment_date') is-invalid @enderror" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Ödeme Yöntemi <span class="text-danger-600">*</span></label>
                            <select class="form-control radius-8 @error('payment_method') is-invalid @enderror" name="payment_method" required>
                                <option value="">Ödeme yöntemi seçin</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Nakit</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Banka Havalesi</option>
                                <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>Çek</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Dönem</label>
                            <input type="month" class="form-control radius-8 @error('month_year') is-invalid @enderror" name="month_year" value="{{ old('month_year', $currentMonth) }}" required>
                            @error('month_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Notlar</label>
                            <textarea class="form-control radius-8 @error('notes') is-invalid @enderror" name="notes" rows="3" placeholder="Ödeme ile ilgili notlar...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-3 mt-4">
                        <a href="{{ route('management.employees.index') }}" class="btn btn-outline-secondary">
                            <iconify-icon icon="solar:arrow-left-outline" class="icon text-lg"></iconify-icon>
                            İptal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <iconify-icon icon="solar:check-circle-outline" class="icon text-lg"></iconify-icon>
                            Maaş Ödemesi Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
