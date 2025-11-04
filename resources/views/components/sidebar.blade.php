<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="{{ route('dashboard') }}" class="sidebar-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="site logo" class="light-logo">
            <img src="{{ asset('assets/images/logo-light.png') }}" alt="site logo" class="dark-logo">
            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="site logo" class="logo-icon">
        </a>
    </div>
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">
            <li>
                <a href="{{ route('dashboard') }}">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span class="d-none d-lg-inline">Dashboard</span>
                    <span class="d-lg-none">Ana</span>
                </a>
                    </li>
            <!-- SATIŞLAR (SALES) -->
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:chart-square-outline" class="menu-icon"></iconify-icon>
                    <span class="d-none d-lg-inline">SATIŞLAR</span>
                    <span class="d-lg-none">SATIŞ</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('sales.customers.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Müşteriler</a>
                    </li>
                    <li>
                        <a href="{{ route('sales.invoices.index') }}"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Faturalar</a>
                    </li>
                </ul>
            </li>

            <!-- ALIŞLAR (PURCHASES) - Admin/GodMode Only -->
            @if(auth()->user() && auth()->user()->isAdmin())
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:cart-large-2-outline" class="menu-icon"></iconify-icon>
                    <span class="d-none d-lg-inline">ALIŞLAR</span>
                    <span class="d-lg-none">ALIŞ</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('purchases.suppliers.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Tedarikçiler</a>
                    </li>
                    <li>
                        <a href="{{ route('purchases.invoices.index') }}"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Faturalar</a>
                    </li>
                    <li>
                        <a href="{{ route('finance.supplier-payments.index') }}"><i class="ri-circle-fill circle-icon text成功 w-auto"></i> Tedarikçi Ödemeleri</a>
                    </li>
                </ul>
            </li>
            @endif

            <!-- GİDERLER (EXPENSES) -->
            @if(auth()->user() && auth()->user()->role && auth()->user()->canAccess('expenses'))
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:wallet-money-outline" class="menu-icon"></iconify-icon>
                    <span>GİDERLER</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('expenses.expenses.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Masraflar</a>
                    </li>
                </ul>
            </li>
            @endif

            <!-- ÜRÜN VE HİZMETLER (PRODUCTS AND SERVICES) -->
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:box-outline" class="menu-icon"></iconify-icon>
                    <span>ÜRÜN VE HİZMETLER</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('products.series.index') }}"><i class="ri-circle-fill circle-icon text-success w-auto"></i> Seri Ürünler</a>
                    </li>
                    <li>
                        <a href="{{ route('services.index') }}"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Hizmetler</a>
                    </li>
                    <li>
                        <a href="{{ route('products.categories.index') }}"><i class="ri-circle-fill circle-icon text-success w-auto"></i> Kategoriler</a>
                    </li>
                    <li>
                        <a href="{{ route('products.brands.index') }}"><i class="ri-circle-fill circle-icon text-info w-auto"></i> Markalar</a>
                    </li>
                    <li>
                        <a href="{{ route('barcode.index') }}"><i class="ri-circle-fill circle-icon text-success w-auto"></i> Barkod Bölümü</a>
                    </li>
                </ul>
            </li>

            <!-- RAPOR AL (REPORTS) -->
            @if(auth()->user() && auth()->user()->role && auth()->user()->canAccess('reports'))
            <li>
                <a href="{{ route('reports.index') }}">
                    <iconify-icon icon="solar:chart-outline" class="menu-icon"></iconify-icon>
                    <span>Rapor Al</span>
                </a>
            </li>
            @endif

            <!-- YÖNETİM (MANAGEMENT) -->
            @if(auth()->user() && auth()->user()->role && auth()->user()->canAccess('management'))
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:settings-outline" class="menu-icon"></iconify-icon>
                    <span>YÖNETİM</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('management.users.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Kullanıcılar</a>
                    </li>
                    <li>
                        <a href="{{ route('management.roles.index') }}"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Roller</a>
                    </li>
                    <li>
                        <a href="{{ route('management.employees.index') }}"><i class="ri-circle-fill circle-icon text-success-main w-auto"></i> Çalışanlar</a>
                    </li>
                    <li>
                        <a href="{{ route('account.manage') }}"><i class="ri-circle-fill circle-icon text-info-main w-auto"></i> Hesap Yönetimi</a>
                    </li>
                </ul>
            </li>
            @endif

            <!-- FİNANS (FINANCE) -->
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:calculator-outline" class="menu-icon"></iconify-icon>
                    <span>FİNANS</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('finance.collections.index') }}"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Tahsilatlar</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</aside>