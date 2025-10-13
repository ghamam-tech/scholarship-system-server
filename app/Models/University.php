<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class University extends Model
{
    protected $primaryKey = 'university_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'university_name',
        'city',
        'is_active',
        'country_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }
}
