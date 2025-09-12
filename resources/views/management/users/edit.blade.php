@extends('layout.layout')

@section('title', 'Kullanıcı Düzenle')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Kullanıcı Düzenle</h6>
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
        <li class="fw-medium">Düzenle</li>
    </ul>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center gap-2">
            <iconify-icon icon="solar:user-speak-rounded-outline" class="text-xl"></iconify-icon>
            <h6 class="mb-0">Kullanıcı Bilgilerini Düzenle</h6>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('management.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row gy-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">İsim <span class="text-danger-600">*</span></label>
                    <input type="text" class="form-control radius-8 @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" placeholder="Kullanıcı adını girin" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Email <span class="text-danger-600">*</span></label>
                    <input type="email" class="form-control radius-8 @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}" placeholder="Email adresini girin" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Rol <span class="text-danger-600">*</span></label>
                    <select class="form-control radius-8 @error('role_id') is-invalid @enderror" name="role_id" required>
                        <option value="">Rol Seçin</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Yeni Şifre</label>
                    <input type="password" class="form-control radius-8 @error('password') is-invalid @enderror" name="password" placeholder="Değiştirmek istemiyorsanız boş bırakın">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Şifreyi değiştirmek istemiyorsanız boş bırakabilirsiniz.</small>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Şifre Onayı</label>
                    <input type="password" class="form-control radius-8 @error('password_confirmation') is-invalid @enderror" name="password_confirmation" placeholder="Yeni şifreyi tekrar girin">
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-sm-end justify-content-center gap-3">
                        <a href="{{ route('management.users.index') }}" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-56 py-11 radius-8">
                            İptal
                        </a>
                        <button type="submit" class="btn btn-primary border border-primary-600 text-md px-56 py-12 radius-8">
                            Güncelle
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
