@extends('layout.layout')

@section('title', 'Seri Ürünler')
@section('subTitle', 'Seri Bazlı Ürün Yönetimi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header">
                <h5 class="card-title mb-0">Seri Ürün Listesi</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('products.series.index') }}" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label mb-1">Kategori</label>
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            @php
                                $options = isset($allowedCategories) && is_array($allowedCategories) && count($allowedCategories) > 0
                                    ? $allowedCategories
                                    : ['Gömlek','Ceket','Takım Elbise','Pantalon'];
                            @endphp
                            <option value="">Tümü</option>
                            @foreach($options as $cat)
                                <option value="{{ $cat }}" {{ (request('category') ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex gap-2 align-items-center">
                        <h5 class="card-title mb-0">Seri Ürün Listesi</h5>
                        <button type="button" class="btn btn-danger btn-sm" id="deleteSelectedBtn" style="display:none;">
                            <iconify-icon icon="solar:trash-bin-minimalistic-outline" class="me-1"></iconify-icon>
                            Seçilenleri Sil (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                    <a href="{{ route('products.series.create') }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
                        <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                        Yeni Seri
                    </a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ri-check-line me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($series->count() > 0)
                    <div class="table-responsive">
                        <table class="table bordered-table mb-0 responsive-table" id="seriesTable">
                            <thead>
                                <tr>
                                    <th>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                            <label class="form-check-label" for="selectAll">Tümünü Seç</label>
                                        </div>
                                    </th>
                                    <th>Seri Adı</th>
                                    <th>SKU</th>
                                    <th>Seri Boyutu</th>
                                    <th>Stok (Seri)</th>
                                    <th>Toplam Ürün</th>
                                    <th>Maliyet</th>
                                    <th>Satış Fiyatı</th>
                                    <th>Durum</th>
                                    <th class="text-center">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($series as $serie)
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input row-checkbox" type="checkbox" value="{{ $serie->id }}" data-id="{{ $serie->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($serie->image)
                                                    <img src="{{ $serie->image_url }}?v={{ time() }}" alt="{{ $serie->name }}" 
                                                         class="rounded me-3" width="40" height="40">
                                                @else
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="ri-image-line text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">{{ $serie->name }}</h6>
                                                    @if($serie->category)
                                                        <small class="text-muted">{{ $serie->category }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $serie->sku ?? 'SKU Yok' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-primary mb-1">{{ $serie->series_size }}'li Seri</span>
                                                @if($serie->seriesItems->count() > 0)
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($serie->seriesItems as $item)
                                                            <span class="badge bg-info" style="font-size: 0.7rem; padding: 2px 6px;">{{ $item->size }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ number_format($serie->stock_quantity) }}</span>
                                            @if($serie->critical_stock > 0 && $serie->stock_quantity <= $serie->critical_stock)
                                                <i class="ri-alert-line text-danger ms-1" title="Kritik Stok"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-success">{{ number_format($serie->total_product_count) }}</span>
                                            <small class="text-muted d-block">Toplam Ürün</small>
                                        </td>
                                        <td>
                                            @php
                                                $currency = $serie->currency ?? 'TRY';
                                                $currencySymbol = $currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : '₺');
                                            @endphp
                                            <span class="fw-semibold">{{ number_format($serie->cost, 2) }} {{ $currencySymbol }}</span>
                                            @if($currency !== 'TRY')
                                                <br><small class="text-muted currency-convert" data-amount="{{ $serie->cost }}" data-currency="{{ $currency }}" data-type="cost">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-primary">{{ number_format($serie->price, 2) }} {{ $currencySymbol }}</span>
                                            @if($currency !== 'TRY')
                                                <br><small class="text-muted currency-convert" data-amount="{{ $serie->price }}" data-currency="{{ $currency }}" data-type="price">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($serie->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Pasif</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                                                        data-bs-toggle="dropdown">
                                                    <i class="ri-more-2-line"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('products.series.show', $serie) }}">
                                                            <i class="ri-eye-line me-2"></i>Görüntüle
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('products.series.edit', $serie) }}">
                                                            <i class="ri-edit-line me-2"></i>Düzenle
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('products.series.destroy', $serie) }}" 
                                                              method="POST" class="d-inline"
                                                              onsubmit="return confirm('Bu seriyi silmek istediğinizden emin misiniz?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="ri-delete-bin-line me-2"></i>Sil
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <small class="text-muted">
                                Toplam {{ $series->total() }} seri gösteriliyor
                            </small>
                        </div>
                        <div>
                            {{ $series->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="ri-inbox-line text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-muted">Henüz seri ürün eklenmemiş</h5>
                        <p class="text-muted">İlk seri ürününüzü eklemek için aşağıdaki butona tıklayın.</p>
                        <a href="{{ route('products.series.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i>Yeni Seri Ekle
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // DataTable initialization
    $('#seriesTable').DataTable({
        responsive: true,
        pageLength: 15,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json'
        },
        columnDefs: [
            { orderable: false, targets: [0, 9] }
        ]
    });

    // Select All functionality
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', this.checked);
        updateDeleteButton();
    });

    // Update delete button visibility
    function updateDeleteButton() {
        const checkedCount = $('.row-checkbox:checked').length;
        $('#selectedCount').text(checkedCount);
        
        if (checkedCount > 0) {
            $('#deleteSelectedBtn').show();
        } else {
            $('#deleteSelectedBtn').hide();
        }
    }

    // Row checkbox change
    $('.row-checkbox').on('change', function() {
        const totalCheckboxes = $('.row-checkbox').length;
        const checkedCheckboxes = $('.row-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
        updateDeleteButton();
    });

    // Delete selected items
    $('#deleteSelectedBtn').on('click', function() {
        const selectedIds = [];
        $('.row-checkbox:checked').each(function() {
            selectedIds.push($(this).data('id'));
        });

        if (selectedIds.length === 0) {
            alert('Lütfen silmek istediğiniz serileri seçin');
            return;
        }

        const confirmMessage = selectedIds.length === 1 
            ? 'Seçili seriyi silmek istediğinizden emin misiniz?' 
            : `Seçili ${selectedIds.length} seriyi silmek istediğinizden emin misiniz?`;

        if (confirm(confirmMessage)) {
            // Create a form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("products.series.bulk-delete") }}';
            
            // CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            // IDs
            const idsInput = document.createElement('input');
            idsInput.type = 'hidden';
            idsInput.name = 'ids';
            idsInput.value = JSON.stringify(selectedIds);
            form.appendChild(idsInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });

    // Currency conversion for all items
    const currencyElements = $('.currency-convert');
    if (currencyElements.length > 0) {
        $.get('{{ route("sales.invoices.currency.rates") }}')
            .done(function(response) {
                const rates = response.success ? response.rates : { 'USD': 41.29, 'EUR': 48.55 };
                
                currencyElements.each(function() {
                    const $el = $(this);
                    const amount = parseFloat($el.data('amount'));
                    const currency = $el.data('currency');
                    const exchangeRate = rates[currency] || 1;
                    const amountTRY = amount * exchangeRate;
                    $el.text('(' + amountTRY.toFixed(2).replace('.', ',') + ' ₺)');
                });
            })
            .fail(function() {
                currencyElements.text('(Kur alınamadı)');
            });
    }
});
</script>
@endpush
