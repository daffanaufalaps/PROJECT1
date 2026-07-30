<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaFactor extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'site_class',
        'ss_min',
        'ss_max',
        'fa_value',
    ];

    protected function casts(): array
    {
        return [
            'ss_min' => 'decimal:4',
            'ss_max' => 'decimal:4',
            'fa_value' => 'decimal:4',
        ];
    }

    /**
     * Find Fa value for a given site class and Ss value
     */
    public static function findFaValue(string $siteClass, float $ss): ?float
    {
        $factor = static::where('site_class', $siteClass)
            ->where('ss_min', '<=', $ss)
            ->where('ss_max', '>=', $ss)
            ->first();

        return $factor?->fa_value;
    }
}