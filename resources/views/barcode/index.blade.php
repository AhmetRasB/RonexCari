@extends('layout.layout')

@section('title', 'Barkod Bölümü')
@section('subTitle', 'Barkod / QR Yazdırma')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('barcode.preview') }}" target="_blank">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Sayfa Formatı</label>
                    <select name="layout" class="form-select">
                        <option value="a4-10">A4 - 10 etiket</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Ürünler ve Adetler</label>
                    <div id="items">
                        <div class="row g-2 align-items-center item-row mb-2">
                            <div class="col-md-6">
                                <select class="form-select" name="items[0][product_id]">
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} {{ $p->size ? ' - '.$p->size : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" type="number" min="1" value="10" name="items[0][quantity]" />
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-outline-primary w-100" onclick="addRow()">Satır Ekle</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-between">
                <a href="{{ route('barcode.test') }}" class="btn btn-outline-secondary" target="_blank">Test QR / Barkod</a>
                <button type="submit" class="btn btn-primary">Yazdırma Önizleme</button>
            </div>
        </form>
    </div>
</div>

<script>
let rowIndex = 1;
function addRow() {
    const wrapper = document.getElementById('items');
    const row = document.createElement('div');
    row.className = 'row g-2 align-items-center item-row mb-2';
    row.innerHTML = `
        <div class="col-md-6">
            <select class="form-select" name="items[${rowIndex}][product_id]">
                @foreach($products as $p)
                    <option value="{{ $p->id }}">{{ $p->name }} {{ $p->size ? ' - '.$p->size : '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input class="form-control" type="number" min="1" value="10" name="items[${rowIndex}][quantity]" />
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.item-row').remove()">Kaldır</button>
        </div>`;
    wrapper.appendChild(row);
    rowIndex++;
}
</script>
@endsection


