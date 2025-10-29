@extends('layout.layout')

@section('title', 'Ürünler')
@section('subTitle', 'Ürün Listesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2 align-items-center">
                    <h5 class="card-title mb-0">Tekli Ürün Listesi</h5>
                    <button type="button" class="btn btn-danger btn-sm" id="deleteSelectedBtn" style="display:none;">
                        <iconify-icon icon="solar:trash-bin-minimalistic-outline" class="me-1"></iconify-icon>
                        Seçilenleri Sil (<span id="selectedCount">0</span>)
                    </button>
                </div>
                <a href="{{ route('products.create') }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
                    <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                    Yeni Ürün
                </a>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('products.index') }}" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label mb-1">Kategori</label>
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            @php
                                // Prefer the controller-provided allowedCategories for consistency
                                $options = isset($allowedCategories) && is_array($allowedCategories) && count($allowedCategories) > 0
                                    ? $allowedCategories
                                    : ['Gömlek','Ceket','Takım Elbise','Pantalon'];
                            @endphp
                            <option value="">Tümü</option>
                            @foreach($options as $cat)
                                <option value="{{ $cat }}" {{ ($selectedCategory ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ri-check-line me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($products->count() > 0)
                    <div class="table-responsive">
                    <table class="table bordered-table mb-0 responsive-table" id="productsTable" data-page-length='10'>
                        <thead>
                            <tr>
                                <th scope="col">
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                        <label class="form-check-label">S.L</label>
                                    </div>
                                </th>
                                <th scope="col">Ürün Adı</th>
                                <th scope="col">SKU</th>
                                <th scope="col">Renk</th>
                                <th scope="col">Birim</th>
                                <th scope="col">Maliyet</th>
                                <th scope="col">Satış Fiyatı</th>
                                <th scope="col">Stok</th>
                                <th scope="col">Durum</th>
                                <th scope="col">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $index => $product)
                                <tr>
                                    <td>
                                        <div class="form-check style-check d-flex align-items-center">
                                            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $product->id }}" data-id="{{ $product->id }}">
                                            <label class="form-check-label">{{ $index + 1 }}</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($product->image)
                                                <img src="{{ $product->image_url }}?v={{ time() }}" alt="{{ $product->name }}" 
                                                     class="rounded me-3" width="40" height="40">
                                            @else
                                                <div class="w-40-px h-40-px bg-primary-light text-primary-600 rounded-circle d-flex align-items-center justify-content-center me-12">
                                                    <iconify-icon icon="solar:box-outline" class="text-lg"></iconify-icon>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="text-md mb-0 fw-medium flex-grow-1">{{ $product->name }}</h6>
                                                @if($product->description)
                                                    <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $product->sku ?? 'SKU Yok' }}</td>
                                    <td>
                                        @if($product->color)
                                            <span class="badge" style="background:#e9f7ef; color:#198754; border:1px solid #c3e6cb;">
                                                {{ $product->color }}
                                            </span>
                                        @elseif($product->colorVariants && $product->colorVariants->count() > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($product->colorVariants->take(3) as $variant)
                                                    <span class="badge" style="background:#e9f7ef; color:#198754; border:1px solid #c3e6cb;">
                                                        {{ $variant->color }}
                                                    </span>
                                                @endforeach
                                                @if($product->colorVariants->count() > 3)
                                                    <span class="badge bg-secondary">+{{ $product->colorVariants->count() - 3 }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $product->unit ?? 'Adet' }}</td>
                                    <td>
                                        <span class="fw-semibold">₺{{ number_format($product->cost ?? 0, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-primary">₺{{ number_format($product->price ?? 0, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($product->colorVariants && $product->colorVariants->count() > 0)
                                            <span class="fw-semibold">{{ $product->total_stock }}</span>
                                            @if($product->colorVariants->filter(function($v){ return $v->critical_stock > 0 && $v->stock_quantity <= $v->critical_stock; })->count() > 0)
                                                <i class="ri-alert-line text-danger ms-1" title="Kritik Stok (Renk Varyantları)"></i>
                                            @endif
                                        @else
                                            <span class="fw-semibold">{{ number_format($product->initial_stock ?? 0) }}</span>
                                            @if($product->critical_stock > 0 && $product->initial_stock <= $product->critical_stock)
                                                <i class="ri-alert-line text-danger ms-1" title="Kritik Stok"></i>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <span class="bg-{{ $product->is_active ? 'success' : 'danger' }}-focus text-{{ $product->is_active ? 'success' : 'danger' }}-main px-24 py-4 rounded-pill fw-medium text-sm">
                                            {{ $product->is_active ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('products.show', $product) }}" class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center">
                                            <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}" class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                            <iconify-icon icon="lucide:edit"></iconify-icon>
                                        </a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu ürünü silmek istediğinizden emin misiniz?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0">
                                                <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">Henüz ürün bulunmuyor.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <small class="text-muted">
                                Toplam {{ $products->total() }} ürün gösteriliyor
                            </small>
                        </div>
                        <div>
                            {{ $products->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="ri-inbox-line text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-muted">Henüz ürün eklenmemiş</h5>
                        <p class="text-muted">İlk ürününüzü eklemek için aşağıdaki butona tıklayın.</p>
                        <a href="{{ route('products.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i>Yeni Ürün Ekle
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

<style>
/* Checkbox alanı için kompakt tasarım */
#productsTable th:first-child,
#productsTable td:first-child {
    width: 50px !important;
    min-width: 50px !important;
    max-width: 50px !important;
    padding: 8px 4px !important;
}

#productsTable th:first-child .form-check-label {
    font-size: 0.7rem !important;
    white-space: nowrap;
}

/* Ürün sayfası için hoş tasarım */
#productsTable th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding: 15px 12px;
}

#productsTable td {
    padding: 15px 12px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
}

