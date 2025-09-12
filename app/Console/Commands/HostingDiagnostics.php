<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HostingDiagnostics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hosting:diagnose {--test-api : Test currency API endpoints}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose hosting environment for potential issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Hosting Environment Diagnostics');
        $this->info('=====================================');

        // Check PHP version
        $this->checkPhpVersion();
        
        // Check required extensions
        $this->checkExtensions();
        
        // Check PHP settings
        $this->checkPhpSettings();
        
        // Check file permissions
        $this->checkFilePermissions();
        
        // Check database connection
        $this->checkDatabaseConnection();
        
        // Test currency API if requested
        if ($this->option('test-api')) {
            $this->testCurrencyApi();
        }

        $this->info('✅ Diagnostics completed!');
    }

    private function checkPhpVersion()
    {
        $this->info("\n📋 PHP Version Check:");
        $phpVersion = PHP_VERSION;
        $requiredVersion = '8.1.0';
        
        if (version_compare($phpVersion, $requiredVersion, '>=')) {
            $this->info("✅ PHP Version: {$phpVersion} (Required: {$requiredVersion})");
        } else {
            $this->error("❌ PHP Version: {$phpVersion} (Required: {$requiredVersion})");
        }
    }

    private function checkExtensions()
    {
        $this->info("\n📋 Required Extensions:");
        
        $requiredExtensions = [
            'curl' => 'cURL extension for HTTP requests',
            'json' => 'JSON extension for data parsing',
            'openssl' => 'OpenSSL extension for HTTPS',
            'mbstring' => 'Multibyte string support',
            'fileinfo' => 'File information support',
            'gd' => 'GD extension for image processing',
        ];

        foreach ($requiredExtensions as $ext => $description) {
            if (extension_loaded($ext)) {
                $this->info("✅ {$ext}: {$description}");
            } else {
                $this->error("❌ {$ext}: {$description} - MISSING");
            }
        }
    }

    private function checkPhpSettings()
    {
        $this->info("\n📋 PHP Settings:");
        
        $settings = [
            'memory_limit' => ['current' => ini_get('memory_limit'), 'recommended' => '256M'],
            'max_execution_time' => ['current' => ini_get('max_execution_time'), 'recommended' => '60'],
            'allow_url_fopen' => ['current' => ini_get('allow_url_fopen') ? 'On' : 'Off', 'recommended' => 'On'],
            'max_input_vars' => ['current' => ini_get('max_input_vars'), 'recommended' => '3000'],
            'upload_max_filesize' => ['current' => ini_get('upload_max_filesize'), 'recommended' => '64M'],
            'post_max_size' => ['current' => ini_get('post_max_size'), 'recommended' => '64M'],
        ];

        foreach ($settings as $setting => $values) {
            $current = $values['current'];
            $recommended = $values['recommended'];
            
            if ($this->isSettingOk($setting, $current, $recommended)) {
                $this->info("✅ {$setting}: {$current} (Recommended: {$recommended})");
            } else {
                $this->warn("⚠️  {$setting}: {$current} (Recommended: {$recommended})");
            }
        }
    }

    private function isSettingOk($setting, $current, $recommended)
    {
        switch ($setting) {
            case 'memory_limit':
                return $this->compareMemoryLimit($current, $recommended);
            case 'max_execution_time':
                return $current >= 60 || $current == 0; // 0 means unlimited
            case 'allow_url_fopen':
                return $current === 'On';
            case 'max_input_vars':
                return $current >= 3000;
            case 'upload_max_filesize':
            case 'post_max_size':
                return $this->compareFileSize($current, $recommended);
            default:
                return true;
        }
    }

    private function compareMemoryLimit($current, $recommended)
    {
        $currentBytes = $this->convertToBytes($current);
        $recommendedBytes = $this->convertToBytes($recommended);
        return $currentBytes >= $recommendedBytes;
    }

    private function compareFileSize($current, $recommended)
    {
        $currentBytes = $this->convertToBytes($current);
        $recommendedBytes = $this->convertToBytes($recommended);
        return $currentBytes >= $recommendedBytes;
    }

    private function convertToBytes($value)
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    private function checkFilePermissions()
    {
        $this->info("\n📋 File Permissions:");
        
        $directories = [
            'storage' => 'Storage directory',
            'bootstrap/cache' => 'Bootstrap cache directory',
            'public' => 'Public directory',
        ];

        foreach ($directories as $dir => $description) {
            if (is_dir($dir)) {
                if (is_writable($dir)) {
                    $this->info("✅ {$dir}: {$description} - Writable");
                } else {
                    $this->error("❌ {$dir}: {$description} - NOT WRITABLE");
                }
            } else {
                $this->error("❌ {$dir}: {$description} - NOT FOUND");
            }
        }
    }

    private function checkDatabaseConnection()
    {
        $this->info("\n📋 Database Connection:");
        
        try {
            \DB::connection()->getPdo();
            $this->info("✅ Database connection successful");
            
            // Test a simple query
            \DB::select('SELECT 1 as test');
            $this->info("✅ Database query test successful");
            
        } catch (\Exception $e) {
            $this->error("❌ Database connection failed: " . $e->getMessage());
        }
    }

    private function testCurrencyApi()
    {
        $this->info("\n📋 Currency API Test:");
        
        try {
            $currencyService = new CurrencyService();
            $rates = $currencyService->getExchangeRates();
            
            if (isset($rates['USD']) && isset($rates['EUR'])) {
                $this->info("✅ Currency API working");
                $this->info("   USD Rate: " . ($rates['USD'] ?? 'N/A'));
                $this->info("   EUR Rate: " . ($rates['EUR'] ?? 'N/A'));
                $this->info("   API Version: " . ($rates['api_version'] ?? 'N/A'));
            } else {
                $this->warn("⚠️  Currency API returned null values - using fallback");
            }
            
            // Test all API versions
            $testRates = $currencyService->getTestRates();
            $this->info("\n📋 API Version Tests:");
            
            foreach ($testRates as $version => $result) {
                $status = $result['status'];
                $icon = $status === 'success' ? '✅' : '❌';
                $this->info("   {$icon} {$version}: {$status}");
                
                if ($result['error']) {
                    $this->warn("      Error: " . $result['error']);
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Currency API test failed: " . $e->getMessage());
        }
    }
}