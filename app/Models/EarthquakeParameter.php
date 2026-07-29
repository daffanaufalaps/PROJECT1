<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EarthquakeParameter extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'grid_id',
        'ss',
        's1',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'ss' => 'decimal:4',
            's1' => 'decimal:4',
            'metadata' => 'array',
        ];
    }
}
