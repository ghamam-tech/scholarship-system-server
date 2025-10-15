<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $primaryKey = 'country_id';
    protected $fillable = ['country_name', 'country_code', 'is_active'];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function universities()
    {
        return $this->hasMany(University::class, 'country_id', 'country_id');
    }

    // Many-to-many relationship with scholarships
    public function scholarships()
    {
        return $this->belongsToMany(
            Scholarship::class,
            'country_scholarship', 
            'country_id',
            'scholarship_id',
            'country_id',
            'scholarship_id'
        );
    }
}