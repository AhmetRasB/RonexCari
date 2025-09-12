@extends('layout.layout')

@section('title', 'Faturalar')
@section('subTitle', 'Fatura Listesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Fatura Listesi</h5>
                <a href="{{ route('sales.invoices.create') }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
                    <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                    Yeni Fatura
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
                            <th scope="col">Fatura No</th>
                            <th scope="col">Müşteri</th>
                            <th scope="col">Tarih</th>
                            <th scope="col">Vade</th>
                            <th scope="col">Tutar</th>
                            <th scope="col">Durum</th>
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
                                    <a href="{{ route('sales.invoices.show', $invoice) }}" class="text-primary-600">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="text-md mb-0 fw-medium">{{ $invoice->customer->name ?? 'Müşteri Silinmiş' }}</h6>
                                            @if($invoice->customer && $invoice->customer->company_name)
                                                <small class="text-secondary-light">{{ $invoice->customer->company_name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $invoice->invoice_date->format('d.m.Y') }}</td>
                                <td>{{ $invoice->due_date->format('d.m.Y') }}</td>
                                <td>
                                    <span class="fw-semibold">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</span>
                                </td>
                                <td id="status-cell-{{ $invoice->id }}">
                                    @switch($invoice->status)
                                        @case('draft')
                                            <span id="status-{{ $invoice->id }}" class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">Taslak</span>
                                            @break
                                        @case('approved')
                                            <span id="status-{{ $invoice->id }}" class="bg-info-focus text-info-main px-24 py-4 rounded-pill fw-medium text-sm">Onaylandı</span>
                                            @break
                                        @case('paid')
                                            <span id="status-{{ $invoice->id }}" class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Ödendi</span>
                                            @break
                                        @case('cancelled')
                                            <span id="status-{{ $invoice->id }}" class="bg-secondary-focus text-secondary-main px-24 py-4 rounded-pill fw-medium text-sm">İptal Edildi</span>
                                            @break
                                        @default
                                            <span id="status-{{ $invoice->id }}" class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">Taslak</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="w-32-px h-32-px bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="İşlemler">
                                            <iconify-icon icon="solar:menu-dots-bold"></iconify-icon>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('sales.invoices.show', $invoice) }}">Görüntüle</a></li>
                                            <li><a class="dropdown-item {{ in_array($invoice->status, ['approved','paid']) ? 'disabled' : '' }}" href="{{ route('sales.invoices.edit', $invoice) }}">Düzenle</a></li>
                                            <li><a class="dropdown-item" target="_blank" href="{{ route('sales.invoices.print', $invoice) }}">Yazdır</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><button id="approve-btn-{{ $invoice->id }}" class="dropdown-item" onclick="approveInvoice({{ $invoice->id }})" {{ $invoice->status !== 'draft' ? 'disabled' : '' }}>Onayla</button></li>
                                            <li><button class="dropdown-item" onclick="revertDraftInvoice({{ $invoice->id }})" {{ $invoice->payment_completed ? 'disabled' : ($invoice->status === 'draft' ? 'disabled' : '') }}>Taslağa Çevir</button></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('sales.invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Bu faturayı silmek istediğinizden emin misiniz?')">
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
                                <td colspan="8" class="text-center py-4">Henüz fatura bulunmuyor.</td>
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

    window.approveInvoice = function(id) {
        fetch(`{{ url('sales/invoices') }}/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(res => { if(!res.ok) throw new Error(res.status); return res.json(); })
            .then(data => {
                const statusCell = document.getElementById(`status-${id}`);
                if (statusCell) {
                    statusCell.className = 'bg-info-focus text-info-main px-24 py-4 rounded-pill fw-medium text-sm';
                    statusCell.textContent = 'Onaylandı';
                }
                const approveBtn = document.getElementById(`approve-btn-${id}`);
                if (approveBtn) approveBtn.disabled = true;
            })
            .catch(err => {
                alert('Onaylama başarısız ('+err+'). Lütfen sayfayı yenileyip tekrar deneyin.');
            });
    }
    window.revertDraftInvoice = function(id) {
        fetch(`{{ url('sales/invoices') }}/${id}/revert-draft`, {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}})
            .then(()=>location.reload());
    }
</script>
@endpush
@endsection
