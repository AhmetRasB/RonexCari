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
                <div id="scannerContainer" class="w-100" style="max-width:600px;margin:0 auto;">
                    <div id="qr-reader" style="width:100%;"></div>
                    <div class="text-center mt-2">
                        <small class="text-muted d-block">Mobilde kamera izni vermeyi unutmayın.</small>
                        <small class="text-muted">Tarayıcı destekliyorsa barkodlar da okunur.</small>
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

        // Allow other pages to open scanner with context
        window.openScanner = function(context){
            targetContext = context || 'global';
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        openBtn?.addEventListener('click', function(){
            window.openScanner('global');
        });

        modalEl.addEventListener('shown.bs.modal', function(){
            const config = { fps: 15, qrbox: { width: 300, height: 300 }, aspectRatio: 1.7778, rememberLastUsedCamera: true };
            html5QrCode = new Html5Qrcode('qr-reader');
            html5QrCode.start({ facingMode: 'environment' }, config, onScanSuccess)
                .catch(err => console.error('Scanner start error', err));
        });

        modalEl.addEventListener('hidden.bs.modal', function(){
            if (html5QrCode) {
                html5QrCode.stop().then(() => html5QrCode.clear());
                html5QrCode = null;
            }
            document.getElementById('qr-reader').classList.remove('scan-success');
        });

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