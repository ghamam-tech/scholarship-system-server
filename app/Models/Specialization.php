<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    protected $primaryKey = 'specialization_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'specialization_name',
        'faculty_name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
