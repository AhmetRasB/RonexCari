<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class HostingBladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Hosting-specific Blade directives
        $this->registerHostingDirectives();
    }

    /**
     * Register hosting-specific Blade directives
     */
    private function registerHostingDirectives(): void
    {
        // @hosting directive for hosting-specific content
        Blade::directive('hosting', function ($expression) {
            return "<?php if(app()->environment('production')): ?>";
        });

        Blade::directive('endhosting', function ($expression) {
            return "<?php endif; ?>";
        });

        // @local directive for local development content
        Blade::directive('local', function ($expression) {
            return "<?php if(app()->environment('local')): ?>";
        });

        Blade::directive('endlocal', function ($expression) {
            return "<?php endif; ?>";
        });

        // @currency directive for currency formatting
        Blade::directive('currency', function ($expression) {
            return "<?php echo number_format($expression, 2, ',', '.'); ?>";
        });

        // @currency_symbol directive for currency symbols
        Blade::directive('currency_symbol', function ($expression) {
            return "<?php 
                switch($expression) {
                    case 'USD': echo '$'; break;
                    case 'EUR': echo '€'; break;
                    case 'TRY': echo '₺'; break;
                    default: echo $expression; break;
                }
            ?>";
        });

        // @api_status directive for API status display
        Blade::directive('api_status', function ($expression) {
            return "<?php 
                \$status = $expression;
                if(\$status === 'success') {
                    echo '<span class=\"badge bg-success\">✓ Çalışıyor</span>';
                } elseif(\$status === 'error') {
                    echo '<span class=\"badge bg-danger\">✗ Hata</span>';
                } else {
                    echo '<span class=\"badge bg-warning\">⚠ Test Ediliyor</span>';
                }
            ?>";
        });

        // @fallback directive for fallback content
        Blade::directive('fallback', function ($expression) {
            return "<?php 
                \$value = $expression;
                if(\$value === null || \$value === '') {
                    echo '<span class=\"text-muted\">Manuel giriş gerekli</span>';
                } else {
                    echo \$value;
                }
            ?>";
        });
    }
}
