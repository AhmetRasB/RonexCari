<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\FixedSeriesSetting;
use Illuminate\Http\Request;

class FixedSeriesSettingController extends Controller
{
    /**
     * Sabit seri ayarlarını listele
     */
    public function index()
    {
        $settings = FixedSeriesSetting::getAllSettings();
        return view('products.fixed-series-settings.index', compact('settings'));
    }

    /**
     * Sabit seri ayarlarını düzenle
     */
    public function edit(FixedSeriesSetting $fixedSeriesSetting)
    {
        return view('products.fixed-series-settings.edit', compact('fixedSeriesSetting'));
    }

    /**
     * Sabit seri ayarlarını güncelle
     */
    public function update(Request $request, FixedSeriesSetting $fixedSeriesSetting)
    {
        $validated = $request->validate([
            'sizes' => 'required|array|min:1',
            'sizes.*' => 'required|string|max:10',
        ]);

        $fixedSeriesSetting->update([
            'sizes' => $validated['sizes']
        ]);

        return redirect()->route('products.fixed-series-settings.index')
            ->with('success', 'Sabit seri ayarları başarıyla güncellendi.');
    }

    /**
     * Varsayılan ayarları oluştur
     */
    public function createDefaults()
    {
        $defaultSettings = [
            ['series_size' => 5, 'sizes' => ['XS', 'S', 'M', 'L', 'XL']],
            ['series_size' => 6, 'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL']],
            ['series_size' => 7, 'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL']],
        ];

        foreach ($defaultSettings as $setting) {
            FixedSeriesSetting::updateOrCreate(
                ['series_size' => $setting['series_size']],
                ['sizes' => $setting['sizes']]
            );
        }

        return redirect()->route('products.fixed-series-settings.index')
            ->with('success', 'Varsayılan sabit seri ayarları oluşturuldu.');
    }
}
