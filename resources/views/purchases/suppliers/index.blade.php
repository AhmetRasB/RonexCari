@extends('layout.layout')

@section('title', 'Tedarikçiler')
@section('subTitle', 'Tedarikçi Listesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tedarikçi Listesi</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('finance.supplier-payments.index') }}" class="btn btn-warning-100 text-warning-600 radius-8 px-20 py-11">
                        <iconify-icon icon="solar:wallet-money-outline" class="me-2"></iconify-icon>
                        Tedarikçi Ödemeleri
                    </a>
                    <a href="{{ route('purchases.suppliers.create') }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
                        <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                        Yeni Tedarikçi
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

                <div class="table-responsive">
                    <table class="table bordered-table mb-0 responsive-table" id="dataTable" data-page-length='10'>
                    <thead>
                        <tr>
                            <th scope="col">
                                <div class="form-check style-check d-flex align-items-center">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                    <label class="form-check-label">S.L</label>
                                </div>
                            </th>
                            <th scope="col">Tedarikçi Adı</th>
                            <th scope="col">Şirket</th>
                            <th scope="col">E-posta</th>
                            <th scope="col">Telefon</th>
                            <th scope="col">Kalan Borç (TL)</th>
                            <th scope="col">Kalan Borç (USD)</th>
                            <th scope="col">Kalan Borç (EUR)</th>
                            <th scope="col">Ödeme Durumu</th>
                            <th scope="col">Durum</th>
                            <th scope="col">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $index => $supplier)
                            <tr>
                                <td>
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" value="{{ $supplier->id }}">
                                        <label class="form-check-label">{{ $index + 1 }}</label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="w-40-px h-40-px bg-primary-light text-primary-600 rounded-circle d-flex align-items-center justify-content-center me-12">
                                            <iconify-icon icon="solar:user-outline" class="text-lg"></iconify-icon>
                                        </div>
                                        <h6 class="text-md mb-0 fw-medium flex-grow-1">{{ $supplier->name }}</h6>
                                    </div>
                                </td>
                                <td>{{ $supplier->company_name ?? '-' }}</td>
                                <td><a href="mailto:{{ $supplier->email }}" class="text-primary-600">{{ $supplier->email }}</a></td>
                                <td>{{ $supplier->phone ?? '-' }}</td>
                                <td>
                                    @if($supplier->remaining_balance_try > 0)
                                        <span class="text-danger fw-semibold">{{ number_format($supplier->remaining_balance_try, 2) }} ₺</span>
                                    @elseif($supplier->remaining_balance_try < 0)
                                        <span class="text-success fw-semibold">{{ number_format(abs($supplier->remaining_balance_try), 2) }} ₺</span>
                                        <small class="text-muted d-block">Alacak</small>
                                    @else
                                        <span class="text-muted">0,00 ₺</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->remaining_balance_usd > 0)
                                        <span class="text-danger fw-semibold">${{ number_format($supplier->remaining_balance_usd, 2) }}</span>
                                    @elseif($supplier->remaining_balance_usd < 0)
                                        <span class="text-success fw-semibold">${{ number_format(abs($supplier->remaining_balance_usd), 2) }}</span>
                                        <small class="text-muted d-block">Alacak</small>
                                    @else
                                        <span class="text-muted">$0.00</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->remaining_balance_eur > 0)
                                        <span class="text-danger fw-semibold">€{{ number_format($supplier->remaining_balance_eur, 2) }}</span>
                                    @elseif($supplier->remaining_balance_eur < 0)
                                        <span class="text-success fw-semibold">€{{ number_format(abs($supplier->remaining_balance_eur), 2) }}</span>
                                        <small class="text-muted d-block">Alacak</small>
                                    @else
                                        <span class="text-muted">€0.00</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->payment_status === 'paid')
                                        <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Ödendi</span>
                                    @elseif($supplier->payment_status === 'partial')
                                        <span class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">Kısmi Ödeme</span>
                                    @else
                                        <span class="bg-danger-focus text-danger-main px-24 py-4 rounded-pill fw-medium text-sm">Ödenmedi</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="bg-{{ $supplier->is_active ? 'success' : 'danger' }}-focus text-{{ $supplier->is_active ? 'success' : 'danger' }}-main px-24 py-4 rounded-pill fw-medium text-sm">
                                        {{ $supplier->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('purchases.suppliers.show', $supplier) }}" class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                    </a>
                                    <a href="{{ route('purchases.suppliers.edit', $supplier) }}" class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                    </a>
                                    <a href="{{ route('finance.supplier-payments.create') }}?supplier_id={{ $supplier->id }}" class="w-32-px h-32-px bg-warning-focus text-warning-main rounded-circle d-inline-flex align-items-center justify-content-center" title="Ödeme Yap">
                                        <iconify-icon icon="solar:dollar-minimalistic-outline"></iconify-icon>
                                    </a>
                                    <form action="{{ route('purchases.suppliers.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu tedarikçiyi silmek istediğinizden emin misiniz?')">
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
                                <td colspan="11" class="text-center py-4">Henüz tedarikçi bulunmuyor.</td>
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