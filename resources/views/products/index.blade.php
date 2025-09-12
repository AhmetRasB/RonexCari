@extends('layout.layout')

@section('title', 'Ürünler')
@section('subTitle', 'Ürün Listesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Ürün Listesi</h5>
                <a href="{{ route('products.create') }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
                    <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                    Yeni Ürün
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table bordered-table mb-0 responsive-table" id="dataTable" data-page-length='10' >
                    <thead>
                        <tr>
                            <th scope="col">
                                <div class="form-check style-check d-flex align-items-center">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                    <label class="form-check-label">S.L</label>
                                </div>
                            </th>
                            <th scope="col">Ürün Adı</th>
                            <th scope="col">Ürün Kodu</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Beden</th>
                            <th scope="col">Renk</th>
                            <th scope="col">Fiyatlar</th>
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
                                        <input class="form-check-input" type="checkbox" value="{{ $product->id }}">
                                        <label class="form-check-label">{{ $index + 1 }}</label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($product->image)
                                            <div class="w-40-px h-40-px me-12">
                                                <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover rounded cursor-pointer product-image-thumbnail" data-bs-toggle="modal" data-bs-target="#imageModal{{ $product->id }}" style="cursor: pointer;">
                                            </div>
                                        @else
                                            <div class="w-40-px h-40-px bg-primary-light text-primary-600 rounded-circle d-flex align-items-center justify-content-center me-12">
                                                <iconify-icon icon="solar:box-outline" class="text-lg"></iconify-icon>
                                            </div>
                                        @endif
                                        <h6 class="text-md mb-0 fw-medium flex-grow-1">{{ $product->name }}</h6>
                                    </div>
                                </td>
                                <td>{{ $product->product_code ?? '-' }}</td>
                                <td>{{ $product->category ?? '-' }}</td>
                                <td>
                                    @if($product->size)
                                        <span class="btn btn-neutral-100 text-neutral-600 radius-8 px-32 py-11">{{ $product->size }}</span>
                                    @else
                                        <span class="text-secondary-light">-</span>
                                    @endif
                                </td>
                                <td>{{ $product->color ?? '-' }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="mb-1">
                                            <small class="text-muted">Satış:</small>
                                            <span class="fw-semibold text-success">{{ number_format($product->sale_price, 2) }} {{ $product->currency }}</span>
                                        </div>
                                        @if($product->purchase_price)
                                            <div>
                                                <small class="text-muted">Alış:</small>
                                                <span class="fw-medium text-info">{{ number_format($product->purchase_price, 2) }} {{ $product->currency }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $product->initial_stock ?? 0 }} {{ $product->unit }}</span>
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
            </div>
        </div>
    </div>
</div>

<!-- Image Modals -->
@foreach($products as $product)
    @if($product->image)
    <div class="modal fade" id="imageModal{{ $product->id }}" tabindex="-1" aria-labelledby="imageModalLabel{{ $product->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel{{ $product->id }}">{{ $product->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded shadow" style="max-width: 100%; max-height: 70vh;">
                    <div class="mt-3">
                        <small class="text-muted">{{ $product->product_code ?? 'Kod yok' }} • {{ $product->category ?? 'Kategori yok' }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                    <a href="{{ route('products.show', $product) }}" class="btn btn-primary">Detayları Görüntüle</a>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

@push('styles')
<style>
    .product-image-thumbnail {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .product-image-thumbnail:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    /* Sadece gerçekten küçük ekranlarda responsive davranış */
    @media (max-width: 992px) {
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            min-width: 800px;
        }
    }
    
    /* Tablet cihazlarda */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .table td, .table th {
            padding: 0.5rem 0.25rem;
            white-space: nowrap;
        }
        
        /* Küçük ekranlarda buton boyutları */
        .w-32-px {
            width: 24px !important;
            height: 24px !important;
        }
        
        /* Badge boyutları */
        .badge {
            font-size: 0.7rem;
        }
    }
    
    /* Mobil cihazlar için */
    @media (max-width: 576px) {
        .table-responsive {
            font-size: 0.75rem;
        }
        
        .table td, .table th {
            padding: 0.25rem 0.125rem;
        }
        
        /* Ürün adı sütununu daha geniş yap */
        .table td:nth-child(2) {
            min-width: 150px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let table = new DataTable('#dataTable');

    // Select all functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
</script>
@endpush
@endsection
