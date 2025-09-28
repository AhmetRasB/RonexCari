@extends('layout.layout')

@section('title', 'Çalışanlar')
@section('subTitle', 'Çalışan Listesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Çalışan Listesi</h5>
                <a href="{{ route('management.employees.create') }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
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
                            <th scope="col">İsim</th>
                            <th scope="col">Email</th>
                            <th scope="col">Telefon</th>
                            <th scope="col">Pozisyon</th>
                            <th scope="col">Departman</th>
                            <th scope="col">Maaş</th>
                            <th scope="col">İşe Başlama</th>
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
                                        <div class="w-8 h-8 bg-primary-100 rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <iconify-icon icon="heroicons:user" class="text-primary-600 text-sm"></iconify-icon>
                                        </div>
                                        <div>
                                            <h6 class="text-sm mb-0 fw-medium">{{ $employee->name }}</h6>
                                            <small class="text-secondary-light">{{ $employee->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-md mb-0">{{ $employee->email }}</span>
                                </td>
                                <td>
                                    <span class="text-md mb-0">{{ $employee->phone ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="text-md mb-0">{{ $employee->position ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="text-md mb-0">{{ $employee->department ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $employee->salary ? number_format($employee->salary, 2) . ' ₺' : '-' }}</span>
                                </td>
                                <td>{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('d.m.Y') : '-' }}</td>
                                <td>
                                    <span class="badge {{ $employee->is_active ? 'bg-success-100 text-success-600' : 'bg-danger-100 text-danger-600' }} px-2 py-1 rounded-pill text-xs fw-medium">
                                        {{ $employee->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="w-32-px h-32-px bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="İşlemler">
                                            <iconify-icon icon="solar:menu-dots-bold"></iconify-icon>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('management.employees.show', $employee) }}">Görüntüle</a></li>
                                            <li><a class="dropdown-item" href="{{ route('management.employees.edit', $employee) }}">Düzenle</a></li>
                                            <li><a class="dropdown-item" href="{{ route('management.employees.salary-payments.show', $employee) }}">Maaş Ödemeleri</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="openSalaryPaymentModal({{ $employee->id }}, '{{ $employee->name }}', {{ $employee->salary ?? 0 }}, '{{ $employee->hire_date }}')">Maaş Öde</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('management.employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Bu çalışanı silmek istediğinizden emin misiniz?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">Sil</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
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

<!-- Salary Payment Modal -->
<div class="modal fade" id="salaryPaymentModal" tabindex="-1" aria-labelledby="salaryPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="salaryPaymentModalLabel">Maaş Ödemesi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="salaryPaymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Employee Info -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Çalışan Bilgileri</h6>
                                    <p class="mb-1"><strong>İsim:</strong> <span id="employeeName"></span></p>
                                    <p class="mb-1"><strong>Aylık Maaş:</strong> <span id="employeeSalary"></span></p>
                                    <p class="mb-0"><strong>İşe Başlama:</strong> <span id="employeeHireDate"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-info-100">
                                <div class="card-body">
                                    <h6 class="card-title text-info-600">Ödeme Bilgileri</h6>
                                    <p class="mb-1"><strong>Dönem:</strong> <span id="currentPeriod"></span></p>
                                    <p class="mb-1"><strong>Kalan Maaş:</strong> <span id="remainingSalary" class="fw-bold text-success"></span></p>
                                    <p class="mb-0"><strong>Maksimum Ödeme:</strong> <span id="maxPayment" class="fw-bold text-primary"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row gy-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Ödeme Tutarı (₺) <span class="text-danger-600">*</span></label>
                            <input type="number" step="0.01" id="paymentAmount" class="form-control radius-8" name="amount" placeholder="Ödeme tutarını girin" required>
                            <small class="text-muted" id="maxAmountText">Maksimum: 0.00 ₺</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Ödeme Tarihi <span class="text-danger-600">*</span></label>
                            <input type="date" class="form-control radius-8" name="payment_date" value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Ödeme Yöntemi <span class="text-danger-600">*</span></label>
                            <select class="form-control radius-8" name="payment_method" required>
                                <option value="">Ödeme yöntemi seçin</option>
                                <option value="cash">Nakit</option>
                                <option value="bank_transfer">Banka Havalesi</option>
                                <option value="check">Çek</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Dönem</label>
                            <input type="month" class="form-control radius-8" name="month_year" id="paymentMonth" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Notlar</label>
                            <textarea class="form-control radius-8" name="notes" rows="3" placeholder="Ödeme ile ilgili notlar..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Maaş Ödemesi Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let table = new DataTable('#dataTable');
    let currentEmployeeId = null;
    let currentEmployeeSalary = 0;
    let currentHireDate = null;

    // Select all functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Open salary payment modal
    function openSalaryPaymentModal(employeeId, employeeName, employeeSalary, hireDate) {
        currentEmployeeId = employeeId;
        currentEmployeeSalary = employeeSalary;
        currentHireDate = hireDate;
        
        // Set employee info
        document.getElementById('employeeName').textContent = employeeName;
        document.getElementById('employeeSalary').textContent = employeeSalary ? employeeSalary.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺' : '-';
        document.getElementById('employeeHireDate').textContent = hireDate ? new Date(hireDate).toLocaleDateString('tr-TR') : '-';
        
        // Set current period
        const currentMonth = new Date().toISOString().slice(0, 7);
        const turkishMonths = {
            '01': 'Ocak', '02': 'Şubat', '03': 'Mart', '04': 'Nisan',
            '05': 'Mayıs', '06': 'Haziran', '07': 'Temmuz', '08': 'Ağustos',
            '09': 'Eylül', '10': 'Ekim', '11': 'Kasım', '12': 'Aralık'
        };
        const [year, month] = currentMonth.split('-');
        const turkishMonth = turkishMonths[month];
        document.getElementById('currentPeriod').textContent = `${turkishMonth} ${year}`;
        document.getElementById('paymentMonth').value = currentMonth;
        
        // Set form action
        document.getElementById('salaryPaymentForm').action = `/management/employees/${employeeId}/salary-payments`;
        
        // Calculate remaining salary for current month
        fetchRemainingSalary(employeeId, currentMonth);
        
        // Show modal
        new bootstrap.Modal(document.getElementById('salaryPaymentModal')).show();
    }

    // Fetch remaining salary
    function fetchRemainingSalary(employeeId, monthYear) {
        fetch(`/management/employees/${employeeId}/remaining-salary?month=${monthYear}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('remainingSalary').textContent = data.remaining.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                document.getElementById('maxPayment').textContent = data.remaining.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                document.getElementById('maxAmountText').textContent = `Maksimum: ${data.remaining.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺`;
                document.getElementById('paymentAmount').max = data.remaining;
            })
            .catch(error => {
                console.error('Error fetching remaining salary:', error);
                // Fallback to full salary
                document.getElementById('remainingSalary').textContent = currentEmployeeSalary.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                document.getElementById('maxPayment').textContent = currentEmployeeSalary.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                document.getElementById('maxAmountText').textContent = `Maksimum: ${currentEmployeeSalary.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺`;
                document.getElementById('paymentAmount').max = currentEmployeeSalary;
            });
    }

</script>
@endpush
