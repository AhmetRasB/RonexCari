@extends('layout.layout')

@section('title', 'Kullanıcılar')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Kullanıcılar</h6>
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
                Yönetim
            </a>
        </li>
        <li>-</li>
        <li class="fw-medium">Kullanıcılar</li>
    </ul>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <iconify-icon icon="solar:users-group-two-rounded-outline" class="text-xl"></iconify-icon>
            <h6 class="mb-0">Kullanıcı Listesi</h6>
        </div>
        <div class="d-flex gap-2">
            <button id="deleteSelectedBtn" class="btn btn-danger text-sm btn-sm px-12 py-6 rounded-8 d-flex align-items-center gap-2" style="display: none;">
                <iconify-icon icon="solar:trash-bin-minimalistic-outline" class="icon text-xl line-height-1"></iconify-icon>
                Seçilenleri Sil (<span id="selectedCount">0</span>)
            </button>
            <a href="{{ route('management.users.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-6 rounded-8 d-flex align-items-center gap-2">
                <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                Yeni Kullanıcı
            </a>
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
                        <th scope="col">
                            <div class="form-check style-check d-flex align-items-center">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label">S.L</label>
                            </div>
                        </th>
                        <th scope="col">İsim</th>
                        <th scope="col">Email</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Oluşturulma</th>
                        <th scope="col">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $user)
                    <tr>
                        <td>
                            <div class="form-check style-check d-flex align-items-center">
                                <input class="form-check-input row-checkbox" type="checkbox" data-id="{{ $user->id }}">
                                <label class="form-check-label">{{ $index + 1 }}</label>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-md mb-0 fw-medium">{{ $user->name }}</h6>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-md mb-0">{{ $user->email }}</span>
                        </td>
                        <td>
                            @if($user->role)
                                <span class="badge bg-{{ $user->hasRole('god_mode') ? 'danger' : ($user->hasRole('admin') ? 'primary' : 'secondary') }} text-sm fw-semibold px-20 py-9 radius-4 text-white">
                                    {{ $user->role->display_name }}
                                </span>
                            @else
                                <span class="badge bg-warning text-sm fw-semibold px-20 py-9 radius-4 text-white">Rol Yok</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-md mb-0">{{ $user->created_at->format('d.m.Y H:i') }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-10">
                                <a href="{{ route('management.users.show', $user) }}" class="bg-success-focus text-success-main w-32-px h-32-px rounded-circle d-flex justify-content-center align-items-center">
                                    <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                </a>
                                
                                <a href="{{ route('management.users.edit', $user) }}" class="bg-warning-focus text-warning-main w-32-px h-32-px rounded-circle d-flex justify-content-center align-items-center">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>
                                
                                @unless($user->hasRole('god_mode'))
                                <form action="{{ route('management.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-danger-focus text-danger-main w-32-px h-32-px rounded-circle d-flex justify-content-center align-items-center border-0">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </button>
                                </form>
                                @endunless
                            </div>
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

    // Select all functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateDeleteButton();
    });

    // Update delete button visibility
    function updateDeleteButton() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        const countSpan = document.getElementById('selectedCount');
        
        if (checkedBoxes.length > 0) {
            deleteBtn.style.display = 'flex';
            countSpan.textContent = checkedBoxes.length;
        } else {
            deleteBtn.style.display = 'none';
        }
    }

    // Row checkbox change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('row-checkbox')) {
            updateDeleteButton();
        }
    });

    // Delete selected
    document.getElementById('deleteSelectedBtn').addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const ids = Array.from(checkedBoxes).map(cb => cb.dataset.id);
        
        if (ids.length === 0) return;
        
        if (confirm(ids.length + ' kullanıcıyı silmek istediğinizden emin misiniz?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("management.users.bulk-delete") }}';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            
            const idsInput = document.createElement('input');
            idsInput.type = 'hidden';
            idsInput.name = 'ids';
            idsInput.value = JSON.stringify(ids);
            
            form.appendChild(csrfInput);
            form.appendChild(idsInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
</script>
@endpush

