@extends('layout.layout')

@section('title', 'Hizmetler')
@section('subTitle', 'Hizmet Listesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Hizmet Listesi</h5>
                <a href="{{ route('services.create') }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
                    <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                    Yeni Hizmet
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
                            <th scope="col">Hizmet Adı</th>
                            <th scope="col">Hizmet Kodu</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Satış Fiyatı</th>
                            <th scope="col">Durum</th>
                            <th scope="col">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $index => $service)
                            <tr>
                                <td>
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" value="{{ $service->id }}">
                                        <label class="form-check-label">{{ $index + 1 }}</label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="w-40-px h-40-px bg-warning-light text-warning-600 rounded-circle d-flex align-items-center justify-content-center me-12">
                                            <iconify-icon icon="solar:settings-outline" class="text-lg"></iconify-icon>
                                        </div>
                                        <h6 class="text-md mb-0 fw-medium flex-grow-1">{{ $service->name }}</h6>
                                    </div>
                                </td>
                                <td>{{ $service->code ?? '-' }}</td>
                                <td>{{ $service->category ?? '-' }}</td>
                                <td>
                                    <span class="fw-semibold">{{ number_format($service->price, 2) }} {{ $service->currency ?? 'TRY' }}</span>
                                </td>
                                <td>
                                    <span class="bg-{{ $service->is_active ? 'success' : 'danger' }}-focus text-{{ $service->is_active ? 'success' : 'danger' }}-main px-24 py-4 rounded-pill fw-medium text-sm">
                                        {{ $service->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('services.show', $service) }}" class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                    </a>
                                    <a href="{{ route('services.edit', $service) }}" class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                    </a>
                                    <form action="{{ route('services.destroy', $service) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu hizmeti silmek istediğinizden emin misiniz?')">
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
                                <td colspan="7" class="text-center py-4">Henüz hizmet bulunmuyor.</td>
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
$(document).ready(function() {
    // Destroy existing DataTable if exists
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().destroy();
    }

    // Initialize DataTable with responsive configuration
    $('#dataTable').DataTable({
        responsive: {
            details: {
                type: 'column',
                target: 'tr'
            }
        },
        scrollX: true,
        scrollCollapse: true,
        paging: true,
        pageLength: 10,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json'
        },
        columnDefs: [
            { "className": "control", "orderable": false, "targets": 0 }, // Checkbox column with control
            { "responsivePriority": 1, "targets": 6 }, // Actions - always visible
            { "responsivePriority": 2, "targets": 1 }, // Name - high priority
            { "responsivePriority": 3, "targets": 4 }, // Price - high priority
            { "responsivePriority": 4, "targets": 5 }, // Status - medium priority
            { "responsivePriority": 10000, "targets": [2, 3] } // Hide these first on small screens
        ]
    });

    // Select all functionality
    $('#selectAll').change(function() {
        $('tbody input[type="checkbox"]').prop('checked', this.checked);
    });

    // Individual checkbox change
    $('tbody').on('change', 'input[type="checkbox"]', function() {
        if (!this.checked) {
            $('#selectAll').prop('checked', false);
        }
    });
});
</script>
@endpush
@endsection
