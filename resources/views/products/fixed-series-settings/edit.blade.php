@extends('layout.layout')

@section('title', 'Sabit Seri Düzenle: ' . $fixedSeriesSetting->series_size . 'li Seri')
@section('subTitle', 'Sabit Seri Beden Ayarlarını Düzenle')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">{{ $fixedSeriesSetting->series_size }}'li Seri Beden Ayarları</h5>
                <a href="{{ route('products.fixed-series-settings.index') }}" class="btn btn-secondary">
                    <i class="ri-arrow-left-line me-1"></i>Geri Dön
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('products.fixed-series-settings.update', $fixedSeriesSetting) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="ri-information-line me-2"></i>
                                <strong>Bilgi:</strong> {{ $fixedSeriesSetting->series_size }}'li seri için {{ $fixedSeriesSetting->series_size }} adet beden seçmelisiniz.
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3">Beden Seçimi</h6>
                            <div id="sizes-container">
                                @foreach($fixedSeriesSetting->sizes as $index => $size)
                                    <div class="row mb-3 size-row">
                                        <div class="col-md-11">
                                            <select class="form-control @error('sizes.' . $index) is-invalid @enderror" 
                                                    name="sizes[]" required>
                                                <option value="">Beden seçin</option>
                                                @php
                                                    $sizeOptions = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', '28', '30', '32', '34', '36', '38', '40', '42', '44', '46', '48', '50'];
                                                @endphp
                                                @foreach($sizeOptions as $option)
                                                    <option value="{{ $option }}" {{ $size == $option ? 'selected' : '' }}>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('sizes.' . $index)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-1">
                                            @if($index > 0)
                                                <button type="button" class="btn btn-outline-danger remove-size">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-outline-danger remove-size" disabled>
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="text-end">
                                <button type="button" class="btn btn-outline-primary" id="add-size">
                                    <i class="ri-add-line me-1"></i>Beden Ekle
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <div class="alert alert-warning" id="size-warning" style="display: none;">
                                <i class="ri-alert-line me-2"></i>
                                <span id="warning-text"></span>
                            </div>
                        </div>
                        
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="ri-save-line me-1"></i>Değişiklikleri Kaydet
                            </button>
                            <a href="{{ route('products.fixed-series-settings.index') }}" class="btn btn-secondary">
                                <i class="ri-cancel-line me-1"></i>İptal
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sizesContainer = document.getElementById('sizes-container');
    const addSizeBtn = document.getElementById('add-size');
    const submitBtn = document.getElementById('submit-btn');
    const warningDiv = document.getElementById('size-warning');
    const warningText = document.getElementById('warning-text');
    const requiredSize = {{ $fixedSeriesSetting->series_size }};
    
    const sizeOptions = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', '28', '30', '32', '34', '36', '38', '40', '42', '44', '46', '48', '50'];
    
    // Beden ekleme
    addSizeBtn.addEventListener('click', function() {
        const currentRows = document.querySelectorAll('.size-row').length;
        
        if (currentRows >= requiredSize) {
            showWarning(`Maksimum ${requiredSize} beden ekleyebilirsiniz.`);
            return;
        }
        
        const newRow = document.createElement('div');
        newRow.className = 'row mb-3 size-row';
        newRow.innerHTML = `
            <div class="col-md-11">
                <select class="form-control" name="sizes[]" required>
                    <option value="">Beden seçin</option>
                    ${sizeOptions.map(size => `<option value="${size}">${size}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger remove-size">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        `;
        
        sizesContainer.appendChild(newRow);
        updateRemoveButtons();
        hideWarning();
    });
    
    // Beden silme
    document.addEventListener('click', function(e) {
        if (e.target && e.target.closest('.remove-size')) {
            const currentRows = document.querySelectorAll('.size-row').length;
            if (currentRows > 1) {
                e.target.closest('.size-row').remove();
                updateRemoveButtons();
                hideWarning();
            }
        }
    });
    
    function updateRemoveButtons() {
        const currentRows = document.querySelectorAll('.size-row').length;
        const removeButtons = document.querySelectorAll('.remove-size');
        removeButtons.forEach((button, index) => {
            button.disabled = currentRows <= 1 || index === 0;
        });
        
        // Add size button'u kontrol et
        if (currentRows >= requiredSize) {
            addSizeBtn.style.display = 'none';
        } else {
            addSizeBtn.style.display = 'inline-block';
        }
    }
    
    function showWarning(message) {
        warningText.textContent = message;
        warningDiv.style.display = 'block';
    }
    
    function hideWarning() {
        warningDiv.style.display = 'none';
    }
    
    // Form submit validation
    submitBtn.addEventListener('click', function(e) {
        const sizeSelects = document.querySelectorAll('select[name="sizes[]"]');
        const selectedSizes = [];
        let hasEmpty = false;
        
        sizeSelects.forEach(select => {
            if (select.value && select.value.trim() !== '') {
                selectedSizes.push(select.value);
            } else {
                hasEmpty = true;
            }
        });
        
        if (hasEmpty) {
            e.preventDefault();
            showWarning('Tüm beden alanları doldurulmalıdır.');
            return false;
        }
        
        if (selectedSizes.length !== requiredSize) {
            e.preventDefault();
            showWarning(`${requiredSize} adet beden seçmelisiniz. Şu anda ${selectedSizes.length} adet seçili.`);
            return false;
        }
        
        // Duplicate kontrolü kaldırıldı - aynı bedenlerden 2'şer tane olabilir
    });
    
    // Initial setup
    updateRemoveButtons();
});
</script>
@endsection
