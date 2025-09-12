@extends('layout.layout')

@section('title', 'Çalışanlar')
@section('subTitle', 'Çalışan Listesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Çalışan Listesi</h5>
                <a href="{{ route('expenses.employees.create') }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
                    <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                    Yeni Çalışan
                </a>
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
                            <th scope="col">Ad Soyad</th>
                            <th scope="col">Telefon</th>
                            <th scope="col">Aylık Maaş</th>
                            <th scope="col">Maaş Günü</th>
                            <th scope="col">Biriken Maaş</th>
                            <th scope="col">Ödenen</th>
                            <th scope="col">Kalan</th>
                            <th scope="col">Durum</th>
                            <th scope="col">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $index => $employee)
                            <tr>
                                <td>
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" value="{{ $employee->id }}">
                                        <label class="form-check-label">{{ $index + 1 }}</label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="w-40-px h-40-px bg-primary-light text-primary-600 rounded-circle d-flex align-items-center justify-content-center me-12">
                                            <iconify-icon icon="solar:user-outline" class="text-lg"></iconify-icon>
                                        </div>
                                        <div>
                                            <h6 class="text-md mb-0 fw-medium">{{ $employee->name }}</h6>
                                            @if($employee->emergency_contact_name)
                                                <small class="text-muted">Yakın: {{ $employee->emergency_contact_name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $employee->phone ?? '-' }}</td>
                                <td>
                                    <span class="text-primary-600 fw-semibold">{{ number_format($employee->monthly_salary, 2) }} ₺</span>
                                </td>
                                <td>
                                    <span class="bg-info-focus text-info-main px-24 py-4 rounded-pill fw-medium text-sm">{{ $employee->salary_day }}</span>
                                </td>
                                <td>
                                    <span class="text-warning-600 fw-semibold">{{ number_format($employee->accumulated_salary, 2) }} ₺</span>
                                </td>
                                <td>
                                    <span class="text-success-600 fw-semibold">{{ number_format($employee->paid_amount, 2) }} ₺</span>
                                </td>
                                <td>
                                    @if($employee->remaining_salary > 0)
                                        <span class="text-danger-600 fw-semibold">{{ number_format($employee->remaining_salary, 2) }} ₺</span>
                                    @else
                                        <span class="text-success-600 fw-semibold">0 ₺</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="bg-{{ $employee->is_active ? 'success' : 'danger' }}-focus text-{{ $employee->is_active ? 'success' : 'danger' }}-main px-24 py-4 rounded-pill fw-medium text-sm">
                                        {{ $employee->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                    @if($employee->salary_status === 'pending')
                                        <br><span class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm mt-1">Maaş Bekliyor</span>
                                    @elseif($employee->salary_status === 'paid')
                                        <br><span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm mt-1">Ödendi</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('expenses.employees.show', $employee) }}" class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                    </a>
                                    <a href="{{ route('expenses.employees.edit', $employee) }}" class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                    </a>
                                    @if($employee->remaining_salary > 0)
                                        <button type="button" class="w-32-px h-32-px bg-warning-focus text-warning-main rounded-circle d-inline-flex align-items-center justify-content-center border-0" data-bs-toggle="modal" data-bs-target="#paySalaryModal{{ $employee->id }}">
                                            <iconify-icon icon="solar:dollar-minimalistic-outline"></iconify-icon>
                                        </button>
                                    @endif
                                    <form action="{{ route('expenses.employees.destroy', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu çalışanı silmek istediğinizden emin misiniz?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0">
                                            <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Pay Salary Modal -->
                            <div class="modal fade" id="paySalaryModal{{ $employee->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Maaş Ödemesi</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('expenses.employees.paySalary', $employee) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Çalışan</label>
                                                    <p class="form-control-plaintext fw-bold">{{ $employee->name }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Kalan Borç</label>
                                                    <p class="form-control-plaintext text-danger fw-bold">{{ number_format($employee->remaining_salary, 2) }} ₺</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="amount{{ $employee->id }}" class="form-label">Ödeme Tutarı (₺) <span class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" min="0" max="{{ $employee->remaining_salary }}" 
                                                           class="form-control" id="amount{{ $employee->id }}" name="amount" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                <button type="submit" class="btn btn-success">Ödeme Yap</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">Henüz çalışan bulunmuyor.</td>
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
            { "responsivePriority": 1, "targets": 9 }, // Actions - always visible
            { "responsivePriority": 2, "targets": 1 }, // Name - high priority
            { "responsivePriority": 3, "targets": 3 }, // Monthly Salary - high priority
            { "responsivePriority": 4, "targets": 8 }, // Status - medium priority
            { "responsivePriority": 10000, "targets": [2, 4, 5, 6, 7] } // Hide these first on small screens
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
