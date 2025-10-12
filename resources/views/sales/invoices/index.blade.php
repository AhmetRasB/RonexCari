@extends('layout.layout')

@section('title', 'Faturalar')
@section('subTitle', 'Fatura Listesi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card basic-data-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Fatura Listesi</h5>
                <div class="d-flex gap-2">
                    <button id="deleteSelectedBtn" class="btn btn-danger-100 text-danger-600 radius-8 px-20 py-11" style="display: none;">
                        <iconify-icon icon="solar:trash-bin-minimalistic-outline" class="me-2"></iconify-icon>
                        Seçilenleri Sil (<span id="selectedCount">0</span>)
                    </button>
                    <a href="{{ route('sales.invoices.create') }}" class="btn btn-primary-100 text-primary-600 radius-8 px-20 py-11">
                        <iconify-icon icon="solar:add-circle-outline" class="me-2"></iconify-icon>
                        Yeni Fatura
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

                <!-- Mobile Card View -->
                <div class="d-block d-lg-none">
                    @forelse($invoices as $index => $invoice)
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <a href="{{ route('sales.invoices.show', $invoice) }}" class="text-primary-600 text-decoration-none">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">{{ $invoice->invoice_date->format('d.m.Y') }}</small>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                            <iconify-icon icon="solar:menu-dots-bold"></iconify-icon>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('sales.invoices.show', $invoice) }}">Görüntüle</a></li>
                                            <li><a class="dropdown-item" href="{{ route('sales.invoices.edit', $invoice) }}">Düzenle</a></li>
                                            <li><a class="dropdown-item" target="_blank" href="{{ route('sales.invoices.print', $invoice) }}">Yazdır</a></li>
                                            @if(!$invoice->payment_completed)
                                            <li>
                                                <form action="{{ route('sales.invoices.mark-paid', $invoice) }}" method="POST" onsubmit="return confirm('Bu faturayı tahsil edildi olarak işaretlemek istediğinize emin misiniz?')">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">Tahsilat Yapıldı</button>
                                                </form>
                                            </li>
                                            @endif
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
                                </div>
                                
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <small class="text-muted">Müşteri:</small>
                                        <div class="fw-medium">{{ $invoice->customer->name ?? 'Müşteri Silinmiş' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Mağaza:</small>
                                        <div>
                                            @if($invoice->account)
                                                <span class="badge {{ $invoice->account->code === 'ronex1' ? 'bg-primary-100 text-primary-600' : 'bg-success-100 text-success-600' }} px-2 py-1 rounded-pill text-xs fw-medium">
                                                    {{ $invoice->account->code }}
                                                </span>
                                            @else
                                                <span class="badge bg-gray-100 text-gray-600 px-2 py-1 rounded-pill text-xs fw-medium">
                                                    Mağaza Yok
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">Tutar:</small>
                                        <div class="fw-semibold">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</div>
                                    </div>
                                    <div>
                                        @if($invoice->payment_completed)
                                            <span class="badge bg-success-100 text-success-600 px-2 py-1 rounded-pill text-xs fw-medium">
                                                <iconify-icon icon="solar:check-circle-outline" class="me-1"></iconify-icon>
                                                Tahsil Edildi
                                            </span>
                                        @else
                                            <span class="badge bg-warning-100 text-warning-600 px-2 py-1 rounded-pill text-xs fw-medium">
                                                <iconify-icon icon="solar:clock-circle-outline" class="me-1"></iconify-icon>
                                                Beklemede
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <iconify-icon icon="solar:document-outline" class="text-4xl text-muted mb-2"></iconify-icon>
                            <p class="text-muted">Henüz fatura bulunmuyor.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Desktop Table View -->
                <div class="table-responsive d-none d-lg-block">
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
                            <th scope="col">Mağaza</th>
                            <th scope="col">Personel</th>
                            <th scope="col">Müşteri</th>
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
                                        <input class="form-check-input row-checkbox" type="checkbox" data-id="{{ $invoice->id }}">
                                        <label class="form-check-label">{{ $index + 1 }}</label>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('sales.invoices.show', $invoice) }}" class="text-primary-600">
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
                                    <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</span>
                                        @if($invoice->payment_completed)
                                            <span class="badge bg-success-100 text-success-600 px-2 py-1 rounded-pill text-xs fw-medium mt-1">
                                                <iconify-icon icon="solar:check-circle-outline" class="me-1"></iconify-icon>
                                                Tahsil Edildi
                                            </span>
                                        @else
                                            <span class="badge bg-warning-100 text-warning-600 px-2 py-1 rounded-pill text-xs fw-medium mt-1">
                                                <iconify-icon icon="solar:clock-circle-outline" class="me-1"></iconify-icon>
                                                Beklemede
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="w-32-px h-32-px bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="İşlemler">
                                            <iconify-icon icon="solar:menu-dots-bold"></iconify-icon>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('sales.invoices.show', $invoice) }}">Görüntüle</a></li>
                                            <li><a class="dropdown-item" href="{{ route('sales.invoices.edit', $invoice) }}">Düzenle</a></li>
                                            <li><a class="dropdown-item" target="_blank" href="{{ route('sales.invoices.print', $invoice) }}">Yazdır</a></li>
                                            @if(!$invoice->payment_completed)
                                            <li>
                                                <form action="{{ route('sales.invoices.mark-paid', $invoice) }}" method="POST" onsubmit="return confirm('Bu faturayı tahsil edildi olarak işaretlemek istediğinize emin misiniz?')">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">Tahsilat Yapıldı</button>
                                                </form>
                                            </li>
                                            @endif
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
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateDeleteButton();
    });

    // Update delete button visibility
    function updateDeleteButton() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        const countSpan = document.getElementById('selectedCount');
        
        if (checkedBoxes.length > 0) {
            deleteBtn.style.display = 'inline-block';
            countSpan.textContent = checkedBoxes.length;
        } else {
            deleteBtn.style.display = 'none';
        }
    }

    // Row checkbox change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('row-checkbox')) {
            updateDeleteButton();
        }
    });

    // Delete selected
    document.getElementById('deleteSelectedBtn').addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const ids = Array.from(checkedBoxes).map(cb => cb.dataset.id);
        
        if (ids.length === 0) return;
        
        if (confirm(ids.length + ' faturayı silmek istediğinizden emin misiniz?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("sales.invoices.bulk-delete") }}';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            
            const idsInput = document.createElement('input');
            idsInput.type = 'hidden';
            idsInput.name = 'ids';
            idsInput.value = JSON.stringify(ids);
            
            form.appendChild(csrfInput);
            form.appendChild(idsInput);
            document.body.appendChild(form);
            form.submit();
        }
    });

    // Approval functions removed - invoices are now directly approved
</script>
@endpush
@endsection
