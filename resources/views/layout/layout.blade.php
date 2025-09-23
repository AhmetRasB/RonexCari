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
                            <button type="button" class="btn btn-outline-primary" id="scanModeQr">QR</button>
                            <button type="button" class="btn btn-outline-primary" id="scanModeBarcode">Barkod</button>
                        </div>
                        <div class="form-check form-switch">
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
                    <div class="small text-muted mt-2">Mobilde QR, masaüstünde barkod önerilir.</div>
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
            } else {
                // Default behavior: navigate to product if QR url pattern matches
                const match = (payload||'').match(/\/products\/(\d+)/);
                if (match) {
                    window.location.href = '/products/' + match[1];
                } else if (onSingleResult) {
                    onSingleResult(payload);
                }
            }
        }

        function startQr(){
            stopAll();
            currentMode = 'qr';
            qrReaderEl.classList.remove('d-none');
            barcodeReaderEl.classList.add('d-none');
            html5Qr = new Html5Qrcode('qrReader');
            Html5Qrcode.getCameras().then(cams => {
                const id = (cams && cams[0]) ? cams[0].id : undefined;
                html5Qr.start({ facingMode: "environment", deviceId: id }, { fps: 10, qrbox: 250 }, (decoded)=>{
                    handlePayload(decoded);
                    if (!multiScan) { showPostScanControls(); }
                });
            }).catch(()=>{
                html5Qr.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 }, (decoded)=>{
                    handlePayload(decoded);
                    if (!multiScan) { showPostScanControls(); }
                });
            });
        }

        function startBarcode(){
            stopAll();
            currentMode = 'barcode';
            qrReaderEl.classList.add('d-none');
            barcodeReaderEl.classList.remove('d-none');
            Quagga.init({
                inputStream: { name: 'Live', type: 'LiveStream', target: barcodeReaderEl, constraints: { facingMode: 'environment' } },
                decoder: { readers: ['code_128_reader','ean_reader','ean_8_reader'] }
            }, function(err){
                if (err) { console.log(err); return; }
                Quagga.start();
            });
            Quagga.onDetected(function(result){
                const code = (result && result.codeResult && result.codeResult.code) ? result.codeResult.code : null;
                if (!code) return;
                handlePayload(code);
                if (!multiScan) { showPostScanControls(); }
            });
        }

        function stopAll(){
            try { Quagga.offDetected(); Quagga.stop(); } catch(e){}
            try { if (html5Qr) { html5Qr.stop().then(()=>{ html5Qr.clear(); html5Qr = null; }).catch(()=>{}); } } catch(e){}
        }

        document.getElementById('scanModeQr').addEventListener('click', function(){ hidePostScanControls(); startQr(); });
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
            if (currentMode === 'qr') startQr(); else startBarcode();
        });
        document.getElementById('closeScannerBtn').addEventListener('click', function(){
            window.dispatchEvent(new Event('close-scanner'));
        });

        modalEl.addEventListener('hidden.bs.modal', function(){ stopAll(); invoiceContext = false; onSingleResult = null; hidePostScanControls(); });
        window.addEventListener('close-scanner', function(){
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        });

        // Public API
        window.openGlobalScanner = function(options){
            options = options || {};
            invoiceContext = !!options.invoiceContext;
            onSingleResult = options.onResult || null;
            multiScan = !!options.multi;
            const defaultMode = options.mode || ((/Mobi|Android/i.test(navigator.userAgent)) ? 'qr' : 'barcode');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            if (defaultMode === 'qr') { startQr(); }
            else { startBarcode(); }
        }

        // Convenience hooks for buttons if present
        document.addEventListener('click', function(e){
            if (e.target.closest('#openNavbarScanner')){
                const isMobile = /Mobi|Android/i.test(navigator.userAgent);
                window.openGlobalScanner({ mode: isMobile ? 'qr' : 'barcode' });
            }
            if (e.target.closest('#openInvoiceScanner')){
                window.openGlobalScanner({ invoiceContext: true, multi: true });
            }
        });
    })();
    </script>

</body>

</html>