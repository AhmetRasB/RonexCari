@extends('layout.layout')

@section('title', 'Kategoriler')
@section('subTitle', 'Ürün Kategorileri')

@section('content')
<div class="row">
    <div class="col-12 d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Kategoriler</h5>
        <a href="{{ route('products.categories.create') }}" class="btn btn-primary">Yeni Kategori</a>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ad</th>
                                <th>Durum</th>
                                <th>Oluşturma</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $i => $cat)
                            <tr>
                                <td>{{ $categories->firstItem() + $i }}</td>
                                <td>{{ $cat->name }}</td>
                                <td>
                                    <span class="badge {{ $cat->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $cat->is_active ? 'Aktif' : 'Pasif' }}</span>
                                </td>
                                <td>{{ $cat->created_at?->format('d.m.Y H:i') }}</td>
                                <td class="d-flex gap-2">
                                    <a href="{{ route('products.categories.edit', $cat) }}" class="btn btn-sm btn-warning">Düzenle</a>
                                    <form method="POST" action="{{ route('products.categories.destroy', $cat) }}" onsubmit="return confirm('Silinsin mi?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Sil</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Kategori bulunamadı</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection


