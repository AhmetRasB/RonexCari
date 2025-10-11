@extends('layout.layout')

@section('title', 'Ürünler')
@section('subTitle', 'Ürün Listesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Ürün Listesi</h5>
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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Tekli Ürün Listesi</h5>
                    <a href="{{ route('products.create') }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
                        <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                        Yeni Ürün
                    </a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ri-check-line me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($products->count() > 0)
                    <div class="table-responsive">
                        <table class="table bordered-table mb-0 responsive-table" id="productsTable">
                            <thead>
                                <tr>
                                    <th>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                            <label class="form-check-label" for="selectAll">Tümünü Seç</label>
                                        </div>
                                    </th>
                                    <th>Ürün Adı</th>
                                    <th>SKU</th>
                                    <th>Renk</th>
                                    <th>Birim</th>
                                    <th>Maliyet</th>
                                    <th>Satış Fiyatı</th>
                                    <th>Stok</th>
                                    <th>Durum</th>
                                    <th class="text-center">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $product->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($product->image)
                                                    <img src="{{ $product->image_url }}?v={{ time() }}" alt="{{ $product->name }}" 
                                                         class="rounded me-3" width="40" height="40">
                                                @else
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="ri-image-line text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">{{ $product->name }}</h6>
                                                    @if($product->description)
                                                        <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $product->sku ?? 'SKU Yok' }}</span>
                                        </td>
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
                                        <td>
                                            <span class="badge bg-info">{{ $product->unit ?? 'Adet' }}</span>
                                        </td>
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
                                            @if($product->is_active)
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
                                                        <a class="dropdown-item" href="{{ route('products.show', $product) }}">
                                                            <i class="ri-eye-line me-2"></i>Görüntüle
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('products.edit', $product) }}">
                                                            <i class="ri-edit-line me-2"></i>Düzenle
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('products.destroy', $product) }}" 
                                                              method="POST" class="d-inline"
                                                              onsubmit="return confirm('Bu ürünü silmek istediğinizden emin misiniz?')">
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

@push('scripts')
<script>
$(document).ready(function() {
    // DataTable initialization
    $('#productsTable').DataTable({
        responsive: true,
        pageLength: 15,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json'
        },
        columnDefs: [
            { orderable: false, targets: [0, 8] }
        ]
    });

    // Select All functionality
    $('#selectAll').on('change', function() {
        $('tbody input[type="checkbox"]').prop('checked', this.checked);
    });

    $('tbody input[type="checkbox"]').on('change', function() {
        const totalCheckboxes = $('tbody input[type="checkbox"]').length;
        const checkedCheckboxes = $('tbody input[type="checkbox"]:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
});
</script>
@endpush