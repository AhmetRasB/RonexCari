@extends('layout.layout')

@section('title', 'Müşteriler')
@section('subTitle', 'Müşteri Listesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Müşteri Listesi</h5>
                <a href="{{ route('sales.customers.create') }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
                    <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                    Yeni Müşteri
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
                            <th scope="col">Müşteri Adı</th>
                            <th scope="col">Şirket</th>
                            <th scope="col">E-posta</th>
                            <th scope="col">Telefon</th>
                            <th scope="col">Borç Bakiyesi (TL)</th>
                            <th scope="col">Borç Bakiyesi (USD)</th>
                            <th scope="col">Borç Bakiyesi (EUR)</th>
                            <th scope="col">Durum</th>
                            <th scope="col">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $index => $customer)
                            <tr>
                                <td>
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" value="{{ $customer->id }}">
                                        <label class="form-check-label">{{ $index + 1 }}</label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="w-40-px h-40-px bg-primary-light text-primary-600 rounded-circle d-flex align-items-center justify-content-center me-12">
                                            <iconify-icon icon="solar:user-outline" class="text-lg"></iconify-icon>
                                        </div>
                                        <h6 class="text-md mb-0 fw-medium flex-grow-1">{{ $customer->name }}</h6>
                                    </div>
                                </td>
                                <td>{{ $customer->company_name ?? '-' }}</td>
                                <td><a href="mailto:{{ $customer->email }}" class="text-primary-600">{{ $customer->email }}</a></td>
                                <td>{{ $customer->phone ?? '-' }}</td>
                                <td>
                                    @if($customer->balance_try > 0)
                                        <span class="text-danger fw-semibold">+{{ number_format($customer->balance_try, 2) }} ₺</span>
                                        <small class="text-muted d-block">Borç</small>
                                    @elseif($customer->balance_try < 0)
                                        <span class="text-success fw-semibold">{{ number_format($customer->balance_try, 2) }} ₺</span>
                                        <small class="text-muted d-block">Alacak</small>
                                    @else
                                        <span class="text-muted">0,00 ₺</span>
                                    @endif
                                </td>
                                <td>
                                    @if($customer->balance_usd > 0)
                                        <span class="text-danger fw-semibold">+${{ number_format($customer->balance_usd, 2) }}</span>
                                        <small class="text-muted d-block">Borç</small>
                                    @elseif($customer->balance_usd < 0)
                                        <span class="text-success fw-semibold">${{ number_format($customer->balance_usd, 2) }}</span>
                                        <small class="text-muted d-block">Alacak</small>
                                    @else
                                        <span class="text-muted">$0.00</span>
                                    @endif
                                </td>
                                <td>
                                    @if($customer->balance_eur > 0)
                                        <span class="text-danger fw-semibold">+€{{ number_format($customer->balance_eur, 2) }}</span>
                                        <small class="text-muted d-block">Borç</small>
                                    @elseif($customer->balance_eur < 0)
                                        <span class="text-success fw-semibold">€{{ number_format($customer->balance_eur, 2) }}</span>
                                        <small class="text-muted d-block">Alacak</small>
                                    @else
                                        <span class="text-muted">€0.00</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="bg-{{ $customer->is_active ? 'success' : 'danger' }}-focus text-{{ $customer->is_active ? 'success' : 'danger' }}-main px-24 py-4 rounded-pill fw-medium text-sm">
                                        {{ $customer->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('sales.customers.show', $customer) }}" class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                    </a>
                                    <a href="{{ route('sales.customers.edit', $customer) }}" class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                    </a>
                                    <form action="{{ route('sales.customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu müşteriyi silmek istediğinizden emin misiniz?')">
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
                                <td colspan="8" class="text-center py-4">Henüz müşteri bulunmuyor.</td>
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
