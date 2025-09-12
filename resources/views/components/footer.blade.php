<footer class="d-footer">
    <div class="row align-items-center justify-content-between">
        <div class="col-auto">
            <p class="mb-0">© {{ date('Y') }} {{ config('company.name') }}. Tüm Hakları Saklıdır.</p>
        </div>
        <div class="col-auto">
            <p class="mb-0">
                <iconify-icon icon="solar:phone-outline" class="me-1"></iconify-icon>
                {{ config('company.contact.phone') }}
            </p>
        </div>
    </div>
</footer>