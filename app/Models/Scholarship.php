<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    protected $primaryKey = 'scholarship_id';
    protected $fillable = [
        'scholarship_name',
        'scholarship_type',
        'allowed_program', 
        'total_beneficiaries',
        'opening_date',
        'closing_date',
        'description',
        'is_active',
        'is_hided',
        'sponsor_id'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'is_hided' => 'boolean',
        'opening_date' => 'date',
        'closing_date' => 'date'
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class, 'sponsor_id', 'sponsor_id');
    }

    // Many-to-many relationship with countries
    public function countries()
    {
        return $this->belongsToMany(
            Country::class,
            'country_scholarship', // Make sure this matches your table name
            'scholarship_id',
            'country_id',
            'scholarship_id',
            'country_id'
        );
    }

    // Many-to-many relationship with universities - UPDATED TABLE NAME
    public function universities()
    {
        return $this->belongsToMany(
            University::class,
            'university_scholarship', // Changed to match your table name
            'scholarship_id',
            'university_id',
            'scholarship_id',
            'university_id'
        );
    }
}