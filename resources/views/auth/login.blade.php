<!DOCTYPE html>
<html lang="tr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('company.name') }} - Giriş Yap</title>
  <link rel="icon" type="image/png" href="{{ asset('assets/images/logo-icon.png') }}" sizes="16x16">
  <!-- remix icon font css  -->
  <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
  <!-- BootStrap css -->
  <link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
  <!-- main css -->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body>

<section class="auth bg-base d-flex flex-wrap">  
    <div class="auth-left d-lg-block d-none">
        <div class="d-flex align-items-center flex-column h-100 justify-content-center">
            <img src="{{ asset('assets/images/loginbigimage.png') }}" alt="Ronex" class="img-fluid" style="max-width: 100%; height: auto;">
        </div>
    </div>
    <div class="auth-right py-32 px-24 d-flex flex-column justify-content-center">
        <div class="max-w-464-px mx-auto w-100">
            <div>
                <a href="{{ route('dashboard') }}" class="mb-40 max-w-290-px d-inline-block">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('company.name') }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div style="display:none; font-size: 24px; font-weight: bold; color: #333;">{{ config('company.name') }}</div>
                </a>
                <h4 class="mb-12">Hesabınıza Giriş Yapın</h4>
                <p class="mb-32 text-secondary-light text-lg">Hoş geldiniz! Lütfen bilgilerinizi girin</p>
            </div>
            
    <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success mb-4">
                    {{ session('status') }}
                </div>
            @endif
            
            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

                <div class="icon-field mb-16">
                    <span class="icon top-50 translate-middle-y">
                        <iconify-icon icon="mage:email"></iconify-icon>
                    </span>
                    <input type="email" 
                           id="email"
                           name="email"
                           class="form-control h-56-px bg-neutral-50 radius-12" 
                           placeholder="E-posta"
                           value="{{ old('email') }}"
                           required 
                           autofocus 
                           autocomplete="username">
                    @error('email')
                        <div class="text-danger mt-2 text-sm">{{ $message }}</div>
                    @enderror
        </div>

                <div class="position-relative mb-20">
                    <div class="icon-field">
                        <span class="icon top-50 translate-middle-y">
                            <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                        </span> 
                        <input type="password" 
                               id="password"
                            name="password"
                               class="form-control h-56-px bg-neutral-50 radius-12" 
                               placeholder="Şifre"
                               required 
                               autocomplete="current-password">
                        @error('password')
                            <div class="text-danger mt-2 text-sm">{{ $message }}</div>
                        @enderror
                    </div>
                    <span class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light" data-toggle="#password"></span>
        </div>

                <div class="mb-20">
                    <div class="d-flex justify-content-between gap-2">
                        <div class="form-check style-check d-flex align-items-center">
                            <input class="form-check-input border border-neutral-300" 
                                   type="checkbox" 
                                   id="remember" 
                                   name="remember"
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Beni Hatırla</label>
                        </div>
                        <span class="text-secondary-light text-sm d-flex align-items-center">
                            Şifrenizi mi unuttunuz? Lütfen yöneticinizle iletişime geçin.
                        </span>
        </div>
        </div>
        
                <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12 mt-32"> 
                    Giriş Yap
                </button>
            </form>
        </div>
    </div>
</section>

  <!-- jQuery library js -->
  <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
  <!-- Bootstrap js -->
  <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
  <!-- Iconify Font js -->
  <script src="{{ asset('assets/js/lib/iconify-icon.min.js') }}"></script>
  
  <!-- main js -->
  <script src="{{ asset('assets/js/app.js') }}"></script>

    <script>
      // ================== Password Show Hide Js Start ==========
      function initializePasswordToggle(toggleSelector) {
        $(toggleSelector).on('click', function() {
            $(this).toggleClass("ri-eye-off-line");
            var input = $($(this).attr("data-toggle"));
            if (input.attr("type") === "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    }
    // Call the function
    initializePasswordToggle('.toggle-password');
  // ========================= Password Show Hide Js End ===========================
  
        // Refresh CSRF token on page load to prevent 419 errors
        document.addEventListener('DOMContentLoaded', function() {
            fetch('/sanctum/csrf-cookie')
                .then(response => response.json())
                .catch(error => {
                    // Ignore errors, just ensure token is refreshed
                });
        });
    </script>

</body>
</html>
