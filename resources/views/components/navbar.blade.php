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
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <button type="button" class="btn btn-outline-success d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#qrScannerModal">
                    <iconify-icon icon="solar:qr-code-outline" class="text-xl"></iconify-icon>
                    <span class="d-none d-md-inline">QR</span>
                </button>

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

<!-- Global QR Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrScannerLabel">QR Barkod Okuyucu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2 d-flex align-items-center gap-2">
                    <label for="qr-camera-select" class="form-label mb-0">Kamera:</label>
                    <select id="qr-camera-select" class="form-select form-select-sm" style="max-width: 280px;"></select>
                </div>
                <div id="qr-reader" style="width: 100%;"></div>
                <div id="qr-reader-results" class="mt-2 small text-muted"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/html5-qrcode" defer></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let html5Qr;
        let currentCameraId = null;
        const modal = document.getElementById('qrScannerModal');
        function startScannerWithDevice(deviceId) {
            const onScanSuccess = (decodedText) => {
                const results = document.getElementById('qr-reader-results');
                if (results) results.innerText = decodedText;
                // Eğer fatura oluştur sayfasındaysak satıra ekle; değilsek ön izlemeye git
                fetch(`{{ route('products.lookup') }}?q=` + encodeURIComponent(decodedText))
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            // If we are on sales invoice create, add row, else go to products page
                            if (typeof window.addProductFromScanner === 'function') {
                                window.addProductFromScanner(data.product);
                                // keep scanning for multiple items
                            } else {
                                const previewUrl = `{{ route('products.qr.preview') }}` + `?q=` + encodeURIComponent(decodedText);
                                window.location.href = previewUrl;
                            }
                        } else {
                            alert('Ürün bulunamadı');
                        }
                    })
                    .catch(() => alert('Bağlantı hatası'));
            };
            const config = { fps: 10, qrbox: 250 };
            const qrRegion = document.getElementById('qr-reader');
            qrRegion.innerHTML = '';
            if (html5Qr) {
                try { html5Qr.stop().then(() => html5Qr.clear()); } catch(e) {}
            }
            html5Qr = new Html5Qrcode('qr-reader');
            currentCameraId = deviceId;
            html5Qr.start(
                { deviceId: { exact: deviceId } },
                config,
                onScanSuccess,
                (err) => { /* ignore scan errors */ }
            ).catch(err => {
                console.error('QR start error:', err);
            });
        }
        modal.addEventListener('shown.bs.modal', function () {
            if (!window.Html5Qrcode) return;
            const select = document.getElementById('qr-camera-select');
            select.innerHTML = '<option value="">Kameralar yükleniyor...</option>';
            Html5Qrcode.getCameras().then(cams => {
                if (!cams || cams.length === 0) {
                    select.innerHTML = '<option> Kamera bulunamadı</option>';
                    return;
                }
                // Populate list
                select.innerHTML = '';
                cams.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.label || c.id;
                    select.appendChild(opt);
                });
                // Prefer back/environment camera
                let preferred = cams.find(c => /back|rear|environment/i.test(c.label)) || cams[cams.length - 1];
                select.value = preferred.id;
                startScannerWithDevice(preferred.id);
            }).catch(err => {
                console.error('Camera list error:', err);
                // Try facingMode fallback
                const qrRegion = document.getElementById('qr-reader');
                qrRegion.innerHTML = '';
                html5Qr = new Html5Qrcode('qr-reader');
                html5Qr.start(
                    { facingMode: { exact: 'environment' } },
                    { fps: 10, qrbox: 250 },
                    (txt) => {
                        const results = document.getElementById('qr-reader-results');
                        if (results) results.innerText = txt;
                        fetch(`{{ route('products.lookup') }}?q=` + encodeURIComponent(txt))
                          .then(r => r.json()).then(data => {
                            if (data.success && typeof window.addProductFromScanner === 'function') {
                                window.addProductFromScanner(data.product);
                            }
                          });
                    }
                ).catch(e => console.error('Environment start error:', e));
            });
            // Handle camera change
            select.addEventListener('change', function() {
                if (this.value && this.value !== currentCameraId) {
                    startScannerWithDevice(this.value);
                }
            });
        });
        modal.addEventListener('hidden.bs.modal', function () {
            try {
                if (html5Qr) {
                    html5Qr.stop().then(() => html5Qr.clear());
                }
                document.getElementById('qr-reader').innerHTML = '';
            } catch (e) {}
        });
    });
    </script>
</div>