@extends('layout.layout')

@section('title', 'Ürünler')
@section('subTitle', 'Ürün Listesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Ürün Listesi</h5>
                <a href="{{ route('products.index.create') }}" class="btn btn-primary">
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
                            <th   scope="col">
                                <div class="form-check style-check d-flex align-items-center">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                    <label class="form-check-label">S.L</label>
                                </div>
                            </th>
                            <th scope="col">Ürün Adı</th>
                            <th scope="col">SKU</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Fiyat</th>
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
                                        <div class="w-40-px h-40-px bg-info-light text-info-600 rounded-circle d-flex align-items-center justify-content-center me-12">
                                            <iconify-icon icon="solar:box-outline" class="text-lg"></iconify-icon>
                                        </div>
                                        <h6 class="text-md mb-0 fw-medium flex-grow-1">{{ $product->name }}</h6>
                                    </div>
                                </td>
                                <td><span class="text-primary-600 fw-medium">{{ $product->sku ?? '-' }}</span></td>
                                <td>{{ $product->category ?? '-' }}</td>
                                <td><span class="fw-semibold">₺{{ number_format($product->price, 2) }}</span></td>
                                <td>
                                    <span class="badge {{ ($product->stock_quantity ?? 0) > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $product->stock_quantity ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="bg-{{ $product->is_active ? 'success' : 'danger' }}-focus text-{{ $product->is_active ? 'success' : 'danger' }}-main px-24 py-4 rounded-pill fw-medium text-sm">
                                        {{ $product->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('products.index.show', $product) }}" class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                    </a>
                                    <a href="{{ route('products.index.edit', $product) }}" class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                    </a>
                                    <form action="{{ route('products.index.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu ürünü silmek istediğinizden emin misiniz?')">
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
                                <td colspan="8" class="text-center py-4">Henüz ürün bulunmuyor.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Destroy existing DataTable if exists
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().destroy();
    }

    let table = new DataTable('#dataTable', {
        responsive: {
            details: {
                type: 'column',
                target: 'tr'
            }
        },
        scrollX: true,
        scrollCollapse: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json'
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        columnDefs: [
            { "className": "control", "orderable": false, "targets": 0 }, // Checkbox column with control
            { "responsivePriority": 1, "targets": 7 }, // Actions - always visible
            { "responsivePriority": 2, "targets": 1 }, // Name - high priority
            { "responsivePriority": 3, "targets": 4 }, // Price - high priority
            { "responsivePriority": 4, "targets": 6 }, // Status - medium priority
            { "responsivePriority": 10000, "targets": [2, 3, 5] } // Hide these first on small screens
        ]
    });

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
