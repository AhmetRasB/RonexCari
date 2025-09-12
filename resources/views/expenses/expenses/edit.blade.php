@extends('layout.layout')

@section('title', 'Masraf Düzenle')
@section('subTitle', 'Masraf Güncelle')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Masraf Düzenle</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('expenses.expenses.update', $expense) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row gy-3">
                        <div class="col-12">
                            <label class="form-label">Masraf Adı <span class="text-danger">*</span></label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:wallet-money-outline"></iconify-icon>
                                </span>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       placeholder="Masraf adı girin" value="{{ old('name', $expense->name) }}" required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tutar (₺) <span class="text-danger">*</span></label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:dollar-minimalistic-outline"></iconify-icon>
                                </span>
                                <input type="number" step="0.01" min="0" name="amount" class="form-control @error('amount') is-invalid @enderror" 
                                       placeholder="Tutar girin" value="{{ old('amount', $expense->amount) }}" required>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Masraf Tarihi <span class="text-danger">*</span></label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:calendar-outline"></iconify-icon>
                                </span>
                                <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" 
                                       value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                            </div>
                            @error('expense_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Açıklama</label>
                            <div class="icon-field">
                                <span class="icon">
                                    <iconify-icon icon="solar:notes-outline"></iconify-icon>
                                </span>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3" placeholder="Açıklama girin">{{ old('description', $expense->description) }}</textarea>
                            </div>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', $expense->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <iconify-icon icon="solar:check-circle-outline" class="me-2"></iconify-icon>
                                    Ödendi
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary-600">
                                <iconify-icon icon="solar:check-circle-outline" class="me-2"></iconify-icon>
                                Güncelle
                            </button>
                            <a href="{{ route('expenses.expenses.index') }}" class="btn btn-secondary ms-2">
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
