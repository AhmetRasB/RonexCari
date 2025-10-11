@extends('layout.layout')

@section('title', 'Barkod Bölümü')
@section('subTitle', 'Barkod / QR Yazdırma')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">Makineye Yazdır (QZ Tray) / Dışa Aktar</h6>
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tür</label>
                <select class="form-select" id="printType" onchange="syncItemOptions()">
                    <option value="product">Ürün</option>
                    <option value="series">Seri</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Öğe</label>
                <select class="form-select" id="printItem">
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" data-type="product">{{ $p->name }} {{ $p->size ? ' - '.$p->size : '' }}</option>
                    @endforeach
                    @foreach($series as $s)
                        <option value="{{ $s->id }}" data-type="series" style="display:none;">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Seri Modu</label>
                <select class="form-select" id="seriesMode">
                    <option value="outer">Dış Etiket</option>
                    <option value="sizes">Beden Etiketleri</option>
                    <option value="full">Dış + Bedenler (FULL)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Adet</label>
                <input type="number" id="printCount" class="form-control" value="1" min="1" />
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="button" class="btn btn-success w-100" onclick="printViaQz()">Makineye Yazdır</button>
                <button type="button" class="btn btn-outline-dark w-100" onclick="previewZpl()">Önizleme</button>
            </div>
        </div>
        <div class="mt-2 d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary" id="btnDownloadZpl" href="#" onclick="downloadZpl(event)">ZPL indir</a>
            <a class="btn btn-outline-secondary" id="btnDownloadCsv" href="#" onclick="downloadCsv(event)">CSV indir</a>
            <a class="btn btn-outline-secondary" id="btnDownloadBtxml" href="#" onclick="downloadBtxml(event)">BTXML indir</a>
            <a href="{{ route('barcode.test') }}" class="btn btn-outline-info" target="_blank">Test QR / Barkod</a>
        </div>
        <div class="alert alert-info mt-2 mb-0">
            <strong>Etiket Formatı:</strong>
            <ul class="mb-0 mt-2">
                <li><strong>QR Kod:</strong> Ürün/Seri detay sayfasına yönlendirir</li>
                <li><strong>Barkod:</strong> CODE128 formatında yazdırılır</li>
                <li><strong>Bilgiler:</strong> Ürün adı, renk, beden, stok, kategori</li>
                <li><strong>Seri Ürünler:</strong> Dış paket + Her renk x Her beden etiketleri</li>
                <li><strong>Karakter Dönüşümü:</strong> Türkçe karakterler İngilizce'ye çevrilir (ğ→g, ü→u, ş→s, vb.)</li>
            </ul>
        </div>
        <div class="alert alert-warning mt-2 mb-0">
            <strong>Yazdırma:</strong> QZ Tray kurulu olmalı. USB CAB EOS4 yazıcısını QZ Tray'den seçip tarayıcıya izin verin.<br>
            <strong>USB Bellek:</strong> ZPL dosyasını indirip USB belleğe atabilirsiniz. Yazıcı direkt USB'den okuyabilir.
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">(Eski) PDF Önizleme</h6>
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
                        <strong>Bilgi:</strong>
                        <ul class="mb-0 mt-1">
                            <li><strong>Ürün:</strong> Renk varyantları varsa her renk için ayrı etiket oluşturulur</li>
                            <li><strong>Seri:</strong> 1 dış paket etiketi + Her renk x Her beden kombinasyonu için etiket</li>
                            <li>Tüm metinler İngilizce karakterlere çevrilir (Türkçe karakter desteği yok)</li>
                        </ul>
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

// === Printing & Export helpers ===
function currentParams() {
    const type = document.getElementById('printType').value;
    const itemSel = document.getElementById('printItem');
    const id = itemSel.value;
    const mode = document.getElementById('seriesMode').value;
    const count = parseInt(document.getElementById('printCount').value || '1', 10) || 1;
    return { type, id, mode, count };
}

function syncItemOptions(){
    const type = document.getElementById('printType').value;
    const select = document.getElementById('printItem');
    const options = select.querySelectorAll('option');
    options.forEach(o => { o.style.display = (o.dataset.type === type) ? '' : 'none'; });
    const first = select.querySelector(`option[data-type="${type}"]`);
    if (first) select.value = first.value;
    document.getElementById('seriesMode').disabled = (type !== 'series');
}
syncItemOptions();

function buildUrl(base){
    const { type, id, mode, count } = currentParams();
    const u = new URL(base, window.location.origin);
    u.searchParams.set('type', type);
    u.searchParams.set('id', id);
    if (type === 'series') u.searchParams.set('mode', mode);
    u.searchParams.set('count', count);
    return u.toString();
}

async function printViaQz(){
    try {
        if (!window.qz) { alert('QZ Tray bulunamadı. Lütfen QZ Tray kurun ve tarayıcıya izin verin.'); return; }
        const url = buildUrl('{{ route('print.labels.zpl') }}');
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        const zpl = await res.text();
        const cfg = qz.configs.create(null); // default yazıcı (QZ üzerinden seçili)
        await qz.print(cfg, [{ type: 'raw', format: 'plain', data: zpl }]);
    } catch (e) {
        console.error(e);
        alert('Yazdırma başarısız: ' + (e.message || e));
    }
}

function downloadZpl(e){ e.preventDefault(); window.open(buildUrl('{{ route('print.labels.zpl') }}'), '_blank'); }
function downloadCsv(e){ e.preventDefault(); window.open(buildUrl('{{ route('print.labels.csv') }}'), '_blank'); }
function downloadBtxml(e){ e.preventDefault(); window.open(buildUrl('{{ route('print.labels.btxml') }}'), '_blank'); }

// -- ZPL Preview (Labelary)
async function previewZpl(){
    try {
        const url = buildUrl('{{ route('print.labels.preview') }}');
        const imgRes = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        if (!imgRes.ok) throw new Error('Labelary render failed');
        const blob = await imgRes.blob();
        const urlObj = URL.createObjectURL(blob);
        const w = window.open('about:blank');
        w.document.write('<title>Etiket Önizleme</title>');
        w.document.write('<img style="max-width:100%;image-rendering: pixelated;" src="'+urlObj+'" />');
        w.document.close();
    } catch (e) {
        console.error(e);
        alert('Önizleme oluşturulamadı: ' + (e.message || e));
    }
}
</script>
@endsection



