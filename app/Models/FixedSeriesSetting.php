<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedSeriesSetting extends Model
{
    protected $fillable = [
        'series_size',
        'sizes'
    ];

    protected $casts = [
        'sizes' => 'array'
    ];

    /**
     * Seri boyutuna göre ayarları getir
     */
    public static function getSizesForSeries($seriesSize)
    {
        $setting = self::where('series_size', $seriesSize)->first();
        return $setting ? $setting->sizes : [];
    }

    /**
     * Tüm sabit seri ayarlarını getir
     */
    public static function getAllSettings()
    {
        return self::orderBy('series_size')->get();
    }
}
