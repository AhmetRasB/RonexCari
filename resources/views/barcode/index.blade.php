@extends('layout.layout')

@section('title', 'Barkod Bölümü')
@section('subTitle', 'Barkod / QR Yazdırma')

@section('content')
<!-- QZ Tray Script -->
<script src="https://cdn.jsdelivr.net/npm/qz-tray@3.0.0/dist/qz-tray.js"></script>
<script>
    // QZ Tray configuration
    qz.security.setCertificatePromise(function(resolve, reject) {
        // For development, you can disable certificate validation
        // In production, you should use proper certificates
        resolve();
    });
</script>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">Makineye Yazdır (QZ Tray) / Dışa Aktar</h6>
        <div id="printItems">
            <div class="row g-2 align-items-end mb-2 print-item-row">
                <div class="col-md-2">
                    <label class="form-label">Tür</label>
                    <select class="form-select item-type" onchange="syncItemOptionsForRow(this)">
                        <option value="series" selected>Seri</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Öğe</label>
                    <select class="form-select item-select">
                        @foreach($series as $s)
                            <option value="{{ $s->id }}" data-type="series">{{ $s->name }}</option>
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

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">ZPL'den PDF'e Dönüştürme ve Yazdırma Adımları</h6>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li class="mb-2">Önce üstteki <strong>ZPL indir</strong> butonuna tıklayıp etiketi <code>.zpl</code> olarak indirin.</li>
                    <li class="mb-2">Şu adrese gidin: <a href="https://zplpdf.com/en/convert-zpl-to-pdf" target="_blank" rel="noopener">zplpdf.com - ZPL to PDF</a></li>
                    <li class="mb-2">Sayfada <strong>3×6 Production</strong> boyutunu seçin ve <strong>PDF</strong>'i indirin.</li>
                    <li class="mb-2">Bilgisayarınızdaki <strong>Ekran Alıntısı Aracı</strong> ile gerekli alanın ekran görüntüsünü alın (gerekliyse kırpın).</li>
                    <li class="mb-2"><strong>Bartender</strong>'ı açıp görüntüyü/etiketi tasarımınıza ekleyin.</li>
                    <li class="mb-2">Hazır! Yazdırmaya başlayabilirsiniz.</li>
                </ol>
            </div>
        </div>
        <div class="mt-3 d-flex flex-wrap gap-2">
            
            <a class="btn btn-outline-dark" href="#" onclick="downloadAllZpl(event)" id="zplBtn">
                <span class="btn-text">ZPL indir</span>
                <span class="btn-loading" style="display: none;">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    İndiriliyor...
                </span>
            </a>
            
            
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
// === Helper functions ===
function showLoading(buttonId) {
    const btn = document.getElementById(buttonId);
    if (btn) {
        btn.querySelector('.btn-text').style.display = 'none';
        btn.querySelector('.btn-loading').style.display = 'inline-block';
        btn.disabled = true;
    }
}

function hideLoading(buttonId) {
    const btn = document.getElementById(buttonId);
    if (btn) {
        btn.querySelector('.btn-text').style.display = 'inline-block';
        btn.querySelector('.btn-loading').style.display = 'none';
        btn.disabled = false;
    }
}

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
    // Yalnızca seri destekleniyor; tüm seçenekler zaten seridir
    options.forEach(o => { o.style.display = ''; });
    row.querySelector('.item-mode').disabled = false;
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
        showLoading('printBtn');
        
        // Check if QZ Tray is available
        if (typeof qz === 'undefined') {
            alert('QZ Tray bulunamadı. Lütfen QZ Tray kurun ve tarayıcıya izin verin.\n\nQZ Tray\'i şu adresten indirebilirsiniz: https://qz.io/download/');
            return;
        }
        
        const items = getAllPrintItems();
        if (items.length === 0) {
            alert('Yazdırılacak öğe bulunamadı!');
            return;
        }
        
        let allZpl = '';
        
        for (const item of items) {
            const u = new URL('{{ route('print.labels.zpl') }}', window.location.origin);
            u.searchParams.set('type', item.type);
            u.searchParams.set('id', item.id);
            if (item.type === 'series') u.searchParams.set('mode', item.mode);
            u.searchParams.set('count', item.count);
            
            const res = await fetch(u.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }
            const zpl = await res.text();
            allZpl += zpl;
        }
        
        // Initialize QZ Tray if not already done
        if (!qz.websocket.isActive()) {
            await qz.websocket.connect();
        }
        
        const cfg = qz.configs.create(null);
        await qz.print(cfg, [{ type: 'raw', format: 'plain', data: allZpl }]);
        alert('Yazdırma başarılı!');
    } catch (e) {
        console.error('Print error:', e);
        alert('Yazdırma başarısız: ' + (e.message || e));
    } finally {
        hideLoading('printBtn');
    }
}

