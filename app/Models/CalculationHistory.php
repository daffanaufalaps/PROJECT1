<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalculationHistory extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'latitude',
        'longitude',
        'ss',
        's1',
        'fa',
        'fv',
        'sms',
        'sm1',
        'sds',
        'sd1',
        'pga',
        'sig_bmkg_scale',
        'risk_category',
        'kds',
        'narration',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'ss' => 'decimal:4',
            's1' => 'decimal:4',
            'fa' => 'decimal:4',
            'fv' => 'decimal:4',
            'sms' => 'decimal:4',
            'sm1' => 'decimal:4',
            'sds' => 'decimal:4',
            'sd1' => 'decimal:4',
            'pga' => 'decimal:4',
            'mmi' => 'decimal:2',
        ];
    }
}