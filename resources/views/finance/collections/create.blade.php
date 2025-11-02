@extends('layout.layout')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Yeni Tahsilat</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('finance.collections.index') }}">Tahsilatlar</a></li>
                                <li class="breadcrumb-item active">Yeni Tahsilat</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                <iconify-icon icon="solar:wallet-money-outline" class="me-2"></iconify-icon>
                                Tahsilat Bilgileri
                            </h4>
                        </div>
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <iconify-icon icon="solar:danger-circle-outline" class="me-2"></iconify-icon>
                                    <strong>Hata!</strong> Lütfen formu kontrol edin.
                                    <ul class="mb-0 mt-2">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form action="{{ route('finance.collections.store') }}" method="POST" onsubmit="return validateForm()">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="customer_search" class="form-label">
                                                <iconify-icon icon="solar:user-outline" class="me-1"></iconify-icon>
                                                Müşteri/Tedarikçi <span class="text-danger">*</span>
                                            </label>
                                            <div class="position-relative">
                                                <input type="text" class="form-control @error('customer_id') is-invalid @enderror" 
                                                       id="customer_search" name="customer_search" 
                                                       placeholder="Müşteri ara..." autocomplete="off">
                                                <input type="hidden" id="customer_id" name="customer_id" value="{{ old('customer_id') }}">
                                                <div id="customer_results" class="dropdown-menu w-100" style="display: none; position: absolute; z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
                                            </div>
                                            @error('customer_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="collection_type" class="form-label">
                                                <iconify-icon icon="solar:wallet-outline" class="me-1"></iconify-icon>
                                                Tahsilat Türü <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select @error('collection_type') is-invalid @enderror" id="collection_type" name="collection_type" required>
                                                <option value="">Seçiniz</option>
                                                <option value="nakit" {{ old('collection_type') == 'nakit' ? 'selected' : '' }}>Nakit</option>
                                                <option value="banka" {{ old('collection_type') == 'banka' ? 'selected' : '' }}>Banka</option>
                                                <option value="kredi_karti" {{ old('collection_type') == 'kredi_karti' ? 'selected' : '' }}>Kredi Kartı</option>
                                                <option value="havale" {{ old('collection_type') == 'havale' ? 'selected' : '' }}>Havale</option>
                                                <option value="eft" {{ old('collection_type') == 'eft' ? 'selected' : '' }}>EFT</option>
                                                <option value="cek" {{ old('collection_type') == 'cek' ? 'selected' : '' }}>Çek</option>
                                            </select>
                                            @error('collection_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="transaction_date" class="form-label">
                                                <iconify-icon icon="solar:calendar-outline" class="me-1"></iconify-icon>
                                                İşlem Tarihi <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" 
                                                   id="transaction_date" name="transaction_date" 
                                                   value="{{ old('transaction_date', \Carbon\Carbon::now()->format('Y-m-d')) }}" required>
                                            @error('transaction_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="amount" class="form-label">
                                                <iconify-icon icon="solar:dollar-outline" class="me-1"></iconify-icon>
                                                Ödenecek Tutar <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0.01" 
                                                       class="form-control @error('amount') is-invalid @enderror" 
                                                       id="amount" name="amount" 
                                                       value="{{ old('amount') }}" 
                                                       placeholder="Ödenecek Tutar" required>
                                                <span class="input-group-text" id="currency-symbol">₺</span>
                                            </div>
                                            <small class="text-muted">İndirim yapılacaksa indirim tutarını aşağıdaki alana girin.</small>
                                            @error('amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="currency" class="form-label">
                                                <iconify-icon icon="solar:dollar-outline" class="me-1"></iconify-icon>
                                                Para Birimi <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                                                <option value="TRY" {{ old('currency', 'TRY') == 'TRY' ? 'selected' : '' }}>₺ TRY</option>
                                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>$ USD</option>
                                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>€ EUR</option>
                                            </select>
                                            @error('currency')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="discount" class="form-label">
                                                <iconify-icon icon="solar:tag-price-outline" class="me-1"></iconify-icon>
                                                Yapılacak İndirim
                                            </label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0" 
                                                       class="form-control @error('discount') is-invalid @enderror" 
                                                       id="discount" name="discount" 
                                                       value="{{ old('discount', '') }}" 
                                                       placeholder="İndirim Tutarı (opsiyonel)">
                                                <span class="input-group-text" id="discount-currency-symbol">₺</span>
                                            </div>
                                            <small class="text-muted">İndirim eklendikten sonra kalan tutar ödeme olarak alınacaktır.</small>
                                            @error('discount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row" id="payment-summary" style="display: none;">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <strong>Özet:</strong><br>
                                            <span id="summary-text"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        <iconify-icon icon="solar:document-outline" class="me-1"></iconify-icon>
                                        Açıklama
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" 
                                              placeholder="Açıklama">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('finance.collections.index') }}" class="btn btn-light">
                                        <iconify-icon icon="solar:close-circle-outline" class="me-1"></iconify-icon>
                                        İptal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <iconify-icon icon="solar:check-circle-outline" class="me-1"></iconify-icon>
                                        Kaydet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                <iconify-icon icon="solar:wallet-outline" class="me-2"></iconify-icon>
                                Müşteri Bakiyeleri
                            </h4>
                        </div>
                        <div class="card-body">
                            <div id="customer-balances" style="display: none;">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><strong>₺ TRY:</strong></span>
                                        <span id="balance-try" class="badge bg-primary">0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><strong>$ USD:</strong></span>
                                        <span id="balance-usd" class="badge bg-success">0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><strong>€ EUR:</strong></span>
                                        <span id="balance-eur" class="badge bg-info">0.00</span>
                                    </div>
                                </div>
                            </div>
                            <div id="no-customer-selected" class="text-muted text-center">
                                <iconify-icon icon="solar:user-outline" style="font-size: 48px;"></iconify-icon>
                                <p class="mt-2">Lütfen bir müşteri seçin</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerSearch = document.getElementById('customer_search');
    const customerId = document.getElementById('customer_id');
    const customerResults = document.getElementById('customer_results');
    let searchTimeout;

    // Müşteri bakiyelerini yükle - Önce tanımlanmalı
    function loadCustomerBalances(customerId) {
        console.log('loadCustomerBalances çağrıldı:', customerId);
        
        if (!customerId || customerId === '' || customerId === null || customerId === undefined) {
            document.getElementById('customer-balances').style.display = 'none';
            document.getElementById('no-customer-selected').style.display = 'block';
            return;
        }
        
        // Loading göster
        document.getElementById('customer-balances').style.display = 'block';
        document.getElementById('no-customer-selected').style.display = 'none';
        document.getElementById('balance-try').textContent = 'Yükleniyor...';
        document.getElementById('balance-usd').textContent = 'Yükleniyor...';
        document.getElementById('balance-eur').textContent = 'Yükleniyor...';
        
        const url = `{{ route('finance.collections.get.balances') }}?customer_id=${customerId}`;
        console.log('Bakiye yükleme URL:', url);
        
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Bakiye verisi:', data);
                if (data.error) {
                    console.error('Bakiye yükleme hatası:', data.error);
                    document.getElementById('customer-balances').style.display = 'none';
                    document.getElementById('no-customer-selected').style.display = 'block';
                    return;
                }
                
                // Değerleri güvenli şekilde parse et
                const balanceTry = parseFloat(data.balance_try) || 0;
                const balanceUsd = parseFloat(data.balance_usd) || 0;
                const balanceEur = parseFloat(data.balance_eur) || 0;
                
                console.log('Parse edilmiş bakiyeler:', { balanceTry, balanceUsd, balanceEur });
                
                // Formatla ve göster - eğer toLocaleString desteklenmiyorsa fallback kullan
                const formatNumber = (num) => {
                    if (typeof num === 'number' && !isNaN(num)) {
                        try {
                            return num.toLocaleString('tr-TR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        } catch (e) {
                            return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    }
                    return '0,00';
                };
                
                document.getElementById('balance-try').textContent = formatNumber(balanceTry);
                document.getElementById('balance-usd').textContent = formatNumber(balanceUsd);
                document.getElementById('balance-eur').textContent = formatNumber(balanceEur);
                
                document.getElementById('customer-balances').style.display = 'block';
                document.getElementById('no-customer-selected').style.display = 'none';
            })
            .catch(error => {
                console.error('Bakiye yükleme hatası:', error);
                document.getElementById('customer-balances').style.display = 'none';
                document.getElementById('no-customer-selected').style.display = 'block';
            });
    }

    // Eğer eski değer varsa göster ve bakiyeleri yükle
    @if(old('customer_id'))
        const selectedCustomer = @json($customers->find(old('customer_id')));
        if (selectedCustomer) {
            customerSearch.value = selectedCustomer.name + ' - ' + selectedCustomer.email;
            customerId.value = {{ old('customer_id') }};
            loadCustomerBalances({{ old('customer_id') }});
        }
    @endif

    customerSearch.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            customerResults.style.display = 'none';
            customerId.value = '';
            // Müşteri silindiğinde bakiyeleri gizle
            document.getElementById('customer-balances').style.display = 'none';
            document.getElementById('no-customer-selected').style.display = 'block';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('finance.collections.search.customers') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        customerResults.innerHTML = data.map(customer => `
                            <a href="#" class="dropdown-item" data-id="${customer.id}" data-name="${customer.name}" data-email="${customer.email}">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-xs">
                                            <div class="avatar-title bg-soft-primary text-primary rounded-circle fs-16">
                                                <iconify-icon icon="solar:user-outline"></iconify-icon>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <h6 class="mb-0">${customer.name}</h6>
                                        <small class="text-muted">${customer.email}</small>
                                    </div>
                                </div>
                            </a>
                        `).join('');
                        customerResults.style.display = 'block';
                    } else {
                        customerResults.innerHTML = '<div class="dropdown-item text-muted">Müşteri bulunamadı</div>';
                        customerResults.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Arama hatası:', error);
                    customerResults.style.display = 'none';
                });
        }, 300);
    });

    // Müşteri seçimi
    customerResults.addEventListener('click', function(e) {
        e.preventDefault();
        const item = e.target.closest('.dropdown-item');
        if (item && item.dataset.id) {
            console.log('Müşteri seçildi:', item.dataset.id, item.dataset.name);
            customerId.value = item.dataset.id;
            customerSearch.value = item.dataset.name + ' - ' + item.dataset.email;
            customerResults.style.display = 'none';
            
            // Müşteri seçildiğinde bakiyeleri getir
            loadCustomerBalances(item.dataset.id);
        }
    });
    
    // Müşteri ID input alanı değiştiğinde bakiyeleri yükle (manuel değişiklik için)
    customerId.addEventListener('change', function() {
        console.log('Customer ID değişti:', this.value);
        if (this.value && this.value.trim() !== '') {
            loadCustomerBalances(this.value);
        } else {
            document.getElementById('customer-balances').style.display = 'none';
            document.getElementById('no-customer-selected').style.display = 'block';
        }
    });
    

    // Dışarı tıklayınca kapat
    document.addEventListener('click', function(e) {
        if (!customerSearch.contains(e.target) && !customerResults.contains(e.target)) {
            customerResults.style.display = 'none';
        }
    });

    // Para birimi değişikliği
    const currencySelect = document.getElementById('currency');
    const currencySymbol = document.getElementById('currency-symbol');
    const discountCurrencySymbol = document.getElementById('discount-currency-symbol');
    const amountInput = document.getElementById('amount');
    const discountInput = document.getElementById('discount');
    
    function updateCurrencySymbols() {
        const currency = currencySelect.value;
        const symbol = currency === 'TRY' ? '₺' : (currency === 'USD' ? '$' : '€');
        currencySymbol.textContent = symbol;
        discountCurrencySymbol.textContent = symbol;
    }
    
    currencySelect.addEventListener('change', function() {
        updateCurrencySymbols();
        calculatePayment();
    });
    
    // İndirim ve ödeme hesaplama
    function calculatePayment() {
        const paymentAmount = parseFloat(amountInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const currency = currencySelect.value;
        const symbol = currency === 'TRY' ? '₺' : (currency === 'USD' ? '$' : '€');
        
        if (paymentAmount > 0 || discount > 0) {
            const totalDebt = paymentAmount + discount;
            const summaryDiv = document.getElementById('payment-summary');
            const summaryText = document.getElementById('summary-text');
            
            if (discount > 0) {
                summaryText.innerHTML = `
                    Toplam Borç: <strong>${totalDebt.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${symbol}</strong><br>
                    Yapılacak İndirim: <strong class="text-danger">${discount.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${symbol}</strong><br>
                    Ödenecek Tutar: <strong class="text-success">${paymentAmount.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${symbol}</strong>
                `;
                summaryDiv.style.display = 'block';
            } else {
                summaryDiv.style.display = 'none';
            }
        } else {
            document.getElementById('payment-summary').style.display = 'none';
        }
    }
    
    amountInput.addEventListener('input', calculatePayment);
    discountInput.addEventListener('input', calculatePayment);
    
    // İlk yüklemede sembolleri güncelle
    updateCurrencySymbols();
});

