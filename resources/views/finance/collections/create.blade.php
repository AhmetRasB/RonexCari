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
                <div class="col-lg-8 mx-auto">
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
                                                Ödeme Tutarı <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0.01" 
                                                       class="form-control @error('amount') is-invalid @enderror" 
                                                       id="amount" name="amount" 
                                                       value="{{ old('amount') }}" 
                                                       placeholder="Ödeme Tutarı" required>
                                                <span class="input-group-text" id="currency-symbol">₺</span>
                                            </div>
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

    // Eğer eski değer varsa göster
    @if(old('customer_id'))
        const selectedCustomer = @json($customers->find(old('customer_id')));
        if (selectedCustomer) {
            customerSearch.value = selectedCustomer.name + ' - ' + selectedCustomer.email;
        }
    @endif

    customerSearch.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            customerResults.style.display = 'none';
            customerId.value = '';
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
            customerId.value = item.dataset.id;
            customerSearch.value = item.dataset.name + ' - ' + item.dataset.email;
            customerResults.style.display = 'none';
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
    
    currencySelect.addEventListener('change', function() {
        const currency = this.value;
        switch(currency) {
            case 'TRY':
                currencySymbol.textContent = '₺';
                break;
            case 'USD':
                currencySymbol.textContent = '$';
                break;
            case 'EUR':
                currencySymbol.textContent = '€';
                break;
            default:
                currencySymbol.textContent = '₺';
        }
    });
});

// Form validation
function validateForm() {
    const customerId = document.getElementById('customer_id').value;
    const customerSearch = document.getElementById('customer_search').value;
    
    if (!customerId || customerId.trim() === '') {
        alert('Lütfen bir müşteri seçin!');
        document.getElementById('customer_search').focus();
        return false;
    }
    
    if (!customerSearch || customerSearch.trim() === '') {
        alert('Lütfen bir müşteri seçin!');
        document.getElementById('customer_search').focus();
        return false;
    }
    
    return true;
}
</script>
@endsection
