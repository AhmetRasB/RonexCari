@extends('layout.layout')

@section('title', 'Tahsilatlar')
@section('subTitle', 'Tahsilat Listesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tahsilat Listesi</h5>
                <div class="d-flex gap-2">
                    <button id="deleteSelectedBtn" class="btn btn-danger-100 text-danger-600 radius-8 px-20 py-11" style="display: none;">
                        <iconify-icon icon="solar:trash-bin-minimalistic-outline" class="me-2"></iconify-icon>
                        Seçilenleri Sil (<span id="selectedCount">0</span>)
                    </button>
                    <a href="{{ route('finance.collections.create') }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
                        <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                        Yeni Tahsilat
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
                            <th scope="col">Müşteri</th>
                            <th scope="col">Tahsilat Türü</th>
                            <th scope="col">Tutar</th>
                            <th scope="col">Para Birimi</th>
                            <th scope="col">Tarih</th>
                            <th scope="col">Durum</th>
                            <th scope="col">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($collections as $index => $collection)
                            <tr>
                                <td>
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input row-checkbox" type="checkbox" data-id="{{ $collection->id }}">
                                        <label class="form-check-label">{{ $index + 1 }}</label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs">
                                                <div class="avatar-title bg-soft-primary text-primary rounded-circle fs-16">
                                                    <iconify-icon icon="solar:user-outline"></iconify-icon>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">{{ $collection->customer->name ?? 'Müşteri Silinmiş' }}</h6>
                                            <p class="text-muted mb-0">{{ $collection->customer->email ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-soft-info text-info">
                                        <iconify-icon icon="solar:wallet-outline" class="me-1"></iconify-icon>
                                        {{ $collection->collection_type_text }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ number_format($collection->amount, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-soft-secondary text-secondary">
                                        {{ $collection->currency }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $collection->transaction_date->format('d.m.Y') }}</span>
                                </td>
                                <td>
                                    @if($collection->is_active)
                                        <span class="badge bg-soft-success text-success">
                                            <iconify-icon icon="solar:check-circle-outline" class="me-1"></iconify-icon>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-soft-danger text-danger">
                                            <iconify-icon icon="solar:close-circle-outline" class="me-1"></iconify-icon>
                                            Pasif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <iconify-icon icon="solar:menu-dots-outline"></iconify-icon>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('finance.collections.show', $collection) }}">
                                                    <iconify-icon icon="solar:eye-outline" class="me-2"></iconify-icon>
                                                    Görüntüle
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('finance.collections.edit', $collection) }}">
                                                    <iconify-icon icon="solar:pen-outline" class="me-2"></iconify-icon>
                                                    Düzenle
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('finance.collections.print', $collection) }}" target="_blank">
                                                    <iconify-icon icon="solar:printer-outline" class="me-2"></iconify-icon>
                                                    Yazdır
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('finance.collections.destroy', $collection) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Bu tahsilatı silmek istediğinizden emin misiniz?')">
                                                        <iconify-icon icon="solar:trash-bin-outline" class="me-2"></iconify-icon>
                                                        Sil
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <iconify-icon icon="solar:wallet-money-outline" class="text-muted" style="font-size: 48px;"></iconify-icon>
                                        <h6 class="text-muted mt-2">Henüz tahsilat kaydı bulunmuyor</h6>
                                        <p class="text-muted">İlk tahsilatınızı eklemek için yukarıdaki butonu kullanın.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let table = new DataTable('#dataTable', {
        pageLength: 10,
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json'
        },
        drawCallback: function() {
            // Re-attach event handlers after DataTables redraws
            updateDeleteButton();
        }
    });

    // Select all functionality - use event delegation for DataTables
    $(document).on('change', '#selectAll', function() {
        $('.row-checkbox').prop('checked', this.checked);
        updateDeleteButton();
    });

    // Individual checkbox change - use event delegation
    $(document).on('change', '.row-checkbox', function() {
        const totalCheckboxes = $('.row-checkbox').length;
        const checkedCheckboxes = $('.row-checkbox:checked').length;
        
        // Update select all checkbox
        if (checkedCheckboxes === 0) {
            $('#selectAll').prop('checked', false);
            $('#selectAll').prop('indeterminate', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#selectAll').prop('checked', true);
            $('#selectAll').prop('indeterminate', false);
        } else {
            $('#selectAll').prop('indeterminate', true);
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
            countSpan.text(0);
        }
    }

    // Delete selected - use event delegation
    $(document).on('click', '#deleteSelectedBtn', function(e) {
        e.preventDefault();
        
        const checkedBoxes = $('.row-checkbox:checked');
        const ids = checkedBoxes.map(function() { 
            return $(this).data('id'); 
        }).get();
        
        if (ids.length === 0) {
            alert('Lütfen silmek istediğiniz tahsilatları seçin.');
            return;
        }
        
        if (confirm(ids.length + ' tahsilatı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
            const form = $('<form>', {
                method: 'POST',
                action: '{{ route("finance.collections.bulk-delete") }}'
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
    
    // Initialize delete button state
    updateDeleteButton();
});
</script>
@endpush
