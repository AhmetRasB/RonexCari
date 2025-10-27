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
            <a class="btn btn-outline-primary" href="#" onclick="downloadAllJScript(event)">JScript Dosyası İndir</a>
            <a class="btn btn-outline-secondary" href="#" onclick="downloadAllCsv(event)">CSV indir</a>
            <button type="button" class="btn btn-outline-warning" onclick="showBartenderFields()">Bartender Data Fields</button>
            <a href="{{ route('barcode.test') }}" class="btn btn-outline-info" target="_blank">Test QR / Barkod</a>
        </div>
        <div class="alert alert-info mt-2 mb-0">
            <strong>Etiket Formatı:</strong>
            <ul class="mb-0 mt-2">
                <li><strong>QR Kod:</strong> Ürün/Seri detay sayfasına yönlendirir</li>
                <li><strong>Barkod:</strong> CODE128 formatında yazdırılır</li>
                <li><strong>Bilgiler:</strong> Ürün adı, beden, stok, kategori (renkler kaldırıldı)</li>
                <li><strong>Seri Ürünler:</strong> Dış paket + Her beden etiketleri</li>
                <li><strong>Bartender Veri Kaynağı:</strong> CSV formatında, renkler kaldırılmış, toplam stok</li>
                <li><strong>Karakter Dönüşümü:</strong> Türkçe karakterler İngilizce'ye çevrilir (ğ→g, ü→u, ş→s, vb.)</li>
            </ul>
        </div>
        <div class="alert alert-warning mt-2 mb-0">
            <strong>Yazdırma:</strong> QZ Tray kurulu olmalı. USB CAB EOS4 yazıcısını QZ Tray'den seçip tarayıcıya izin verin.<br>
            <strong>USB Bellek:</strong> JScript (CAB) komut dosyasını indirip USB belleğe atabilirsiniz. Yazıcı direkt USB'den okuyabilir.
        </div>
        <div class="alert alert-success mt-2 mb-0">
            <strong>Bartender Entegrasyonu:</strong><br>
            <strong>1. Veri Kaynağı:</strong> "Bartender Data Fields" butonuna tıklayarak alanları görün<br>
            <strong>2. Bartender'da:</strong> Yeni veri kaynağı oluşturun ve CSV dosyasını seçin<br>
            <strong>3. Alan Eşleştirme:</strong> type, category, name, size, barcode, stock alanlarını eşleştirin<br>
            <strong>4. Tasarım:</strong> Etiket tasarımınızda bu alanları kullanın<br>
            <strong>5. Yazdırma:</strong> Bartender'dan direkt yazdırın veya BTXML ile entegre edin
        </div>
    </div>
</div>

<!-- Bartender Data Fields Modal -->
<div class="modal fade" id="bartenderFieldsModal" tabindex="-1" aria-labelledby="bartenderFieldsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bartenderFieldsModalLabel">
                    <iconify-icon icon="solar:database-outline" class="me-2"></iconify-icon>
                    Bartender Data Fields
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <iconify-icon icon="solar:info-circle-outline" class="me-2"></iconify-icon>
                    <strong>Bartender Entegrasyonu:</strong> Aşağıdaki alanları kopyalayıp Bartender'da veri kaynağı olarak kullanabilirsiniz.
                </div>
                
                <div id="bartenderFieldsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                        <p class="mt-2 text-muted">Veri alanları yükleniyor...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <button type="button" class="btn btn-primary" onclick="downloadBartenderCsv()">
                    <iconify-icon icon="solar:download-outline" class="me-2"></iconify-icon>
                    CSV İndir
                </button>
            </div>
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

async function downloadAllJScript(e){
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
        link.download = 'etiketler.txt';
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

// Global variable to store current item data
let currentBartenderItem = null;

async function showBartenderFields(){
    const items = getAllPrintItems();
    if (items.length === 0) {
        alert('Lütfen en az bir öğe seçin.');
        return;
    }
    
    if (items.length > 1) {
        alert('Bartender Data Fields sadece tek öğe için desteklenmektedir.');
        return;
    }
    
    currentBartenderItem = items[0];
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('bartenderFieldsModal'));
    modal.show();
    
    // Load data fields
    await loadBartenderFields();
}

