<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    protected $primaryKey = 'scholarship_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'scholarship_name',
        'scholarship_type',
        'allowed_program',
        'total_beneficiaries',
        'opening_date',
        'closing_date',
        'description',
        'is_active',
        'is_hidden'
    ];

    protected $casts = [
        'opening_date' => 'date',
        'closing_date' => 'date',
        'is_active' => 'boolean',
        'is_hidden' => 'boolean',
    ];
}
