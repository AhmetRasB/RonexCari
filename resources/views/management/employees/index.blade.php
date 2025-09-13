@extends('layout.layout')

@section('title', 'Çalışanlar')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Çalışanlar</h6>
    <ul class="d-flex align-items-center gap-2">
        <li class="fw-medium">
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                Dashboard
            </a>
        </li>
        <li>-</li>
        <li class="fw-medium">
            <a href="{{ route('management.employees.index') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                Yönetim
            </a>
        </li>
        <li>-</li>
        <li class="fw-medium">Çalışanlar</li>
    </ul>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <iconify-icon icon="solar:users-group-rounded-outline" class="text-xl"></iconify-icon>
            <h6 class="mb-0">Çalışan Listesi</h6>
        </div>
        <a href="{{ route('management.employees.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-6 rounded-8 d-flex align-items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
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
            <table class="table table-hover" id="dataTable">
                <thead>
                    <tr>
                        <th>İsim</th>
                        <th>Email</th>
                        <th>Telefon</th>
                        <th>Pozisyon</th>
                        <th>Maaş</th>
                        <th>Başlangıç Tarihi</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-md mb-0 fw-medium">{{ $employee->name }}</h6>
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
                            <span class="text-md mb-0">{{ $employee->salary ? number_format($employee->salary, 2) . ' ₺' : '-' }}</span>
                        </td>
                        <td>
                            <span class="text-md mb-0">{{ $employee->start_date ? \Carbon\Carbon::parse($employee->start_date)->format('d.m.Y') : '-' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $employee->is_active ? 'success' : 'danger' }} text-sm fw-semibold px-20 py-9 radius-4 text-white">
                                {{ $employee->is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('management.employees.show', $employee->id) }}" class="btn btn-sm btn-outline-primary" title="Görüntüle">
                                    <iconify-icon icon="solar:eye-outline" class="icon text-lg"></iconify-icon>
                                </a>
                                <a href="{{ route('management.employees.edit', $employee->id) }}" class="btn btn-sm btn-outline-warning" title="Düzenle">
                                    <iconify-icon icon="solar:pen-outline" class="icon text-lg"></iconify-icon>
                                </a>
                                <form action="{{ route('management.employees.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu çalışanı silmek istediğinizden emin misiniz?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Sil">
                                        <iconify-icon icon="solar:trash-bin-outline" class="icon text-lg"></iconify-icon>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <iconify-icon icon="solar:users-group-rounded-outline" class="icon text-4xl text-muted"></iconify-icon>
                                <span class="text-muted">Henüz çalışan eklenmemiş</span>
                                <a href="{{ route('management.employees.create') }}" class="btn btn-primary btn-sm">
                                    İlk Çalışanı Ekle
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });
});
</script>
@endpush
