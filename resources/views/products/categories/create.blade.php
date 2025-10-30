@extends('layout.layout')

@section('title', 'Yeni Kategori')
@section('subTitle', 'Ürün Kategorisi Ekle')

@section('content')
<div class="row">
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Yeni Kategori (Hesap: {{ session('current_account_id') ? 'Seçili Hesap' : 'Tüm Hesaplar' }})</h6>
                <form method="POST" action="{{ route('products.categories.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Kategori Adı</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-check mb-3">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                        <a href="{{ route('products.categories.index') }}" class="btn btn-secondary">Geri Dön</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


