@extends('layout.layout')

@section('title', 'Masraf Detayı')
@section('subTitle', 'Masraf Bilgileri')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Masraf Detayı</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('expenses.expenses.edit', $expense) }}" class="btn btn-primary">
                        <iconify-icon icon="lucide:edit" class="me-2"></iconify-icon>
                        Düzenle
                    </a>
                    <a href="{{ route('expenses.expenses.index') }}" class="btn btn-secondary">
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
                                <iconify-icon icon="solar:wallet-money-outline" class="me-2"></iconify-icon>
                                Masraf Bilgileri
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Masraf Adı:</div>
                                    <div class="col-8">{{ $expense->name }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Tutar:</div>
                                    <div class="col-8">
                                        <span class="text-primary-600 fw-semibold fs-5">{{ number_format($expense->amount, 2) }} ₺</span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Tarih:</div>
                                    <div class="col-8">{{ $expense->expense_date->format('d.m.Y') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Ödeme Durumu:</div>
                                    <div class="col-8">
                                        <span class="bg-{{ $expense->is_active ? 'success' : 'warning' }}-focus text-{{ $expense->is_active ? 'success' : 'warning' }}-main px-24 py-4 rounded-pill fw-medium text-sm">
                                            {{ $expense->is_active ? 'Ödendi' : 'Ödenmedi' }}
                                        </span>
                                    </div>
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
                                    <div class="col-8">{{ $expense->created_at->format('d.m.Y H:i') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 fw-medium">Güncellenme:</div>
                                    <div class="col-8">{{ $expense->updated_at->format('d.m.Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($expense->description)
                <div class="mb-4">
                    <h6 class="fw-semibold text-primary-600 mb-2">
                        <iconify-icon icon="solar:notes-outline" class="me-2"></iconify-icon>
                        Açıklama
                    </h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $expense->description }}</p>
                    </div>
                </div>
                @endif

                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('expenses.expenses.edit', $expense) }}" class="btn btn-primary">
                        <iconify-icon icon="lucide:edit" class="me-2"></iconify-icon>
                        Düzenle
                    </a>
                    <form action="{{ route('expenses.expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu masrafı silmek istediğinizden emin misiniz?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <iconify-icon icon="mingcute:delete-2-line" class="me-2"></iconify-icon>
                            Sil
                        </button>
                    </form>
                    <a href="{{ route('expenses.expenses.index') }}" class="btn btn-secondary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                        Geri Dön
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
