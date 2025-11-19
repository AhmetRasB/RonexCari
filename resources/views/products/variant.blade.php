@extends('layout.layout')

@section('title', $type === 'series' ? 'Renk Detayı (Seri)' : 'Renk Detayı (Ürün)')
@section('subTitle', 'Renk Varyantı Odaklı Görünüm')

@section('content')
<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h5 class="card-title mb-0">
						@if($type === 'series')
							{{ $series->name }}
						@else
							{{ $product->name }}
						@endif
					</h5>
					<p class="text-muted mb-0">Seçilen Renk: <span class="badge bg-secondary">{{ $variant->color }}</span></p>
				</div>
				<div class="d-flex gap-2">
					@if($type === 'series')
						<a href="{{ route('products.series.show', $series) }}" class="btn btn-outline-primary">
							<i class="ri-arrow-left-line me-1"></i>Tüm Seri Detayı
						</a>
					@else
						<a href="{{ route('products.show', $product) }}" class="btn btn-outline-primary">
							<i class="ri-arrow-left-line me-1"></i>Tüm Ürün Detayı
						</a>
					@endif
				</div>
			</div>
			<div class="card-body">
				<div class="row g-4">
					<div class="col-md-8">
						<div class="card mb-4">
							<div class="card-header">
								<h6 class="card-title mb-0">Renk Bilgileri</h6>
							</div>
							<div class="card-body">
								<div class="row g-3">
									<div class="col-md-4">
										<label class="form-label text-muted small">Renk</label>
										<div class="form-control-plaintext fw-semibold">{{ $variant->color }}</div>
									</div>
									<div class="col-md-4">
										<label class="form-label text-muted small">Stok</label>
										<div class="form-control-plaintext fw-semibold text-success">{{ number_format($variant->stock_quantity ?? 0) }} Adet</div>
									</div>
									<div class="col-md-4">
										<label class="form-label text-muted small">Kritik Stok</label>
										<div class="form-control-plaintext fw-semibold text-warning">{{ number_format($variant->critical_stock ?? 0) }} Adet</div>
									</div>
								</div>
							</div>
						</div>

						@if($type === 'series' && $series && $series->seriesItems && $series->seriesItems->count() > 0)
							<div class="card">
								<div class="card-header">
									<h6 class="card-title mb-0">Seri İçerikleri (Bedenler)</h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table class="table table-sm">
											<thead>
												<tr>
													<th>Beden</th>
													<th class="text-center">Paket Adedi</th>
												</tr>
											</thead>
											<tbody>
												@foreach($series->seriesItems as $item)
													<tr>
														<td><span class="badge bg-light text-dark">{{ $item->size }}</span></td>
														<td class="text-center">{{ $item->quantity_per_series }}</td>
													</tr>
												@endforeach
											</tbody>
										</table>
									</div>
								</div>
							</div>
						@endif
					</div>
					<div class="col-md-4">
						<div class="card">
							<div class="card-header">
								<h6 class="card-title mb-0"><i class="ri-barcode-line me-1"></i> Kod Bilgileri</h6>
							</div>
							<div class="card-body">
								<div class="mb-3">
									<label class="form-label text-muted small">Varyant Barkodu</label>
									<div class="fw-semibold font-monospace">{{ $variant->barcode ?: '-' }}</div>
								</div>
								<div class="row g-3">
									<div class="col-6 text-center">
										<small class="text-muted d-block">Barkod</small>
										<div id="barcodeSvg" style="height: 40px; display: flex; align-items: center; justify-content: center;">
											<svg style="width: 100%; height: 40px;"></svg>
										</div>
										<div class="small mt-1 font-monospace">{{ $variant->barcode ?: '' }}</div>
									</div>
									<div class="col-6 text-center">
										<small class="text-muted d-block">QR Kod</small>
										<div id="qrBox" style="width: 80px; height: 80px; margin: 0 auto;">
											<img id="qrImg" src="" alt="QR" class="img-fluid" style="max-width: 80px; max-height: 80px;" />
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
	const barcode = @json($variant->barcode);
	const qrUrl = window.location.href;

	// Generate barcode if exists
	try {
		if (barcode) {
			JsBarcode("#barcodeSvg svg", barcode, {
				format: "CODE128",
				displayValue: false,
				margin: 0,
				height: 40
			});
		}
	} catch (e) {
		console.log('Barcode generation error:', e);
	}

	// Generate QR
	const qrImg = document.getElementById('qrImg');
	qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=${encodeURIComponent(qrUrl)}`;
});
</script>
@endpush


