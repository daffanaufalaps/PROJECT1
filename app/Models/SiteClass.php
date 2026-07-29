<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SiteClass extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'site_class',
        'description',
        'vs30_min',
        'vs30_max',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'vs30_min' => 'decimal:2',
            'vs30_max' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function faFactors()
    {
        return $this->hasMany(FaFactor::class, 'site_class', 'site_class');
    }

    public function fvFactors()
    {
        return $this->hasMany(FvFactor::class, 'site_class', 'site_class');
    }
}
