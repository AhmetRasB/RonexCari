@extends('layout.layout')

@section('title', 'Masraflar')
@section('subTitle', 'Masraf Listesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Masraf Listesi</h5>
                <div class="d-flex gap-2">
                    <button id="deleteSelectedBtn" class="btn btn-danger-100 text-danger-600 radius-8 px-20 py-11" style="display: none;">
                        <iconify-icon icon="solar:trash-bin-minimalistic-outline" class="me-2"></iconify-icon>
                        Seçilenleri Sil (<span id="selectedCount">0</span>)
                    </button>
                    <a href="{{ route('expenses.expenses.create') }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
                        <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                        Yeni Masraf
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
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
                            <th scope="col">Masraf Adı</th>
                            <th scope="col">Mağaza</th>
                            <th scope="col">Personel</th>
                            <th scope="col">Açıklama</th>
                            <th scope="col">Tutar</th>
                            <th scope="col">Tarih</th>
                            <th scope="col">Ödeme Durumu</th>
                            <th scope="col">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $index => $expense)
                            <tr>
                                <td>
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input row-checkbox" type="checkbox" data-id="{{ $expense->id }}">
                                        <label class="form-check-label">{{ $index + 1 }}</label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="w-40-px h-40-px bg-warning-light text-warning-600 rounded-circle d-flex align-items-center justify-content-center me-12">
                                            <iconify-icon icon="solar:wallet-money-outline" class="text-lg"></iconify-icon>
                                        </div>
                                        <h6 class="text-md mb-0 fw-medium flex-grow-1">{{ $expense->name }}</h6>
                                    </div>
                                </td>
                                <td>
                                    @if($expense->account)
                                        <span class="badge {{ $expense->account->code === 'ronex1' ? 'bg-primary-100 text-primary-600' : 'bg-success-100 text-success-600' }} px-2 py-1 rounded-pill text-xs fw-medium">
                                            {{ $expense->account->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-gray-100 text-gray-600 px-2 py-1 rounded-pill text-xs fw-medium">
                                            Mağaza Yok
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($expense->user)
                                        <div class="d-flex align-items-center">
                                            <div class="w-8 h-8 bg-info-100 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <iconify-icon icon="heroicons:user" class="text-info-600 text-sm"></iconify-icon>
                                            </div>
                                            <div>
                                                <h6 class="text-sm mb-0 fw-medium">{{ $expense->user->name }}</h6>
                                                <small class="text-secondary-light">{{ $expense->user->email }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Bilinmiyor</span>
                                    @endif
                                </td>
                                <td>{{ $expense->description ? Str::limit($expense->description, 50) : '-' }}</td>
                                <td>
                                    <span class="text-primary-600 fw-semibold">{{ number_format($expense->amount, 2) }} ₺</span>
                                </td>
                                <td>{{ $expense->expense_date->format('d.m.Y') }}</td>
                                <td>
                                    <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">
                                        Kaydedildi
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('expenses.expenses.show', $expense) }}" class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                    </a>
                                    <a href="{{ route('expenses.expenses.edit', $expense) }}" class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                    </a>
                                    <form action="{{ route('expenses.expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu masrafı silmek istediğinizden emin misiniz?')">
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
                                <td colspan="9" class="text-center py-4">Henüz masraf bulunmuyor.</td>
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
            { "orderable": false, "targets": 0 }, // Checkbox column
            { "orderable": false, "targets": 8 }, // Actions column
            { "responsivePriority": 1, "targets": 8 }, // Actions - always visible
            { "responsivePriority": 2, "targets": 1 }, // Name - high priority  
            { "responsivePriority": 3, "targets": 5 }, // Amount - high priority
            { "responsivePriority": 4, "targets": 6 }, // Date - medium priority
            { "responsivePriority": 5, "targets": 7 }, // Status - medium priority
            { "responsivePriority": 10, "targets": 2 }, // Store - low priority
            { "responsivePriority": 11, "targets": 3 }, // User - low priority
            { "responsivePriority": 12, "targets": 4 } // Description - lowest priority
        ]
    });

    // Select all functionality
    $('#selectAll').change(function() {
        $('.row-checkbox').prop('checked', this.checked);
        updateDeleteButton();
    });

    // Individual checkbox change
    $('tbody').on('change', '.row-checkbox', function() {
        if (!this.checked) {
            $('#selectAll').prop('checked', false);
        }
        updateDeleteButton();
    });

    // Update delete button visibility
    function updateDeleteButton() {
        const checkedBoxes = $('.row-checkbox:checked');
        const deleteBtn = $('#deleteSelectedBtn');
        const countSpan = $('#selectedCount');
        
        if (checkedBoxes.length > 0) {
            deleteBtn.show();
            countSpan.text(checkedBoxes.length);
        } else {
            deleteBtn.hide();
        }
    }

    // Delete selected
    $('#deleteSelectedBtn').click(function() {
        const checkedBoxes = $('.row-checkbox:checked');
        const ids = checkedBoxes.map(function() { return $(this).data('id'); }).get();
        
        if (ids.length === 0) return;
        
        if (confirm(ids.length + ' gideri silmek istediğinizden emin misiniz?')) {
            const form = $('<form>', {
                method: 'POST',
                action: '{{ route("expenses.expenses.bulk-delete") }}'
            });
            
            form.append($('<input>', {
                type: 'hidden',
                name: '_token',
                value: '{{ csrf_token() }}'
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'ids',
                value: JSON.stringify(ids)
            }));
            
            $('body').append(form);
            form.submit();
        }
    });
});
</script>
@endpush
@endsection