async function loadBartenderFields(){
    if (!currentBartenderItem) return;
    
    try {
        const u = new URL('{{ route('print.labels.csv') }}', window.location.origin);
        u.searchParams.set('type', currentBartenderItem.type);
        u.searchParams.set('id', currentBartenderItem.id);
        if (currentBartenderItem.type === 'series') u.searchParams.set('mode', currentBartenderItem.mode);
        
        const response = await fetch(u.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        const csvData = await response.text();
        
        // Parse CSV data
        const lines = csvData.split('\n');
        const headers = lines[0].split(',');
        const data = lines[1] ? lines[1].split(',') : [];
        
        // Create fields display
        const fieldsHtml = headers.map((header, index) => {
            const value = data[index] || '';
            return `
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-primary">${header}</label>
                    </div>
                    <div class="col-md-7">
                        <div class="input-group">
                            <input type="text" class="form-control field-value" value="${value}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('${value}')">
                                <iconify-icon icon="solar:copy-outline"></iconify-icon>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-outline-primary w-100" onclick="copyToClipboard('${value}')">
                            Kopyala
                        </button>
                    </div>
                </div>
            `;
        }).join('');
        
        document.getElementById('bartenderFieldsContent').innerHTML = `
            <div class="mb-3">
                <h6 class="fw-semibold text-success">
                    <iconify-icon icon="solar:database-outline" class="me-2"></iconify-icon>
                    ${currentBartenderItem.type === 'product' ? 'Ürün' : 'Seri'} Veri Alanları
                </h6>
                <p class="text-muted mb-3">Aşağıdaki alanları Bartender'da veri kaynağı olarak kullanabilirsiniz.</p>
            </div>
            ${fieldsHtml}
            <div class="alert alert-warning mt-3">
                <iconify-icon icon="solar:warning-outline" class="me-2"></iconify-icon>
                <strong>Bartender'da Kullanım:</strong><br>
                1. Yeni veri kaynağı oluşturun<br>
                2. CSV dosyasını seçin veya alanları manuel olarak kopyalayın<br>
                3. Alanları eşleştirin: ${headers.join(', ')}<br>
                4. Etiket tasarımınızda bu alanları kullanın
            </div>
        `;
        
    } catch (error) {
        console.error('Error loading Bartender fields:', error);
        document.getElementById('bartenderFieldsContent').innerHTML = `
            <div class="alert alert-danger">
                <iconify-icon icon="solar:danger-triangle-outline" class="me-2"></iconify-icon>
                Veri alanları yüklenirken hata oluştu: ${error.message}
            </div>
        `;
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show success feedback
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<iconify-icon icon="solar:check-circle-outline"></iconify-icon> Kopyalandı!';
        btn.classList.remove('btn-outline-secondary', 'btn-outline-primary');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    }).catch(err => {
        console.error('Copy failed:', err);
        alert('Kopyalama başarısız: ' + err.message);
    });
}

async function downloadBartenderCsv(){
    if (!currentBartenderItem) return;
    
    try {
        const u = new URL('{{ route('print.labels.csv') }}', window.location.origin);
        u.searchParams.set('type', currentBartenderItem.type);
        u.searchParams.set('id', currentBartenderItem.id);
        if (currentBartenderItem.type === 'series') u.searchParams.set('mode', currentBartenderItem.mode);
        
        const response = await fetch(u.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        const csvData = await response.text();
        
        const fileName = `bartender_${currentBartenderItem.type}_${currentBartenderItem.id}_${currentBartenderItem.mode || 'data'}.csv`;
        
        const blob = new Blob([csvData], { type: 'text/csv; charset=UTF-8' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        alert('Bartender CSV dosyası başarıyla indirildi!\n\nDosya adı: ' + fileName);
        
    } catch (error) {
        console.error('CSV download failed:', error);
        alert('CSV indirme başarısız: ' + error.message);
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



