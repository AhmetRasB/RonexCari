@extends('layout.layout')

@section('title', 'Müşteri Düzenle')
@section('subTitle', 'Müşteri Güncelle')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Müşteri Düzenle</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('sales.customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row gy-3">
                        <div class="col-12">
                            <label class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="f7:person"></iconify-icon>
                                </span>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       placeholder="Ad Soyad girin" value="{{ old('name', $customer->name) }}" required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Şirket Adı</label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:buildings-outline"></iconify-icon>
                                </span>
                                <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" 
                                       placeholder="Şirket adı girin" value="{{ old('company_name', $customer->company_name) }}">
                            </div>
                            @error('company_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">E-posta</label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="mage:email"></iconify-icon>
                                </span>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       placeholder="E-posta adresi girin" value="{{ old('email', $customer->email) }}">
                            </div>
                            @error('email')
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
                                       placeholder="Telefon numarası girin" value="{{ old('phone', $customer->phone) }}">
                            </div>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Vergi Numarası</label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:document-outline"></iconify-icon>
                                </span>
                                <input type="text" name="tax_number" class="form-control @error('tax_number') is-invalid @enderror" 
                                       placeholder="Vergi numarası girin" value="{{ old('tax_number', $customer->tax_number) }}">
                            </div>
                            @error('tax_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">İletişim Kişisi</label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:user-outline"></iconify-icon>
                                </span>
                                <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" 
                                       placeholder="İletişim kişisi girin" value="{{ old('contact_person', $customer->contact_person) }}">
                            </div>
                            @error('contact_person')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adres</label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:map-point-outline"></iconify-icon>
                                </span>
                                <textarea name="address" class="form-control @error('address') is-invalid @enderror" 
                                          rows="3" placeholder="Adres girin">{{ old('address', $customer->address) }}</textarea>
                            </div>
                            @error('address')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notlar</label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:notes-outline"></iconify-icon>
                                </span>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                          rows="3" placeholder="Notlar girin">{{ old('notes', $customer->notes) }}</textarea>
                            </div>
                            @error('notes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <iconify-icon icon="solar:check-circle-outline" class="me-2"></iconify-icon>
                                    Aktif
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary-600">
                                <iconify-icon icon="solar:check-circle-outline" class="me-2"></iconify-icon>
                                Güncelle
                            </button>
                            <a href="{{ route('sales.customers.index') }}" class="btn btn-secondary ms-2">
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
