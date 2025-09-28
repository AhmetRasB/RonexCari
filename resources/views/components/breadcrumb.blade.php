<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 gap-md-3 mb-3 mb-md-24">
    <h6 class="fw-semibold mb-0 text-sm text-md-base"><?php echo $title;?></h6>
    <ul class="d-flex align-items-center gap-1 gap-md-2">
        <li class="fw-medium">
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-sm text-md-lg"></iconify-icon>
                <span class="d-none d-sm-inline">Dashboard</span>
                <span class="d-sm-none">Ana</span>
            </a>
        </li>
        <li class="text-muted">-</li>
        <li class="fw-medium text-sm text-md-base"><?php echo $subTitle;?></li>
    </ul>
</div>