@extends('layout.layout')

@section('title', 'Yeni Marka')
@section('subTitle', 'Marka Oluştur')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Yeni Marka</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('products.brands.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Marka Adı</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                <label class="form-check-label" for="is_active">Aktif</label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Kaydet</button>
                <a href="{{ route('products.brands.index') }}" class="btn btn-secondary">İptal</a>
            </div>
        </form>
    </div>
</div>
@endsection


