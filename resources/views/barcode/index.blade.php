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
                    <label class="form-label">Ürünler/Seriler ve Adetler</label>
                    <div id="items">
                        <div class="row g-2 align-items-center item-row mb-2">
                            <div class="col-md-4">
                                <select class="form-select" name="items[0][type]" onchange="updateOptions(this, 0)">
                                    <option value="product">Ürün</option>
                                    <option value="series">Seri</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="items[0][id]" id="select-0">
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-type="product">{{ $p->name }} {{ $p->size ? ' - '.$p->size : '' }}</option>
                                    @endforeach
                                    @foreach($series as $s)
                                        <option value="{{ $s->id }}" data-type="series" style="display:none;">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input class="form-control" type="number" min="1" value="1" name="items[0][quantity]" placeholder="Paket Adedi" />
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-primary w-100" onclick="addRow()">Satır Ekle</button>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info mt-2">
                        <i class="ri-information-line me-2"></i>
                        <strong>Bilgi:</strong> Seri seçilirse, her paket için 1 dış paket etiketi + tüm beden etiketleri otomatik oluşturulur.
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

function updateOptions(typeSelect, index) {
    const selectId = `select-${index}`;
    const select = document.getElementById(selectId);
    const selectedType = typeSelect.value;
    
    // Show/hide options based on type
    const options = select.querySelectorAll('option');
    options.forEach(option => {
        if (option.dataset.type === selectedType) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Select first visible option
    const firstVisible = select.querySelector(`option[data-type="${selectedType}"]`);
    if (firstVisible) {
        select.value = firstVisible.value;
    }
}

function addRow() {
    const wrapper = document.getElementById('items');
    const row = document.createElement('div');
    row.className = 'row g-2 align-items-center item-row mb-2';
    row.innerHTML = `
        <div class="col-md-4">
            <select class="form-select" name="items[${rowIndex}][type]" onchange="updateOptions(this, ${rowIndex})">
                <option value="product">Ürün</option>
                <option value="series">Seri</option>
            </select>
        </div>
        <div class="col-md-4">
            <select class="form-select" name="items[${rowIndex}][id]" id="select-${rowIndex}">
                @foreach($products as $p)
                    <option value="{{ $p->id }}" data-type="product">{{ $p->name }} {{ $p->size ? ' - '.$p->size : '' }}</option>
                @endforeach
                @foreach($series as $s)
                    <option value="{{ $s->id }}" data-type="series" style="display:none;">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input class="form-control" type="number" min="1" value="1" name="items[${rowIndex}][quantity]" placeholder="Paket Adedi" />
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.item-row').remove()">Kaldır</button>
        </div>`;
    wrapper.appendChild(row);
    rowIndex++;
}
</script>
@endsection



