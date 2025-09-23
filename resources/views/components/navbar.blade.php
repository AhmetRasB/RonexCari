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
                <button type="button" class="btn btn-outline-success btn-sm ms-2" id="openSimpleScanner">
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

<!-- Simple Scanner Modal -->
<div class="modal fade" id="simpleScanner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down" style="max-width: 760px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Barkod Okuyucu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-sm-6">
                            <label class="form-label mb-1">Kamera</label>
                            <select id="ssCamera" class="form-select"></select>
                        </div>
                        <div class="col-12 col-sm-6 text-sm-end mt-2 mt-sm-0">
                            <div class="form-check d-inline-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" id="ssScanMore" checked>
                                <label class="form-check-label" for="ssScanMore">Bir tane daha tara</label>
                            </div>
                        </div>
                    </div>
                    <div class="position-relative mt-3" id="ssViewport">
                        <video id="ssVideo" playsinline></video>
                        <div id="ssFrame" class="ss-frame"></div>
                    </div>
                    <div id="ssAlert" class="alert alert-warning d-none mt-3"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
/* Responsive scanner layout */
#simpleScanner .modal-content{border-radius:12px;}
#ssViewport{background:#000;border-radius:10px;overflow:hidden;max-width:100%;margin:0 auto;aspect-ratio:1/1;}
#ssVideo{width:100%;height:100%;object-fit:cover;display:block;}
.ss-frame{position:absolute;inset:10%;border:4px solid rgba(255,255,255,.95);border-radius:10px;box-shadow:0 0 0 9999px rgba(0,0,0,.35) inset;pointer-events:none;}
.ss-frame.success{border-color:#28a745;box-shadow:0 0 0 9999px rgba(40,167,69,.25) inset;}
@media (max-width:576px){.navbar-search{display:none!important} .ss-frame{inset:12%;}}
</style>
<script>
(function(){
    const btn = document.getElementById('openSimpleScanner');
    const modalEl = document.getElementById('simpleScanner');
    const video = document.getElementById('ssVideo');
    const frame = document.getElementById('ssFrame');
    const cameraSel = document.getElementById('ssCamera');
    const alertBox = document.getElementById('ssAlert');
    let stream = null; let running=false; let timer=null; let detector=null; let context='global';

    window.openScanner = function(ctx){ context = ctx || 'global'; new bootstrap.Modal(modalEl).show(); }
    btn?.addEventListener('click', ()=>openScanner('global'));

    function show(msg,type='warning'){ alertBox.className='alert alert-'+type; alertBox.textContent=msg; alertBox.classList.remove('d-none'); }
    function hideAlert(){ alertBox.classList.add('d-none'); alertBox.textContent=''; }

    async function listCameras(){
        try{
            const devices = await navigator.mediaDevices.enumerateDevices();
            const cams = devices.filter(d=>d.kind==='videoinput');
            cameraSel.innerHTML='';
            cams.forEach((d,i)=>{ const o=document.createElement('option'); o.value=d.deviceId; o.textContent=d.label||('Kamera '+(i+1)); cameraSel.appendChild(o); });
            const back = cams.find(d=>/back|rear|arka/i.test(d.label||'')); if(back) cameraSel.value=back.deviceId;
            if(cams.length===0) show('Kamera bulunamadı. İzinleri ve HTTPS bağlantısını kontrol edin.'); else hideAlert();
        }catch(e){ show('Kameralara erişilemedi: '+e.message,'danger'); }
    }

    async function start(){
        try{
            if(!('BarcodeDetector' in window)) throw new Error('Tarayıcı desteklemiyor');
            const constraints={ video: cameraSel.value?{deviceId:{exact:cameraSel.value}}:{ facingMode:{ideal:'environment'} }, audio:false };
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject=stream; await video.play(); running=true; hideAlert();
            detector = new BarcodeDetector({ formats:['qr_code','code_128','ean_13','ean_8','code_39'] });
            schedule();
        }catch(e){ show('Kamera başlatılamadı: '+e.message,'danger'); }
    }
    function stop(){ running=false; if(timer){clearTimeout(timer);timer=null;} try{stream?.getTracks().forEach(t=>t.stop());}catch(_){ } frame.classList.remove('success'); video.srcObject=null; }
    function schedule(){ if(!running) return; timer=setTimeout(scan, 300); }
    async function scan(){
        if(!running) return; try{
            const codes = await detector.detect(video);
            if(codes && codes.length){
                frame.classList.add('success');
                const val = codes[0].rawValue || '';
                handleResult(val);
                if(!document.getElementById('ssScanMore').checked){ bootstrap.Modal.getInstance(modalEl)?.hide(); return; }
                setTimeout(()=>frame.classList.remove('success'), 350);
            }
        }catch(_){ }
        schedule();
    }
    function handleResult(text){
        const m = text.match(/products\/(\d+)/);
        if(m){
            const id=m[1];
            if(context==='invoice'){ window.addScannedProductById?.(id); }
            else window.location.href='/products/'+id;
            return;
        }
        if(context==='invoice'){ window.addScannedProductByCode?.(text); }
        else alert('Taranan kod: '+text);
    }

    modalEl.addEventListener('shown.bs.modal', async ()=>{ await listCameras(); await start(); });
    modalEl.addEventListener('hidden.bs.modal', ()=>{ stop(); });
    cameraSel.addEventListener('change', ()=>{ stop(); start(); });
})();
</script>
@endpush