async function downloadAllJScript(e){
    e.preventDefault();
    try {
        showLoading('jscriptBtn');
        
        const items = getAllPrintItems();
        if (items.length === 0) {
            alert('İndirilecek öğe bulunamadı!');
            return;
        }
        
        let allZpl = '';
        
        for (const item of items) {
            const u = new URL('{{ route('print.labels.zpl') }}', window.location.origin);
            u.searchParams.set('type', item.type);
            u.searchParams.set('id', item.id);
            if (item.type === 'series') u.searchParams.set('mode', item.mode);
            u.searchParams.set('count', item.count);
            
            const res = await fetch(u.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }
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
        
        // Clean up the URL object
        URL.revokeObjectURL(link.href);
        
    } catch (e) {
        console.error('JScript download error:', e);
        alert('İndirme başarısız: ' + (e.message || e));
    } finally {
        hideLoading('jscriptBtn');
    }
}

async function downloadAllCsv(e){
    e.preventDefault();
    try {
        showLoading('csvBtn');
        
        const items = getAllPrintItems();
        if (items.length === 0) {
            alert('İndirilecek öğe bulunamadı!');
            return;
        }
        
        if (items.length === 1) {
            const item = items[0];
            const u = new URL('{{ route('print.labels.csv') }}', window.location.origin);
            u.searchParams.set('type', item.type);
            u.searchParams.set('id', item.id);
            if (item.type === 'series') u.searchParams.set('mode', item.mode);
            
            // Test the URL first
            const testRes = await fetch(u.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            if (!testRes.ok) {
                throw new Error(`HTTP ${testRes.status}: ${testRes.statusText}`);
            }
            
            window.open(u.toString(), '_blank');
        } else {
            alert('CSV indirme sadece tek öğe için desteklenmektedir.');
        }
    } catch (error) {
        console.error('CSV download error:', error);
        alert('CSV indirme başarısız: ' + error.message);
    } finally {
        hideLoading('csvBtn');
    }
}

async function downloadAllZpl(e){
    e.preventDefault();
    try {
        showLoading('zplBtn');
        const items = getAllPrintItems();
        if (items.length === 0) {
            alert('İndirilecek öğe bulunamadı!');
            return;
        }

        let allZpl = '';
        for (const item of items) {
            const u = new URL('{{ route('print.labels.zpl') }}', window.location.origin);
            u.searchParams.set('type', item.type);
            u.searchParams.set('id', item.id);
            if (item.type === 'series') u.searchParams.set('mode', item.mode);
            u.searchParams.set('count', item.count);
            const res = await fetch(u.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            allZpl += await res.text();
        }

        const blob = new Blob([allZpl], { type: 'text/plain' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'etiketler.zpl';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
    } catch (e) {
        console.error('ZPL download error:', e);
        alert('ZPL indirme başarısız: ' + (e.message || e));
    } finally {
        hideLoading('zplBtn');
    }
}

// Global variable to store current item data
let currentBartenderItem = null;

async function showBartenderFields(){
    try {
        showLoading('bartenderBtn');
        
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
    } finally {
        hideLoading('bartenderBtn');
    }
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

// PDF Export fonksiyonu
async function exportToPDF() {
    try {
        showLoading('pdfBtn');
        
        const items = getAllPrintItems();
        if (items.length === 0) {
            alert('Yazdırılacak öğe bulunamadı!');
            return;
        }
        
        // PDF oluşturmak için backend'e istek gönder
        const response = await fetch('{{ route("print.labels.pdf") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ items: items })
        });
        
        if (!response.ok) {
            throw new Error('PDF oluşturma başarısız');
        }
        
        const blob = await response.blob();
        const url = URL.createObjectURL(blob);
        
        // PDF'i indir
        const link = document.createElement('a');
        link.href = url;
        link.download = `barkod_etiketleri_${new Date().toISOString().split('T')[0]}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        URL.revokeObjectURL(url);
        
        alert('PDF başarıyla oluşturuldu ve indirildi!');
        
    } catch (error) {
        console.error('PDF export failed:', error);
        alert('PDF oluşturma başarısız: ' + error.message);
    } finally {
        hideLoading('pdfBtn');
    }
}

async function previewAllZpl(){
    try {
        showLoading('previewBtn');
        
        const items = getAllPrintItems();
        if (items.length === 0) {
            alert('Önizlenecek öğe bulunamadı!');
            return;
        }
        
        const item = items[0]; // İlk öğeyi önizle
        const u = new URL('{{ route('print.labels.preview') }}', window.location.origin);
        u.searchParams.set('type', item.type);
        u.searchParams.set('id', item.id);
        if (item.type === 'series') u.searchParams.set('mode', item.mode);
        
        const imgRes = await fetch(u.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        if (!imgRes.ok) {
            throw new Error(`HTTP ${imgRes.status}: ${imgRes.statusText}`);
        }
        
        const blob = await imgRes.blob();
        const urlObj = URL.createObjectURL(blob);
        const w = window.open('about:blank');
        w.document.write('<title>Etiket Önizleme</title>');
        w.document.write('<img style="max-width:100%;image-rendering: pixelated;" src="'+urlObj+'" />');
        w.document.close();
    } catch (e) {
        console.error('Preview error:', e);
        alert('Önizleme oluşturulamadı: ' + (e.message || e));
    } finally {
        hideLoading('previewBtn');
    }
}
</script>
@endsection



