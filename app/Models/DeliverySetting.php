<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliverySetting extends Model
{
    protected $fillable = [
        'base_cost',
        'cost_per_km',
        'free_km',
        'fee_min',
        'fee_max',
    ];

    protected $casts = [
        'base_cost' => 'float',
        'cost_per_km' => 'float',
        'free_km' => 'float',
        'fee_min' => 'float',
        'fee_max' => 'float',
    ];

    /**
     * Singleton: returns the single config row, creating it with defaults if absent.
     */
    public static function getConfig(): self
    {
        $config = static::first();

        if (! $config) {
            $config = static::create([
                'base_cost' => 1.50,
                'cost_per_km' => 0.50,
                'free_km' => 0.00,
                'fee_min' => 2.00,
                'fee_max' => 15.00,
            ]);
        }

        return $config;
    }
}
