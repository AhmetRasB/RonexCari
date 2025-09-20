@extends('layout.layout')

@php
    $title='Hesap Seçimi';
    $subTitle = 'Çalışmak istediğiniz hesabı seçiniz';
@endphp

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Hesap Tutma Sistemi</h5>
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

                <div class="row">
                    @foreach($accounts as $account)
                        <div class="col-md-6 mb-4">
                            <form method="POST" action="{{ route('account.store') }}" class="h-100">
                                @csrf
                                <input type="hidden" name="account_id" value="{{ $account->id }}">
                                
                                <div class="card h-100 border-2 hover-shadow-lg transition-all duration-300 cursor-pointer" onclick="this.closest('form').submit()">
                                    <div class="card-body text-center p-4">
                                        <div class="mb-4">
                                            <div class="w-20 h-20 {{ $account->code === 'ronex1' ? 'bg-primary-100' : 'bg-success-100' }} rounded-circle d-flex align-items-center justify-content-center mx-auto">
                                                <iconify-icon icon="heroicons:building-office-2" class="text-3xl {{ $account->code === 'ronex1' ? 'text-primary-600' : 'text-success-600' }}"></iconify-icon>
                                            </div>
                                        </div>
                                        
                                        <h4 class="card-title mb-3 {{ $account->code === 'ronex1' ? 'text-primary-600' : 'text-success-600' }}">
                                            {{ $account->name }}
                                        </h4>
                                        
                                        @if($account->company_name)
                                            <p class="text-muted mb-2">
                                                <iconify-icon icon="heroicons:building-office" class="me-1"></iconify-icon>
                                                {{ $account->company_name }}
                                            </p>
                                        @endif
                                        
                                        @if($account->address)
                                            <p class="text-muted mb-2">
                                                <iconify-icon icon="heroicons:map-pin" class="me-1"></iconify-icon>
                                                {{ $account->address }}
                                                @if($account->city)
                                                    , {{ $account->city }}
                                                @endif
                                            </p>
                                        @endif
                                        
                                        @if($account->phone)
                                            <p class="text-muted mb-2">
                                                <iconify-icon icon="heroicons:phone" class="me-1"></iconify-icon>
                                                {{ $account->phone }}
                                            </p>
                                        @endif
                                        
                                        <p class="text-muted mb-4">{{ $account->description }}</p>
                                        
                                        <button type="submit" class="btn {{ $account->code === 'ronex1' ? 'btn-primary' : 'btn-success' }} px-4 py-2">
                                            <iconify-icon icon="heroicons:check" class="me-2"></iconify-icon>
                                            Bu Hesabı Seç
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted">
                        <iconify-icon icon="heroicons:information-circle" class="me-1"></iconify-icon>
                        Her hesap ayrı finansal veriler tutar. Faturalar, giderler ve gelirler hesap bazında ayrı işlenir.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
