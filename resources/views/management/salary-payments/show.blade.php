@extends('layout.layout')

@section('title', 'Maaş Ödemeleri')
@section('subTitle', 'Maaş Ödeme Geçmişi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Maaş Ödemeleri - {{ $employee->name }}</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('management.employees.salary-payments.create', $employee) }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
                        <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                        Yeni Ödeme
                    </a>
                    <a href="{{ route('management.employees.index') }}" class="btn btn-outline-secondary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-2"></iconify-icon>
                        Geri Dön
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

                <!-- Employee Summary -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Aylık Maaş</h6>
                                <h4 class="text-primary">{{ $employee->salary ? number_format($employee->salary, 2) . ' ₺' : '-' }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success-100">
                            <div class="card-body text-center">
                                <h6 class="card-title text-success-600">Toplam Ödenen</h6>
                                <h4 class="text-success">{{ number_format($employee->salaryPayments()->sum('amount'), 2) }} ₺</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning-100">
                            <div class="card-body text-center">
                                <h6 class="card-title text-warning-600">Kalan Maaş</h6>
                                <h4 class="text-warning">{{ number_format(($employee->salary ?? 0) - $employee->salaryPayments()->sum('amount'), 2) }} ₺</h4>
                            </div>
                        </div>
                    </div>
                </div>

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
                            <th scope="col">Tarih</th>
                            <th scope="col">Dönem</th>
                            <th scope="col">Tutar</th>
                            <th scope="col">Ödeme Yöntemi</th>
                            <th scope="col">Mağaza</th>
                            <th scope="col">Ödeyen</th>
                            <th scope="col">Notlar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $index => $payment)
                            <tr>
                                <td>
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" value="{{ $payment->id }}">
                                        <label class="form-check-label">{{ $index + 1 }}</label>
                                    </div>
                                </td>
                                <td>{{ $payment->payment_date->format('d.m.Y') }}</td>
                                <td>
                                    <span class="badge bg-primary-100 text-primary-600 px-2 py-1 rounded-pill text-xs fw-medium">
                                        {{ \Carbon\Carbon::parse($payment->month_year . '-01')->locale('tr')->isoFormat('MMM Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">{{ number_format($payment->amount, 2) }} ₺</span>
                                </td>
                                <td>
                                    @switch($payment->payment_method)
                                        @case('cash')
                                            <span class="badge bg-success-100 text-success-600 px-2 py-1 rounded-pill text-xs fw-medium">
                                                <iconify-icon icon="solar:wallet-money-outline" class="me-1"></iconify-icon>
                                                Nakit
                                            </span>
                                            @break
                                        @case('bank_transfer')
                                            <span class="badge bg-info-100 text-info-600 px-2 py-1 rounded-pill text-xs fw-medium">
                                                <iconify-icon icon="solar:card-outline" class="me-1"></iconify-icon>
                                                Banka Havalesi
                                            </span>
                                            @break
                                        @case('check')
                                            <span class="badge bg-warning-100 text-warning-600 px-2 py-1 rounded-pill text-xs fw-medium">
                                                <iconify-icon icon="solar:document-outline" class="me-1"></iconify-icon>
                                                Çek
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    @if($payment->account)
                                        <span class="badge {{ $payment->account->code === 'ronex1' ? 'bg-primary-100 text-primary-600' : 'bg-success-100 text-success-600' }} px-2 py-1 rounded-pill text-xs fw-medium">
                                            {{ $payment->account->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-gray-100 text-gray-600 px-2 py-1 rounded-pill text-xs fw-medium">
                                            Mağaza Yok
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->user)
                                        <div class="d-flex align-items-center">
                                            <div class="w-8 h-8 bg-info-100 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <iconify-icon icon="heroicons:user" class="text-info-600 text-sm"></iconify-icon>
                                            </div>
                                            <div>
                                                <h6 class="text-sm mb-0 fw-medium">{{ $payment->user->name }}</h6>
                                                <small class="text-secondary-light">{{ $payment->user->email }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Bilinmiyor</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-sm">{{ $payment->notes ?? '-' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center gap-2">
                                        <iconify-icon icon="solar:wallet-money-outline" class="icon text-4xl text-muted"></iconify-icon>
                                        <span class="text-muted">Henüz maaş ödemesi yapılmamış</span>
                                        <a href="{{ route('management.employees.salary-payments.create', $employee) }}" class="btn btn-primary btn-sm">
                                            İlk Ödemeyi Yap
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>

                @if($payments->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $payments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

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
