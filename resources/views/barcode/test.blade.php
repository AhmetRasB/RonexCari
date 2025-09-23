@extends('layout.layout')

@section('title', 'Barkod Testi')
@section('subTitle', 'Canlı Önizleme')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">QR Kod</div>
            <div class="card-body text-center">
                {!! $qrSvg !!}
                <div class="small text-muted mt-2">{{ $qrValue }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">Barkod</div>
            <div class="card-body text-center">
                {!! $barcodeSvg !!}
                <div class="small text-muted mt-2">{{ $barcodeValue }}</div>
            </div>
        </div>
    </div>
</div>
@endsection


