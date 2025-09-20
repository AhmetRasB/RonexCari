@extends('layout.layout')

@php
    $title='Hesap Yönetimi';
    $subTitle = 'Hesap bilgilerini düzenleyin';
@endphp

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Hesap Yönetimi</h5>
                <a href="{{ route('account.select') }}" class="btn btn-outline-primary">
                    <iconify-icon icon="heroicons:arrow-left" class="me-2"></iconify-icon>
                    Hesap Seçimine Dön
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row">
                    @foreach($accounts as $account)
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header {{ $account->code === 'ronex1' ? 'bg-primary-50' : 'bg-success-50' }}">
                                    <h6 class="card-title mb-0 {{ $account->code === 'ronex1' ? 'text-primary-600' : 'text-success-600' }}">
                                        <iconify-icon icon="heroicons:building-office-2" class="me-2"></iconify-icon>
                                        {{ $account->name }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('account.update', $account) }}">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Hesap Adı</label>
                                                <input type="text" class="form-control" name="name" value="{{ $account->name }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Şirket Adı</label>
                                                <input type="text" class="form-control" name="company_name" value="{{ $account->company_name }}">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Açıklama</label>
                                            <textarea class="form-control" name="description" rows="2">{{ $account->description }}</textarea>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Adres</label>
                                            <textarea class="form-control" name="address" rows="2">{{ $account->address }}</textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Şehir</label>
                                                <input type="text" class="form-control" name="city" value="{{ $account->city }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">İlçe</label>
                                                <input type="text" class="form-control" name="district" value="{{ $account->district }}">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Posta Kodu</label>
                                                <input type="text" class="form-control" name="postal_code" value="{{ $account->postal_code }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Telefon</label>
                                                <input type="text" class="form-control" name="phone" value="{{ $account->phone }}">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">E-posta</label>
                                                <input type="email" class="form-control" name="email" value="{{ $account->email }}">
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <span class="badge {{ $account->is_active ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $account->is_active ? 'Aktif' : 'Pasif' }}
                                                </span>
                                            </div>
                                            <button type="submit" class="btn {{ $account->code === 'ronex1' ? 'btn-primary' : 'btn-success' }}">
                                                <iconify-icon icon="heroicons:check" class="me-2"></iconify-icon>
                                                Güncelle
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
