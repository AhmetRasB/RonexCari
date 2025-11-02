<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    // Trunçgil API URLs - try in order
    private const TRUNCIL_URLS = [
        'v1' => 'https://finans.truncgil.com/today.json',
        'v3' => 'https://finans.truncgil.com/v3/today.json', 
        'v4' => 'https://finans.truncgil.com/v4/today.json'
    ];
    
    private const CACHE_KEY = 'exchange_rates';
    private const CACHE_DURATION = 300; //5 minutes (balanced updates)

    /**
     * Get current exchange rates from multiple sources
     */
    public function getExchangeRates(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            // Try all Trunçgil API versions in order
            foreach (self::TRUNCIL_URLS as $version => $url) {
                Log::info("Trying Trunçgil API {$version}", ['url' => $url]);
                
                $rates = $this->fetchFromTruncilAPI($version, $url);
                if ($rates) {
                    Log::info("Successfully fetched rates from Trunçgil API {$version}", $rates);
                    return array_merge($rates, ['api_version' => $version, 'api_url' => $url]);
                }
                
                Log::warning("Trunçgil API {$version} failed, trying next version");
            }

            // If all APIs fail, return null values for manual input
            Log::warning('All Trunçgil API versions failed, returning null values for manual input');
            return [
                'USD' => null,
                'EUR' => null,
                'api_version' => 'none',
                'api_url' => 'failed'
            ];
        });
    }

    /**
     * Fetch rates from Trunçgil API
     */
    private function fetchFromTruncilAPI(string $version, string $url): ?array
    {
        try {
            Log::info("Fetching from Trunçgil API {$version}", ['url' => $url]);
            
            $response = Http::timeout(10)->get($url);
            
            if (!$response->successful()) {
                Log::warning("Trunçgil API {$version} request failed", ['status' => $response->status(), 'url' => $url]);
                return null;
            }

            $data = $response->json();
            
            if (!$data) {
                Log::error("Invalid Trunçgil API {$version} response structure");
                return null;
            }

            // Parse based on API version
            $rates = $this->parseRatesByVersion($data, $version);
            
            if (isset($rates['USD']) && isset($rates['EUR'])) {
                Log::info("Successfully fetched rates from Trunçgil API {$version}", $rates);
                return $rates;
            }
            
            Log::error("USD or EUR rates not found in Trunçgil API {$version} response", [
                'found_rates' => array_keys($rates),
                'version' => $version
            ]);
            return null;
            
        } catch (\Exception $e) {
            Log::error("Exception while fetching from Trunçgil API {$version}", [
                'error' => $e->getMessage(),
                'url' => $url,
                'version' => $version
            ]);
            return null;
        }
    }

    /**
     * Parse rates based on API version
     */
    private function parseRatesByVersion(array $data, string $version): array
    {
        $rates = [];

        switch ($version) {
            case 'v1':
                // v1 format: {"USD":{"Alış":"41,2784","Satış":"41,2921"}}
                if (isset($data['USD']['Alış'])) {
                    $rates['USD'] = $this->parseRate($data['USD']['Alış']);
                }
                if (isset($data['EUR']['Alış'])) {
                    $rates['EUR'] = $this->parseRate($data['EUR']['Alış']);
                }
                break;

            case 'v3':
                // v3 format: {"USD":{"Buying":"41,2784","Selling":"41,2921"}}
                if (isset($data['USD']['Buying'])) {
                    $rates['USD'] = $this->parseRate($data['USD']['Buying']);
                }
                if (isset($data['EUR']['Buying'])) {
                    $rates['EUR'] = $this->parseRate($data['EUR']['Buying']);
                }
                break;

            case 'v4':
                // v4 format: array of objects [{"code":"USD","selling":41.2921}]
                if (is_array($data)) {
                    foreach ($data as $item) {
                        if (isset($item['code']) && isset($item['selling'])) {
                            if ($item['code'] === 'USD') {
                                $rates['USD'] = (float) $item['selling'];
                            } elseif ($item['code'] === 'EUR') {
                                $rates['EUR'] = (float) $item['selling'];
                            }
                        }
                    }
                }
                break;
        }

        return $rates;
    }

    /**
     * Parse rate string to float (handle Turkish comma format)
     */
    private function parseRate(string $rateStr): float
    {
        // Convert Turkish format "41,2784" to "41.2784"
        $rateStr = str_replace(',', '.', $rateStr);
        return (float) $rateStr;
    }

    /**
     * Clear exchange rate cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Log::info('Exchange rates cache cleared');
    }

    /**
     * Set manual exchange rates and cache them
     */
    public function setManualRates(float $usdRate = null, float $eurRate = null): array
    {
        $manualRates = [
            'USD' => $usdRate ?? 41.29,
            'EUR' => $eurRate ?? 48.55,
            'api_version' => 'manual',
            'api_url' => 'manual_input'
        ];

        // Cache the manual rates
        Cache::put(self::CACHE_KEY, $manualRates, self::CACHE_DURATION);
        
        Log::info('Manual exchange rates set and cached', $manualRates);
        
        return $manualRates;
    }

}