@extends('layout.layout')

@section('title', 'Barkod Bölümü')
@section('subTitle', 'Barkod / QR Yazdırma')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">Makineye Yazdır (QZ Tray) / Dışa Aktar</h6>
        <div id="printItems">
            <div class="row g-2 align-items-end mb-2 print-item-row">
                <div class="col-md-2">
                    <label class="form-label">Tür</label>
                    <select class="form-select item-type" onchange="syncItemOptionsForRow(this)">
                        <option value="product">Ürün</option>
                        <option value="series">Seri</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Öğe</label>
                    <select class="form-select item-select">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-type="product">{{ $p->name }} {{ $p->size ? ' - '.$p->size : '' }}</option>
                        @endforeach
                        @foreach($series as $s)
                            <option value="{{ $s->id }}" data-type="series" style="display:none;">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Seri Modu</label>
                    <select class="form-select item-mode">
                        <option value="outer">Dış Etiket</option>
                        <option value="sizes">Beden Etiketleri</option>
                        <option value="full">Dış + Bedenler (FULL)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Adet</label>
                    <input type="number" class="form-control item-count" value="1" min="1" />
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-outline-primary w-100" onclick="addPrintRow()">Satır Ekle</button>
                </div>
            </div>
        </div>
        <div class="mt-3 d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-success" onclick="printAllViaQz()">Hepsini Makineye Yazdır</button>
            <button type="button" class="btn btn-outline-dark" onclick="previewAllZpl()">Önizleme</button>
            <a class="btn btn-outline-primary" href="#" onclick="downloadAllZpl(event)">ZPL Dosyası İndir</a>
            <a class="btn btn-outline-secondary" href="#" onclick="downloadAllCsv(event)">CSV indir</a>
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

<script>
// === Multi-row printing ===
function addPrintRow() {
    const wrapper = document.getElementById('printItems');
    const row = document.createElement('div');
    row.className = 'row g-2 align-items-end mb-2 print-item-row';
    row.innerHTML = `
        <div class="col-md-2">
            <select class="form-select item-type" onchange="syncItemOptionsForRow(this)">
                <option value="product">Ürün</option>
                <option value="series">Seri</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select item-select">
                @foreach($products as $p)
                    <option value="{{ $p->id }}" data-type="product">{{ $p->name }} {{ $p->size ? ' - '.$p->size : '' }}</option>
                @endforeach
                @foreach($series as $s)
                    <option value="{{ $s->id }}" data-type="series" style="display:none;">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select item-mode">
                <option value="outer">Dış Etiket</option>
                <option value="sizes">Beden Etiketleri</option>
                <option value="full">Dış + Bedenler (FULL)</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" class="form-control item-count" value="1" min="1" />
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.print-item-row').remove()">Kaldır</button>
        </div>`;
    wrapper.appendChild(row);
    syncItemOptionsForRow(row.querySelector('.item-type'));
}

function syncItemOptionsForRow(typeSelect) {
    const row = typeSelect.closest('.print-item-row');
    const type = typeSelect.value;
    const select = row.querySelector('.item-select');
    const options = select.querySelectorAll('option');
    options.forEach(o => { o.style.display = (o.dataset.type === type) ? '' : 'none'; });
    const first = select.querySelector(`option[data-type="${type}"]`);
    if (first) select.value = first.value;
    row.querySelector('.item-mode').disabled = (type !== 'series');
}

// Init first row
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.item-type').forEach(syncItemOptionsForRow);
});

function getAllPrintItems() {
    const rows = document.querySelectorAll('.print-item-row');
    const items = [];
    rows.forEach(row => {
        const type = row.querySelector('.item-type').value;
        const id = row.querySelector('.item-select').value;
        const mode = row.querySelector('.item-mode').value;
        const count = parseInt(row.querySelector('.item-count').value || '1', 10) || 1;
        items.push({ type, id, mode, count });
    });
    return items;
}

async function printAllViaQz(){
    try {
        if (!window.qz) { alert('QZ Tray bulunamadı. Lütfen QZ Tray kurun ve tarayıcıya izin verin.'); return; }
        const items = getAllPrintItems();
        let allZpl = '';
        
        for (const item of items) {
            const u = new URL('{{ route('print.labels.zpl') }}', window.location.origin);
            u.searchParams.set('type', item.type);
            u.searchParams.set('id', item.id);
            if (item.type === 'series') u.searchParams.set('mode', item.mode);
            u.searchParams.set('count', item.count);
            
            const res = await fetch(u.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            const zpl = await res.text();
            allZpl += zpl;
        }
        
        const cfg = qz.configs.create(null);
        await qz.print(cfg, [{ type: 'raw', format: 'plain', data: allZpl }]);
        alert('Yazdırma başarılı!');
    } catch (e) {
        console.error(e);
        alert('Yazdırma başarısız: ' + (e.message || e));
    }
}

async function downloadAllZpl(e){
    e.preventDefault();
    try {
        const items = getAllPrintItems();
        let allZpl = '';
        
        for (const item of items) {
            const u = new URL('{{ route('print.labels.zpl') }}', window.location.origin);
            u.searchParams.set('type', item.type);
            u.searchParams.set('id', item.id);
            if (item.type === 'series') u.searchParams.set('mode', item.mode);
            u.searchParams.set('count', item.count);
            
            const res = await fetch(u.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            const zpl = await res.text();
            allZpl += zpl;
        }
        
        const blob = new Blob([allZpl], { type: 'text/plain' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'etiketler.zpl';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (e) {
        console.error(e);
        alert('İndirme başarısız: ' + (e.message || e));
    }
}

async function downloadAllCsv(e){
    e.preventDefault();
    const items = getAllPrintItems();
    if (items.length === 1) {
        const item = items[0];
        const u = new URL('{{ route('print.labels.csv') }}', window.location.origin);
        u.searchParams.set('type', item.type);
        u.searchParams.set('id', item.id);
        if (item.type === 'series') u.searchParams.set('mode', item.mode);
        window.open(u.toString(), '_blank');
    } else {
        alert('CSV indirme sadece tek öğe için desteklenmektedir.');
    }
}

async function previewAllZpl(){
    try {
        const items = getAllPrintItems();
        if (items.length === 0) return;
        
        const item = items[0]; // İlk öğeyi önizle
        const u = new URL('{{ route('print.labels.preview') }}', window.location.origin);
        u.searchParams.set('type', item.type);
        u.searchParams.set('id', item.id);
        if (item.type === 'series') u.searchParams.set('mode', item.mode);
        
        const imgRes = await fetch(u.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
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



