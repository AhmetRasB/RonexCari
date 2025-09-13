@extends('layout.layout')

@section('title', 'Roller')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Roller</h6>
    <ul class="d-flex align-items-center gap-2">
        <li class="fw-medium">
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                Dashboard
            </a>
        </li>
        <li>-</li>
        <li class="fw-medium">
            <a href="{{ route('management.roles.index') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                Yönetim
            </a>
        </li>
        <li>-</li>
        <li class="fw-medium">Roller</li>
    </ul>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center gap-2">
            <iconify-icon icon="solar:shield-user-outline" class="text-xl"></iconify-icon>
            <h6 class="mb-0">Rol Listesi</h6>
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
            <table id="dataTable" class="table bordered-table mb-0">
                <thead>
                    <tr>
                        <th scope="col">Rol Adı</th>
                        <th scope="col">Açıklama</th>
                        <th scope="col">Kullanıcı Sayısı</th>
                        <th scope="col">İzinler</th>
                        <th scope="col">Oluşturulma</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <iconify-icon icon="{{ $role->name === 'god_mode' ? 'solar:crown-outline' : ($role->name === 'admin' ? 'solar:shield-user-outline' : 'solar:user-outline') }}" class="text-lg text-{{ $role->name === 'god_mode' ? 'danger' : ($role->name === 'admin' ? 'primary' : 'secondary') }}"></iconify-icon>
                                <div>
                                    <h6 class="text-md mb-0 fw-medium">{{ $role->display_name }}</h6>
                                    <span class="text-sm text-secondary-light">{{ $role->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-md mb-0">{{ $role->description ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-info text-sm fw-semibold px-20 py-9 radius-4 text-white">
                                {{ $role->users_count }} kullanıcı
                            </span>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @if(in_array('*', $role->permissions ?? []))
                                    <span class="badge bg-danger text-xs fw-semibold px-8 py-4 radius-4 text-white">
                                        TÜM İZİNLER
                                    </span>
                                @else
                                    @php
                                        $permissionLabels = [
                                            'dashboard' => 'Dashboard',
                                            'sales' => 'Satışlar',
                                            'purchases' => 'Alışlar',
                                            'products' => 'Ürünler',
                                            'finance' => 'Finans',
                                            'expenses' => 'Giderler',
                                            'reports' => 'Raporlar',
                                            'management' => 'Yönetim'
                                        ];
                                    @endphp
                                    @foreach(array_slice($role->permissions ?? [], 0, 3) as $permission)
                                        <span class="badge bg-secondary text-xs fw-semibold px-8 py-4 radius-4 text-white">
                                            {{ $permissionLabels[$permission] ?? $permission }}
                                        </span>
                                    @endforeach
                                    @if(count($role->permissions ?? []) > 3)
                                        <span class="badge bg-light text-xs fw-semibold px-8 py-4 radius-4 text-dark">
                                            +{{ count($role->permissions) - 3 }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="text-md mb-0">{{ $role->created_at->format('d.m.Y H:i') }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let table = new DataTable('#dataTable');
</script>
@endpush
