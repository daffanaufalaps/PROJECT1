<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FvFactor extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'site_class',
        's1_min',
        's1_max',
        'fv_value',
    ];

    protected function casts(): array
    {
        return [
            's1_min' => 'decimal:4',
            's1_max' => 'decimal:4',
            'fv_value' => 'decimal:4',
        ];
    }

    /**
     * Find Fv value for a given site class and S1 value
     */
    public static function findFvValue(string $siteClass, float $s1): ?float
    {
        $factor = static::where('site_class', $siteClass)
            ->where('s1_min', '<=', $s1)
            ->where('s1_max', '>=', $s1)
            ->first();

        return $factor?->fv_value;
    }
}
