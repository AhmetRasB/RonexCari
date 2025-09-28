<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<x-head />

<body>

    <!-- ..::  header area start ::.. -->
    <x-sidebar />
    <!-- ..::  header area end ::.. -->

    <main class="dashboard-main">

        <!-- ..::  navbar start ::.. -->
        <x-navbar />
        <!-- ..::  navbar end ::.. -->
        <div class="dashboard-main-body">
            
            <!-- ..::  breadcrumb  start ::.. -->
            <x-breadcrumb title='{{ isset($title) ? $title : "" }}' subTitle='{{ isset($subTitle) ? $subTitle : "" }}' />
            <!-- ..::  header area end ::.. -->

            @yield('content')
        
        </div>
        <!-- ..::  footer  start ::.. -->
        <x-footer />
        <!-- ..::  footer area end ::.. -->

    </main>

    <!-- ..::  scripts  start ::.. -->
    <x-script  script='{!! isset($script) ? $script : "" !!}' />
    @stack('scripts')
    <!-- ..::  scripts  end ::.. -->
    
    <!-- Fix navbar z-index -->
    <style>
        .navbar-header {
            z-index: 1030 !important;
        }
        .navbar-header .dropdown-menu {
            z-index: 1031 !important;
        }
    </style>

    <!-- Global Scanner Modal -->
    <div class="modal fade" id="globalScannerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-sm-down modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tarayıcı</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="btn-group" role="group" aria-label="Scan mode">
                            <button type="button" class="btn btn-outline-primary active" id="scanModeBarcode">Barkod Okuyucu</button>
                        </div>
                        <div class="form-check form-switch d-none" id="multiScanToggleWrapper">
                            <input class="form-check-input" type="checkbox" id="scanMultiToggle">
                            <label class="form-check-label" for="scanMultiToggle">Çoklu tarama</label>
                        </div>
                    </div>
                    <div id="scannerViewport" class="border rounded position-relative" style="min-height:300px;">
                        <div id="qrReader" style="width:100%;"></div>
                        <div id="barcodeReader" class="d-none w-100" style="height:300px; background:#000;"></div>
                        <div id="scanBorder" class="position-absolute top-0 start-0 w-100 h-100" style="border: 3px solid transparent; pointer-events:none;"></div>
                    </div>
                    <audio id="scanBeep">
                        <source src="https://actions.google.com/sounds/v1/cartoon/wood_plank_flicks.ogg" type="audio/ogg">
                    </audio>
                    <div class="small text-muted mt-2">Barkod okunduktan sonra 5 saniye bekleyin. QR kodlar için telefon kamera uygulamasını kullanın.</div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <div id="postScanControls" class="d-none">
                        <button type="button" class="btn btn-primary me-2" id="scanAgainBtn">Daha fazla tara</button>
                        <button type="button" class="btn btn-secondary" id="closeScannerBtn">Kapat</button>
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanner Libraries -->
    <script src="https://unpkg.com/html5-qrcode@2.3.11/html5-qrcode.min.js"></script>
    <script src="https://unpkg.com/quagga@0.12.1/dist/quagga.min.js"></script>
    <script>
    (function(){
        let html5Qr;
        let currentMode = 'qr';
        let multiScan = false;
        let invoiceContext = false;
        let onSingleResult = null;
        let lastPayload = null;

        const modalEl = document.getElementById('globalScannerModal');
        const scanBorder = document.getElementById('scanBorder');
        const qrReaderEl = document.getElementById('qrReader');
        const barcodeReaderEl = document.getElementById('barcodeReader');
        const beepEl = document.getElementById('scanBeep');

        function setBorder(color){
            scanBorder.style.borderColor = color;
            setTimeout(()=>{ scanBorder.style.borderColor = 'transparent'; }, 600);
        }

        function handlePayload(payload){
            try { beepEl && beepEl.play().catch(()=>{}); } catch(e){}
            setBorder('#28a745');
            lastPayload = payload;

            // 5 saniye bekleme ekle - farklı barkodları hemen alıp bozulmasın
            setTimeout(() => {
                // If invoice page provided global functions, use them
                if (invoiceContext) {
                    if (currentMode === 'qr') {
                        const match = (payload||'').match(/\/products\/(\d+)/);
                        if (match && window.addScannedProductById) {
                            window.addScannedProductById(match[1]);
                        } else if (window.addScannedProductByCode) {
                            window.addScannedProductByCode(payload);
                        }
                    } else {
                        if (window.addScannedProductByCode) {
                            window.addScannedProductByCode(payload);
                        }
                    }
                    // Close scanner immediately after adding to invoice to avoid lingering camera/backdrop
                    window.dispatchEvent(new Event('close-scanner'));
                } else {
                    // Default behavior: navigate to product if QR url pattern matches
                    const match = (payload||'').match(/\/products\/(\d+)/);
                    if (match) {
                        window.location.href = '/products/' + match[1];
                    } else if (onSingleResult) {
                        onSingleResult(payload);
                    }
                }
            }, 5000); // 5 saniye bekleme
        }

        function startQr(){
            stopAll();
            currentMode = 'qr';
            qrReaderEl.classList.remove('d-none');
            barcodeReaderEl.classList.add('d-none');
            html5Qr = new Html5Qrcode('qrReader');
            Html5Qrcode.getCameras().then(cams => {
                const id = (cams && cams[0]) ? cams[0].id : undefined;
                html5Qr.start({ facingMode: "environment", deviceId: id }, { fps: 12, qrbox: 250, formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE, Html5QrcodeSupportedFormats.CODE_128, Html5QrcodeSupportedFormats.EAN_13, Html5QrcodeSupportedFormats.EAN_8 ] }, (decoded)=>{
                    handlePayload(decoded);
                    if (!multiScan) { showPostScanControls(); }
                });
            }).catch(()=>{
                html5Qr.start({ facingMode: "environment" }, { fps: 12, qrbox: 250, formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE, Html5QrcodeSupportedFormats.CODE_128, Html5QrcodeSupportedFormats.EAN_13, Html5QrcodeSupportedFormats.EAN_8 ] }, (decoded)=>{
                    handlePayload(decoded);
                    if (!multiScan) { showPostScanControls(); }
                });
            });
        }

        function startBarcode(){
            console.log('🎬 Starting barcode scanner...');
            
            // Önce temizlik yap ama çok agresif olma
            try { 
                if (typeof Quagga !== 'undefined' && Quagga.stop) {
                    Quagga.stop();
                }
            } catch(e){
                console.log('Error stopping previous Quagga:', e);
            }
            
            currentMode = 'barcode';
            qrReaderEl.classList.add('d-none');
            barcodeReaderEl.classList.remove('d-none');
            
            // Barcode reader elementini temizle
            barcodeReaderEl.innerHTML = '';
            
            console.log('🔧 Initializing Quagga...');
            Quagga.init({
                inputStream: { 
                    name: 'Live', 
                    type: 'LiveStream', 
                    target: barcodeReaderEl, 
                    constraints: { 
                        facingMode: 'environment',
                        width: 640,
                        height: 480
                    } 
                },
                decoder: { 
                    readers: ['code_128_reader','ean_reader','ean_8_reader'],
                    debug: {
                        showCanvas: true,
                        showPatches: false,
                        showFoundPatches: false,
                        showSkeleton: false,
                        showLabels: false,
                        showPatchLabels: false,
                        showBoundingBox: false,
                        showScanningRegion: false,
                        boxFromPatches: {
                            showTransformed: true,
                            showTransformedBox: true,
                            showBB: true
                        }
                    }
                },
                locate: true,
                locator: {
                    patchSize: 'medium',
                    halfSample: true
                }
            }, function(err){
                if (err) { 
                    console.log('❌ Quagga init error:', err); 
                    return; 
                }
                console.log('✅ Quagga initialized successfully');
                Quagga.start();
                console.log('🚀 Quagga started');
            });
            
            Quagga.onDetected(function(result){
                const code = (result && result.codeResult && result.codeResult.code) ? result.codeResult.code : null;
                if (!code) return;
                console.log('📱 Barcode detected:', code);
                handlePayload(code);
                if (!multiScan) { showPostScanControls(); }
            });
        }

        function stopAll(){
            console.log('🛑 Stopping all scanners...');
            
            // Quagga barkod okuyucuyu durdur - daha güvenli yaklaşım
            try { 
                if (typeof Quagga !== 'undefined') {
                    Quagga.offDetected(); 
                    if (Quagga.stop) {
                        Quagga.stop(); 
                    }
                    console.log('✅ Quagga stopped');
                }
            } catch(e){
                console.log('❌ Error stopping Quagga:', e);
            }
            
            // HTML5 QR okuyucuyu durdur
            try { 
                if (html5Qr) { 
                    html5Qr.stop().then(()=>{
                        html5Qr.clear(); 
                        html5Qr = null; 
                        console.log('✅ HTML5 QR stopped');
                    }).catch((err)=>{
                        console.log('❌ Error stopping HTML5 QR:', err);
                        html5Qr = null;
                    }); 
                } 
            } catch(e){
                console.log('❌ Error with HTML5 QR:', e);
                html5Qr = null;
            }
            
            // Medya tracklerini durdur - daha kontrollü
            try {
                const allVideos = document.querySelectorAll('video');
                console.log('📹 Found videos to stop:', allVideos.length);
                
                allVideos.forEach(function(video, index){
                    // Video elementini pause et
                    try {
                        video.pause();
                        video.currentTime = 0;
                    } catch(e) {
                        console.log('Error pausing video:', e);
                    }
                    
                    // Stream'i durdur
                    if (video.srcObject && video.srcObject.getTracks) {
                        const tracks = video.srcObject.getTracks();
                        console.log(`📡 Video ${index} has ${tracks.length} tracks`);
                        
                        tracks.forEach(function(track, trackIndex){
                            try { 
                                track.stop(); 
                                console.log(`✅ Track ${trackIndex} stopped:`, track.kind);
                            } catch(e){
                                console.log(`❌ Error stopping track ${trackIndex}:`, e);
                            } 
                        });
                        video.srcObject = null;
                    }
                });
                
            } catch(e){
                console.log('❌ Error stopping media tracks:', e);
            }
            
            // Reader elementlerini temizle
            try { 
                if (qrReaderEl) {
                    qrReaderEl.innerHTML = ''; 
                    console.log('✅ QR reader cleared');
                }
            } catch(e){
                console.log('❌ Error clearing QR reader:', e);
            }
            
            try { 
                if (barcodeReaderEl) {
                    barcodeReaderEl.innerHTML = ''; 
                    console.log('✅ Barcode reader cleared');
                }
            } catch(e){
                console.log('❌ Error clearing barcode reader:', e);
            }
            
            console.log('🛑 All scanners stopped');
        }

        function cleanupModalArtifacts(){
            try {
                document.querySelectorAll('.modal-backdrop').forEach(function(el){ el.parentNode && el.parentNode.removeChild(el); });
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('paddingRight');
            } catch(e){}
        }

        function forceCloseScanner(){
            console.log('🚨 FORCE CLOSING SCANNER - AGGRESSIVE MODE 🚨');
            
            try { 
                // Önce tüm kameraları ve tarayıcıları durdur
                stopAll(); 
            } catch(e){
                console.log('Error in stopAll:', e);
            }
            
            // Modal'ı kapat
            try {
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
                console.log('Modal hidden');
            } catch(e){
                console.log('Error hiding modal:', e);
            }
            
            // AGGRESSIVE: Tüm video elementlerini ve medya tracklerini zorla kapat
            try {
                const allVideos = document.querySelectorAll('video');
                console.log('🔍 Found videos for aggressive cleanup:', allVideos.length);
                
                allVideos.forEach(function(video, index){
                    console.log(`🎥 Aggressively processing video ${index}:`, video);
                    
                    // Video'yu pause et ve sıfırla
                    try {
                        video.pause();
                        video.currentTime = 0;
                        video.muted = true;
                        video.volume = 0;
                    } catch(e) {
                        console.log('Error pausing video:', e);
                    }
                    
                    // Stream'i durdur
                    if (video.srcObject && video.srcObject.getTracks) {
                        const tracks = video.srcObject.getTracks();
                        console.log(`📡 Video ${index} has ${tracks.length} tracks`);
                        
                        tracks.forEach(function(track, trackIndex){
                            try { 
                                track.stop(); 
                                console.log(`✅ Track ${trackIndex} stopped:`, track.kind, track.label);
                            } catch(e){
                                console.log(`❌ Error stopping track ${trackIndex}:`, e);
                            } 
                        });
                        video.srcObject = null;
                    }
                    
                    // Video elementini DOM'dan kaldır
                    try {
                        if (video.parentNode) {
                            video.parentNode.removeChild(video);
                            console.log(`🗑️ Video ${index} removed from DOM`);
                        }
                    } catch(e) {
                        console.log('Error removing video element:', e);
                    }
                });
                
                // Ekstra güvenlik: Tüm canvas elementlerini de temizle
                document.querySelectorAll('canvas').forEach(function(canvas, index){
                    try {
                        if (canvas.parentNode && canvas.parentNode.id && 
                            (canvas.parentNode.id.includes('qr') || canvas.parentNode.id.includes('barcode'))) {
                            canvas.parentNode.removeChild(canvas);
                            console.log(`🎨 Canvas ${index} removed`);
                        }
                    } catch(e) {
                        console.log('Error removing canvas:', e);
                    }
                });
                
            } catch(e){
                console.log('Error in aggressive video cleanup:', e);
            }
            
            // Post scan kontrollerini gizle
            try {
                hidePostScanControls();
            } catch(e){
                console.log('Error hiding post scan controls:', e);
            }
            
            // Cleanup işlemlerini yap
            try {
                cleanupModalArtifacts();
            } catch(e){
                console.log('Error in cleanup:', e);
            }
            
            // Final check - kamera ışığı söndü mü?
            setTimeout(() => {
                const finalVideos = document.querySelectorAll('video');
                if (finalVideos.length === 0) {
                    console.log('🎉 SUCCESS: All cameras stopped, no videos remaining!');
                } else {
                    console.log('⚠️ WARNING: Still found videos:', finalVideos.length);
                    finalVideos.forEach((v, i) => {
                        console.log(`Remaining video ${i}:`, v);
                    });
                }
            }, 200);
            
            console.log('✅ Scanner force closed - aggressive cleanup completed');
        }

        // QR butonu kaldırıldı, sadece barkod modu kullanılıyor
        document.getElementById('scanModeBarcode').addEventListener('click', function(){ hidePostScanControls(); startBarcode(); });
        document.getElementById('scanMultiToggle').addEventListener('change', function(e){ multiScan = !!e.target.checked; });

        function showPostScanControls(){
            stopAll();
            const c = document.getElementById('postScanControls');
            if (c) c.classList.remove('d-none');
        }
        function hidePostScanControls(){
            const c = document.getElementById('postScanControls');
            if (c) c.classList.add('d-none');
        }

        document.getElementById('scanAgainBtn').addEventListener('click', function(){
            hidePostScanControls();
            startBarcode(); // Her zaman barkod modu başlat
        });
        document.getElementById('closeScannerBtn').addEventListener('click', function(e){
            e.preventDefault();
            forceCloseScanner();
        });

        // Ensure cleanup on both hide and hidden events
        modalEl.addEventListener('hide.bs.modal', function(){
            stopAll();
            cleanupModalArtifacts();
        });
        modalEl.addEventListener('hidden.bs.modal', function(){
            stopAll();
            invoiceContext = false;
            onSingleResult = null;
            hidePostScanControls();
            cleanupModalArtifacts();
        });
        window.addEventListener('close-scanner', function(){
            forceCloseScanner();
            // Fallback cleanup in case Bootstrap leaves backdrop
            setTimeout(cleanupModalArtifacts, 50);
        });

        // Ensure the built-in "X" button also performs a hard close
        modalEl.addEventListener('click', function(e){
            if (e.target && e.target.matches('.btn-close[data-bs-dismiss="modal"], [data-bs-dismiss="modal"]')) {
                e.preventDefault();
                forceCloseScanner();
            }
        });

        // Also close on ESC key to ensure full cleanup
        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape') {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    e.preventDefault();
                    forceCloseScanner();
                }
            }
        });

        // Clicking on Bootstrap backdrop should also force cleanup/hard close
        document.addEventListener('click', function(e){
            if (document.body.classList.contains('modal-open') && e.target.classList && e.target.classList.contains('modal-backdrop')){
                forceCloseScanner();
                setTimeout(cleanupModalArtifacts, 50);
            }
        });

        // Public API
        window.openGlobalScanner = function(options){
            options = options || {};
            invoiceContext = !!options.invoiceContext;
            onSingleResult = options.onResult || null;
            multiScan = !!options.multi;
            
            // Çoklu tarama seçeneğini sadece invoice context'te göster
            const multiScanToggle = document.getElementById('multiScanToggleWrapper');
            if (invoiceContext) {
                multiScanToggle.classList.remove('d-none');
            } else {
                multiScanToggle.classList.add('d-none');
                multiScan = false; // Normal kullanımda çoklu tarama kapalı
            }
            
            const defaultMode = 'barcode'; // Sadece barkod modu kullanılıyor
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            startBarcode(); // Her zaman barkod modu başlat
        }

        // Convenience hooks for buttons if present
        document.addEventListener('click', function(e){
            if (e.target.closest('#openNavbarScanner')){
                window.openGlobalScanner({ mode: 'barcode', onResult: function(payload){
                    // Try to route to /products/{id} if QR contains it
                    const m = (payload||'').match(/\/products\/(\d+)/);
                    if (m) { window.location.href = '/products/' + m[1]; return; }
                    // Otherwise try search by code via sales invoice search endpoint then navigate
                    fetch('{{ route('sales.invoices.search.products') }}?q=' + encodeURIComponent(payload), { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                      .then(r=>r.json())
                      .then(list=>{ if (list && list.length>0) { const id = (list[0].id||'').toString().replace(/^(product_|series_|service_)/,''); window.location.href = '/products/' + id; } });
                }});
            }
            if (e.target.closest('#openInvoiceScanner')){
                window.openGlobalScanner({ invoiceContext: true, multi: true });
            }
        });
    })();
    </script>

</body>

</html>