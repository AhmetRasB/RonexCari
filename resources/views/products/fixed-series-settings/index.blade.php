@extends('layout.layout')

@section('title', 'Sabit Seri Ayarları')
@section('subTitle', 'Sabit Seri Beden Ayarları')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Sabit Seri Ayarları</h5>
                <div>
                    @if($settings->count() == 0)
                        <a href="{{ route('products.fixed-series-settings.create-defaults') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i>Varsayılan Ayarları Oluştur
                        </a>
                    @endif
                    <a href="{{ route('products.series.index') }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i>Seri Ürünlere Dön
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($settings->count() > 0)
                    <div class="row">
                        @foreach($settings as $setting)
                            <div class="col-md-4 mb-4">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-stack-line me-2"></i>{{ $setting->series_size }}'li Seri
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <strong>Bedenler:</strong>
                                            <div class="mt-2">
                                                @foreach($setting->sizes as $size)
                                                    <span class="badge bg-primary me-1 mb-1">{{ $size }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <a href="{{ route('products.fixed-series-settings.edit', $setting) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="ri-edit-line me-1"></i>Düzenle
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="ri-settings-3-line text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-muted">Sabit Seri Ayarları</h5>
                        <p class="text-muted">Sabit seri beden ayarlarını yapılandırmak için varsayılan ayarları oluşturun.</p>
                        <a href="{{ route('products.fixed-series-settings.create-defaults') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i>Varsayılan Ayarları Oluştur
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
