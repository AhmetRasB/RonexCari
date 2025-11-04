@extends('layout.layout')

@section('title', 'Markalar')
@section('subTitle', 'Ürün Markaları')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Markalar</h5>
        <a href="{{ route('products.brands.create') }}" class="btn btn-primary">Yeni Marka</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ad</th>
                        <th>Durum</th>
                        <th class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                    <tr>
                        <td>{{ $brand->id }}</td>
                        <td>{{ $brand->name }}</td>
                        <td>
                            @if($brand->is_active)
                                <span class="badge bg-soft-success text-success">Aktif</span>
                            @else
                                <span class="badge bg-soft-secondary text-secondary">Pasif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('products.brands.edit', $brand) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
                            <form action="{{ route('products.brands.destroy', $brand) }}" method="POST" class="d-inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Henüz marka eklenmemiş.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $brands->links() }}</div>
    </div>
</div>
@endsection