// Form validation
function validateForm() {
    console.log('validateForm çağrıldı');
    
    const customerId = document.getElementById('customer_id').value;
    const customerSearch = document.getElementById('customer_search').value;
    const amount = document.getElementById('amount').value;
    const collectionType = document.getElementById('collection_type').value;
    const currency = document.getElementById('currency').value;
    const transactionDate = document.getElementById('transaction_date').value;
    
    console.log('Form değerleri:', {
        customerId,
        customerSearch,
        amount,
        collectionType,
        currency,
        transactionDate
    });
    
    if (!customerId || customerId.trim() === '') {
        console.error('Müşteri ID eksik');
        alert('Lütfen bir müşteri seçin!');
        document.getElementById('customer_search').focus();
        return false;
    }
    
    if (!customerSearch || customerSearch.trim() === '') {
        console.error('Müşteri adı eksik');
        alert('Lütfen bir müşteri seçin!');
        document.getElementById('customer_search').focus();
        return false;
    }
    
    if (!amount || parseFloat(amount) <= 0) {
        console.error('Ödenecek tutar eksik veya geçersiz');
        alert('Lütfen geçerli bir ödeme tutarı girin!');
        document.getElementById('amount').focus();
        return false;
    }
    
    if (!collectionType || collectionType === '') {
        console.error('Tahsilat türü seçilmedi');
        alert('Lütfen bir tahsilat türü seçin!');
        document.getElementById('collection_type').focus();
        return false;
    }
    
    console.log('Form validation başarılı, submit ediliyor');
    return true;
}

// Form submit event listener
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action*="collections/store"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form submit event tetiklendi');
            const isValid = validateForm();
            if (!isValid) {
                console.error('Form validation başarısız, submit iptal edildi');
                e.preventDefault();
                return false;
            }
            console.log('Form submit ediliyor...');
        });
    }
});
</script>
@endsection
