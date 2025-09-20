@extends('layout.layout')

@section('title', 'Alış Faturaları')
@section('subTitle', 'Alış Fatura Listesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Alış Fatura Listesi</h5>
                <a href="{{ route('purchases.invoices.create') }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
                    <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                    Yeni Alış Faturası
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
                    <table class="table bordered-table mb-0 responsive-table" id="dataTable" data-page-length='10'>
                    <thead>
                        <tr>
                            <th scope="col">
                                <div class="form-check style-check d-flex align-items-center">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                    <label class="form-check-label">S.L</label>
                                </div>
                            </th>
                            <th scope="col">Fatura No</th>
                            <th scope="col">Mağaza</th>
                            <th scope="col">Personel</th>
                            <th scope="col">Tedarikçi</th>
                            <th scope="col">Tarih</th>
                            <th scope="col">Vade</th>
                            <th scope="col">Tutar</th>
                            <th scope="col">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $index => $invoice)
                            <tr>
                                <td>
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" value="{{ $invoice->id }}">
                                        <label class="form-check-label">{{ $index + 1 }}</label>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('purchases.invoices.show', $invoice) }}" class="text-primary-600">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    @if($invoice->account)
                                        <span class="badge {{ $invoice->account->code === 'ronex1' ? 'bg-primary-100 text-primary-600' : 'bg-success-100 text-success-600' }} px-2 py-1 rounded-pill text-xs fw-medium">
                                            {{ $invoice->account->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-gray-100 text-gray-600 px-2 py-1 rounded-pill text-xs fw-medium">
                                            Mağaza Yok
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($invoice->user)
                                        <div class="d-flex align-items-center">
                                            <div class="w-8 h-8 bg-info-100 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <iconify-icon icon="heroicons:user" class="text-info-600 text-sm"></iconify-icon>
                                            </div>
                                            <div>
                                                <h6 class="text-sm mb-0 fw-medium">{{ $invoice->user->name }}</h6>
                                                <small class="text-secondary-light">{{ $invoice->user->email }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Bilinmiyor</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="text-md mb-0 fw-medium">{{ $invoice->supplier->name }}</h6>
                                            @if($invoice->supplier->company_name)
                                                <small class="text-secondary-light">{{ $invoice->supplier->company_name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $invoice->invoice_date->format('d.m.Y') }}</td>
                                <td>{{ $invoice->due_date->format('d.m.Y') }}</td>
                                <td>
                                    <span class="fw-semibold">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="w-32-px h-32-px bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="İşlemler">
                                            <iconify-icon icon="solar:menu-dots-bold"></iconify-icon>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('purchases.invoices.show', $invoice) }}">Görüntüle</a></li>
                                            <li><a class="dropdown-item" href="{{ route('purchases.invoices.edit', $invoice) }}">Düzenle</a></li>
                                            <li><a class="dropdown-item" target="_blank" href="{{ route('purchases.invoices.print', $invoice) }}">Yazdır</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('purchases.invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Bu alış faturasını silmek istediğinizden emin misiniz?')">
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
                                <td colspan="8" class="text-center py-4">Henüz alış faturası bulunmuyor.</td>
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
        // Initialize DataTable with proper column configuration
        let table = new DataTable('#dataTable', {
            "columnDefs": [
                { "orderable": false, "targets": [0, 7] }, // Disable sorting on checkbox and actions columns
                { "searchable": false, "targets": [0, 7] } // Disable search on checkbox and actions columns
            ],
            "pageLength": 10,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json"
            }
        });

        // Select all functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Approval functions removed - invoices are now directly approved
    });

</script>
@endpush
@endsection
