<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $primaryKey = 'country_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'country_name',
        'country_code',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function universities(): HasMany
    {
        return $this->hasMany(University::class, 'country_id', 'country_id');
    }
}
