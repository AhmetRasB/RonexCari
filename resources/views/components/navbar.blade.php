<div class="navbar-header">
    <div class="row align-items-center justify-content-between">
        <div class="col-auto">
            <div class="d-flex flex-wrap align-items-center gap-4">
                <button type="button" class="sidebar-toggle">
                    <iconify-icon icon="heroicons:bars-3-solid" class="icon text-2xl non-active"></iconify-icon>
                    <iconify-icon icon="iconoir:arrow-right" class="icon text-2xl active"></iconify-icon>
                </button>
                <button type="button" class="sidebar-mobile-toggle">
                    <iconify-icon icon="heroicons:bars-3-solid" class="icon"></iconify-icon>
                </button>
                <form class="navbar-search d-none d-md-block">
                    <input type="text" name="search" placeholder="Search">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
                <!-- Global QR/Barcode scan button (mobile-first) -->
                <button type="button" class="btn btn-outline-success ms-2" id="openGlobalScanner">
                    <iconify-icon icon="solar:qr-code-outline" class="me-1"></iconify-icon>
                    QR
                </button>
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex flex-wrap align-items-center gap-3">

                <div class="dropdown">
                    <button id="notificationBtn" class="has-indicator w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center" type="button" data-bs-toggle="dropdown">
                        <iconify-icon icon="iconoir:bell" class="text-primary-light text-xl"></iconify-icon>
                    </button>
                    <div class="dropdown-menu to-top dropdown-menu-lg p-0">
                        <div class="m-16 py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-between gap-2">
                            <div>
                                <h6 class="text-lg text-primary-light fw-semibold mb-0">Bildirimler</h6>
                            </div>
                            <span id="notificationCount" class="text-primary-600 fw-semibold text-lg w-40-px h-40-px rounded-circle bg-base d-flex justify-content-center align-items-center">0</span>
                        </div>

                        <div id="notificationList" class="max-h-400-px overflow-y-auto scroll-sm pe-4">
                            <div class="px-24 py-12 text-center">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Yükleniyor...</span>
                                </div>
                                <p class="mt-2 mb-0 text-sm text-secondary-light">Bildirimler yükleniyor...</p>
                            </div>
                        </div>

                        <div class="text-center py-12 px-16">
                            <a href="{{ route('dashboard') }}" class="text-primary-600 fw-semibold text-md">Tümünü Gör</a>
                        </div>

                    </div>
                </div><!-- Notification dropdown end -->

                <!-- Account Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary d-flex align-items-center gap-2 px-3 py-2" type="button" data-bs-toggle="dropdown">
                        <iconify-icon icon="heroicons:building-office-2" class="text-xl"></iconify-icon>
                        <span class="fw-medium">{{ $currentAccount->name ?? 'Hesap Seç' }}</span>
                        <iconify-icon icon="heroicons:chevron-down" class="text-sm"></iconify-icon>
                    </button>
                    <div class="dropdown-menu to-top dropdown-menu-sm">
                        <div class="py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <h6 class="text-lg text-primary-light fw-semibold mb-0">Hesap Seçimi</h6>
                            </div>
                        </div>
                        <ul class="to-top-list">
                            @foreach(\App\Models\Account::active()->get() as $account)
                                <li>
                                    <form method="POST" action="{{ route('account.switch') }}" class="w-100">
                                        @csrf
                                        <input type="hidden" name="account_id" value="{{ $account->id }}">
                                        <button type="submit" class="dropdown-item text-black px-0 py-8 hover-bg-transparent d-flex align-items-center gap-3 w-100 border-0 bg-transparent justify-content-center {{ $account->id === ($currentAccount->id ?? null) ? 'bg-primary-50 text-primary-600' : '' }}">
                                            <iconify-icon icon="heroicons:building-office-2" class="icon text-xl"></iconify-icon>
                                            <span class="fw-medium">{{ $account->name }}</span>
                                            @if($account->id === ($currentAccount->id ?? null))
                                                <iconify-icon icon="heroicons:check" class="icon text-primary-600"></iconify-icon>
                                            @endif
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                            <li class="border-top mt-2 pt-2">
                                <a href="{{ route('account.select') }}" class="dropdown-item text-primary-600 px-0 py-8 hover-bg-transparent d-flex align-items-center gap-3 w-100 border-0 bg-transparent justify-content-center">
                                    <iconify-icon icon="heroicons:cog-6-tooth" class="icon text-xl"></iconify-icon>
                                    <span class="fw-medium">Hesap Yönetimi</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div><!-- Account dropdown end -->

                <div class="dropdown">
                    <button class="d-flex justify-content-center align-items-center rounded-circle" type="button" data-bs-toggle="dropdown">
                        <img src="{{ asset('assets/images/user.png') }}" alt="image" class="w-40-px h-40-px object-fit-cover rounded-circle">
                    </button>
                    <div class="dropdown-menu to-top dropdown-menu-sm">
                        <div class="py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <h6 class="text-lg text-primary-light fw-semibold mb-0">{{ Auth::user()->name ?? 'User' }}</h6>
                            </div>
                        </div>
                        <ul class="to-top-list">
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-danger d-flex align-items-center gap-3 w-100 border-0 bg-transparent justify-content-center">
                                        <iconify-icon icon="lucide:power" class="icon text-xl"></iconify-icon> Çıkış Yap
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div><!-- Profile dropdown end -->
            </div>
        </div>
    </div>