#productsTable tbody tr:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

/* Ürün adı için daha büyük font */
#productsTable td:nth-child(2) h6 {
    font-size: 1rem;
    font-weight: 600;
    color: #212529;
}

#productsTable td:nth-child(2) small {
    font-size: 0.8rem;
    color: #6c757d;
}

/* Badge'ler için daha hoş görünüm */
#productsTable .badge {
    font-size: 0.75rem;
    padding: 6px 10px;
    border-radius: 6px;
    font-weight: 500;
}

/* SKU için daha küçük badge */
#productsTable td:nth-child(3) .badge {
    font-size: 0.7rem;
    padding: 4px 8px;
}

/* Renk badge'leri için daha küçük */
#productsTable td:nth-child(4) .badge {
    font-size: 0.7rem;
    padding: 3px 6px;
    margin: 1px;
}

/* Fiyat alanları için daha büyük font */
#productsTable td:nth-child(6),
#productsTable td:nth-child(7) {
    font-size: 1rem;
    font-weight: 600;
}

/* Stok alanı için daha büyük font */
#productsTable td:nth-child(8) {
    font-size: 1rem;
    font-weight: 600;
}

/* Durum badge'i için daha büyük */
#productsTable td:nth-child(9) .badge {
    font-size: 0.8rem;
    padding: 8px 12px;
}

/* İşlemler butonu için daha büyük */
#productsTable td:nth-child(10) .btn {
    padding: 8px 12px;
    font-size: 0.8rem;
}

/* Responsive için */
@media (max-width: 768px) {
    #productsTable th,
    #productsTable td {
        padding: 10px 8px;
        font-size: 0.9rem;
    }
    
    #productsTable td:nth-child(2) h6 {
        font-size: 0.9rem;
    }
    
    #productsTable .badge {
        font-size: 0.7rem;
        padding: 4px 6px;
    }
    
    /* Checkbox alanını mobilde daha da küçült */
    #productsTable th:first-child,
    #productsTable td:first-child {
        width: 40px !important;
        min-width: 40px !important;
        max-width: 40px !important;
        padding: 6px 2px !important;
    }
    
    #productsTable th:first-child .form-check-label {
        font-size: 0.6rem !important;
    }
}
</style>

@push('scripts')
<script>
$(document).ready(function() {
    let table = new DataTable('#productsTable');

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
            alert('Lütfen silmek istediğiniz ürünleri seçin');
            return;
        }

        const confirmMessage = selectedIds.length === 1 
            ? 'Seçili ürünü silmek istediğinizden emin misiniz?' 
            : `Seçili ${selectedIds.length} ürünü silmek istediğinizden emin misiniz?`;

        if (confirm(confirmMessage)) {
            // Create a form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("products.bulk-delete") }}';
            
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
});
</script>
@endpush