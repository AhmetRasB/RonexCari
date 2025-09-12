@extends('layout.layout')

@section('title', 'Hizmetler')
@section('subTitle', 'Yeni Hizmet Ekle')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Hizmet Bilgileri</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('services.store') }}" method="POST">
                    @csrf
                    
                    <!-- Hizmet Bilgileri -->
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3 d-flex align-items-center">
                            <iconify-icon icon="solar:arrow-right-outline" class="me-2"></iconify-icon>
                            Hizmet Bilgileri
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Hizmet Adı <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           placeholder="Hizmet Adı" value="{{ old('name') }}" required>
                                    <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                        <iconify-icon icon="solar:box-outline" class="text-secondary-light"></iconify-icon>
                                    </div>
                                </div>
                                @error('name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hizmet Kodu</label>
                                <div class="position-relative">
                                    <input type="text" name="service_code" class="form-control @error('service_code') is-invalid @enderror" 
                                           placeholder="Hizmet Kodu" value="{{ old('service_code') }}">
                                    <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                        <iconify-icon icon="solar:tag-outline" class="text-secondary-light"></iconify-icon>
                                    </div>
                                </div>
                                @error('service_code')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mt-3">
                                <label class="form-label">Kategori</label>
                                <div class="position-relative">
                                    <select name="category" class="form-control @error('category') is-invalid @enderror">
                                        <option value="">Seçiniz</option>
                                        <option value="Kargo" {{ old('category') == 'Kargo' ? 'selected' : '' }}>Kargo</option>
                                        <option value="Reklamasyon" {{ old('category') == 'Reklamasyon' ? 'selected' : '' }}>Reklamasyon</option>
                                        <option value="Dikiş" {{ old('category') == 'Dikiş' ? 'selected' : '' }}>Dikiş</option>
                                        <option value="Ütü" {{ old('category') == 'Ütü' ? 'selected' : '' }}>Ütü</option>
                                        <option value="Temizlik" {{ old('category') == 'Temizlik' ? 'selected' : '' }}>Temizlik</option>
                                        <option value="Bakım" {{ old('category') == 'Bakım' ? 'selected' : '' }}>Bakım</option>
                                        <option value="Danışmanlık" {{ old('category') == 'Danışmanlık' ? 'selected' : '' }}>Danışmanlık</option>
                                        <option value="Diğer" {{ old('category') == 'Diğer' ? 'selected' : '' }}>Diğer</option>
                                    </select>
                                </div>
                                @error('category')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Fiyat Bilgileri -->
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3 d-flex align-items-center">
                            <iconify-icon icon="solar:arrow-right-outline" class="me-2"></iconify-icon>
                            Fiyat Bilgileri
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Satış Fiyatı <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="number" name="sale_price" class="form-control @error('sale_price') is-invalid @enderror" 
                                           placeholder="Satış Fiyatı" step="0.01" min="0" value="{{ old('sale_price') }}" required>
                                    <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                        <iconify-icon icon="solar:dollar-outline" class="text-secondary-light"></iconify-icon>
                                    </div>
                                </div>
                                <small class="text-secondary-light">KDV Hariç</small>
                                @error('sale_price')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Para Birimi <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select name="currency" class="form-control @error('currency') is-invalid @enderror" required>
                                        <option value="TRY" {{ old('currency', 'TRY') == 'TRY' ? 'selected' : '' }}>₺</option>
                                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>$</option>
                                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>€</option>
                                    </select>
                                </div>
                                @error('currency')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mt-3">
                                <label class="form-label">KDV Oranı <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select name="vat_rate" class="form-control @error('vat_rate') is-invalid @enderror" required>
                                        <option value="0" {{ old('vat_rate') == '0' ? 'selected' : '' }}>0%</option>
                                        <option value="1" {{ old('vat_rate') == '1' ? 'selected' : '' }}>1%</option>
                                        <option value="8" {{ old('vat_rate') == '8' ? 'selected' : '' }}>8%</option>
                                        <option value="18" {{ old('vat_rate') == '18' ? 'selected' : '' }}>18%</option>
                                        <option value="20" {{ old('vat_rate', '20') == '20' ? 'selected' : '' }}>20%</option>
                                    </select>
                                </div>
                                @error('vat_rate')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Ayarlar -->
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3 d-flex align-items-center">
                            <iconify-icon icon="solar:arrow-right-outline" class="me-2"></iconify-icon>
                            Ayarlar
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Durum</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label">Aktif</label>
                                </div>
                                @error('is_active')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Açıklama -->
                    <div class="mb-4">
                        <label class="form-label">Açıklama</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="3" placeholder="Hizmet açıklaması">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Butonlar -->
                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('services.index') }}" class="btn btn-secondary">
                            <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                            Geri Dön
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <iconify-icon icon="solar:check-circle-outline" class="me-2"></iconify-icon>
                            Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