</div>

<!-- Scanner Modal -->
<div class="modal fade" id="scannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-sm-down modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR/Barkod Tarayıcı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container" style="max-width:700px;">
                    <div id="cameraAlerts" class="alert alert-warning d-none" role="alert"></div>
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kamera Seç</label>
                            <select id="cameraSelector" class="form-select"></select>
                        </div>
                        <div class="col-md-6 text-end">
                            <button id="btnStartScan" type="button" class="btn btn-success me-2">
                                <iconify-icon icon="solar:play-outline" class="me-1"></iconify-icon>
                                Kamerayı Başlat
                            </button>
                            <button id="btnStopScan" type="button" class="btn btn-outline-secondary" disabled>
                                <iconify-icon icon="solar:pause-outline" class="me-1"></iconify-icon>
                                Durdur
                            </button>
                        </div>
                    </div>
                    <div id="scannerContainer" class="w-100" style="max-width:680px;margin:0 auto;">
                        <div id="qr-reader" style="width:100%;min-height:240px;background:#0001;border-radius:8px;"></div>
                        <div class="text-center mt-2">
                            <small class="text-muted d-block">Mobilde kamera izni vermeyi unutmayın.</small>
                            <small class="text-muted">Tarayıcı destekliyorsa barkodlar da okunur.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="scanMoreToggle" checked>
                    <label class="form-check-label" for="scanMoreToggle">Bir tane daha tara</label>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>
    <audio id="scanBeep">
        <source src="https://actions.google.com/sounds/v1/cartoon/wood_plank_flicks.ogg" type="audio/ogg">
    </audio>
    <style>
        #qr-reader__scan_region video { width: 100% !important; height: auto !important; }
        .scan-success { outline: 3px solid #28a745; }
    </style>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.10/html5-qrcode.min.js"></script>
<script>
    (function(){
        const openBtn = document.getElementById('openGlobalScanner');
        const modalEl = document.getElementById('scannerModal');
        let html5QrCode = null;
        let targetContext = 'global';
        let isRunning = false;

        // Allow other pages to open scanner with context
        window.openScanner = function(context){
            targetContext = context || 'global';
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        openBtn?.addEventListener('click', function(){
            window.openScanner('global');
        });

        function showAlert(msg, type='warning'){
            const a = document.getElementById('cameraAlerts');
            a.className = 'alert alert-' + type;
            a.textContent = msg;
            a.classList.remove('d-none');
        }

        function clearAlert(){
            const a = document.getElementById('cameraAlerts');
            a.classList.add('d-none');
            a.textContent = '';
        }

        async function populateCameras(){
            try {
                const devices = await Html5Qrcode.getCameras();
                const sel = document.getElementById('cameraSelector');
                sel.innerHTML = '';
                if (!devices || devices.length === 0) {
                    showAlert('Kamera bulunamadı. Tarayıcı izinlerini kontrol edin ve HTTPS kullanın.');
                    return;
                }
                devices.forEach((d, idx) => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.label || ('Kamera ' + (idx+1));
                    sel.appendChild(opt);
                });
                // Prefer back camera if found
                const back = devices.find(d => /back|rear|arka/i.test(d.label || ''));
                if (back) sel.value = back.id;
                clearAlert();
            } catch (e) {
                showAlert('Kameralara erişilemedi: ' + e.message + '. Lütfen siteye kamera izni verin ve sayfayı yenileyin.');
            }
        }

        async function startScan(){
            if (!window.isSecureContext) {
                showAlert('Kamera için güvenli bağlam (HTTPS) gereklidir. Lütfen siteyi https üzerinden açın.', 'danger');
                return;
            }
            try {
                clearAlert();
                const config = { fps: 15, qrbox: { width: 300, height: 300 }, aspectRatio: 1.7778, rememberLastUsedCamera: true };
                const camId = document.getElementById('cameraSelector').value || { facingMode: 'environment' };
                html5QrCode = html5QrCode || new Html5Qrcode('qr-reader');
                await html5QrCode.start(camId, config, onScanSuccess);
                isRunning = true;
                document.getElementById('btnStartScan').setAttribute('disabled', 'disabled');
                document.getElementById('btnStopScan').removeAttribute('disabled');
            } catch (err) {
                showAlert('Kamera başlatılamadı: ' + err.message, 'danger');
            }
        }

        async function stopScan(){
            try {
                if (html5QrCode && isRunning) {
                    await html5QrCode.stop();
                    await html5QrCode.clear();
                    isRunning = false;
                    document.getElementById('btnStartScan').removeAttribute('disabled');
                    document.getElementById('btnStopScan').setAttribute('disabled', 'disabled');
                }
            } catch(e) {}
        }

        modalEl.addEventListener('shown.bs.modal', function(){
            populateCameras();
        });

        modalEl.addEventListener('hidden.bs.modal', function(){
            stopScan();
            document.getElementById('qr-reader').classList.remove('scan-success');
        });

        document.getElementById('btnStartScan')?.addEventListener('click', startScan);
        document.getElementById('btnStopScan')?.addEventListener('click', stopScan);

        function onScanSuccess(decodedText) {
            // Visual feedback and beep
            document.getElementById('qr-reader').classList.add('scan-success');
            document.getElementById('scanBeep')?.play().catch(()=>{});

            // Handle Ronex product QR → open product detail
            if (/ronex\.com\.tr\/products\/(\d+)/.test(decodedText)) {
                const id = decodedText.match(/products\/(\d+)/)[1];
                if (targetContext === 'invoice') {
                    // In invoice page: search by product id
                    window.addScannedProductById?.(id);
                } else {
                    window.location.href = '/products/' + id;
                }
            } else {
                // Fallback: try to use as product code/barcode in invoice context
                if (targetContext === 'invoice') {
                    window.addScannedProductByCode?.(decodedText);
                } else {
                    // Show the result as alert
                    alert('Taranan kod: ' + decodedText);
                }
            }

            // Continue scanning or close
            const keepScanning = document.getElementById('scanMoreToggle')?.checked;
            if (!keepScanning && html5QrCode) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal?.hide();
            } else {
                setTimeout(()=>{
                    document.getElementById('qr-reader').classList.remove('scan-success');
                }, 300);
            }
        }
    })();
</script>
@endpush