@extends('layout.layout')

@section('title', 'Kullanıcı Detayı')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Kullanıcı Detayı</h6>
    <ul class="d-flex align-items-center gap-2">
        <li class="fw-medium">
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                Dashboard
            </a>
        </li>
        <li>-</li>
        <li class="fw-medium">
            <a href="{{ route('management.users.index') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                Kullanıcılar
            </a>
        </li>
        <li>-</li>
        <li class="fw-medium">{{ $user->name }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:user-circle-outline" class="text-xl"></iconify-icon>
                    <h6 class="mb-0">{{ $user->name }}</h6>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('management.users.edit', $user) }}" class="btn btn-warning text-sm btn-sm px-12 py-6 rounded-8 d-flex align-items-center gap-2">
                        <iconify-icon icon="lucide:edit" class="icon text-xl line-height-1"></iconify-icon>
                        Düzenle
                    </a>
                    <a href="{{ route('management.users.index') }}" class="btn btn-primary text-sm btn-sm px-12 py-6 rounded-8 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:arrow-left-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Geri
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row gy-4">
                    <div class="col-12 col-md-6">
                        <div class="d-flex flex-column gap-1">
                            <span class="fw-semibold text-primary-light text-sm">İsim</span>
                            <span class="text-md">{{ $user->name }}</span>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex flex-column gap-1">
                            <span class="fw-semibold text-primary-light text-sm">Email</span>
                            <span class="text-md">{{ $user->email }}</span>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex flex-column gap-1">
                            <span class="fw-semibold text-primary-light text-sm">Rol</span>
                            @if($user->role)
                                <span class="badge bg-{{ $user->hasRole('god_mode') ? 'danger' : ($user->hasRole('admin') ? 'primary' : 'secondary') }} text-sm fw-semibold px-20 py-9 radius-4 text-white d-inline-flex align-items-center gap-2" style="width: fit-content;">
                                    <iconify-icon icon="{{ $user->hasRole('god_mode') ? 'solar:crown-outline' : ($user->hasRole('admin') ? 'solar:shield-user-outline' : 'solar:user-outline') }}"></iconify-icon>
                                    {{ $user->role->display_name }}
                                </span>
                            @else
                                <span class="badge bg-warning text-sm fw-semibold px-20 py-9 radius-4 text-white" style="width: fit-content;">Rol Yok</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex flex-column gap-1">
                            <span class="fw-semibold text-primary-light text-sm">Email Doğrulanma</span>
                            @if($user->email_verified_at)
                                <span class="badge bg-success text-sm fw-semibold px-20 py-9 radius-4 text-white d-inline-flex align-items-center gap-2" style="width: fit-content;">
                                    <iconify-icon icon="solar:check-circle-outline"></iconify-icon>
                                    Doğrulandı
                                </span>
                            @else
                                <span class="badge bg-warning text-sm fw-semibold px-20 py-9 radius-4 text-white d-inline-flex align-items-center gap-2" style="width: fit-content;">
                                    <iconify-icon icon="solar:close-circle-outline"></iconify-icon>
                                    Doğrulanmadı
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex flex-column gap-1">
                            <span class="fw-semibold text-primary-light text-sm">Oluşturulma Tarihi</span>
                            <span class="text-md">{{ $user->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex flex-column gap-1">
                            <span class="fw-semibold text-primary-light text-sm">Son Güncelleme</span>
                            <span class="text-md">{{ $user->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>

                    @if($user->role && $user->role->permissions)
                    <div class="col-12">
                        <div class="d-flex flex-column gap-3">
                            <span class="fw-semibold text-primary-light text-sm">İzinler</span>
                            <div class="d-flex flex-wrap gap-2">
                                @if(in_array('*', $user->role->permissions))
                                    <span class="badge bg-danger text-sm fw-semibold px-12 py-6 radius-4 text-white d-inline-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:crown-outline"></iconify-icon>
                                        TÜM İZİNLER (GOD MODE)
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
                                    @foreach($user->role->permissions as $permission)
                                        <span class="badge bg-info text-sm fw-semibold px-12 py-6 radius-4 text-white">
                                            {{ $permissionLabels[$permission] ?? $permission }}
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
