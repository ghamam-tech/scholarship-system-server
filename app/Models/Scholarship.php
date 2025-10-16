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
        'closing_date' => 'date',
        'total_beneficiaries' => 'integer'
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
            'country_scholarship',
            'scholarship_id',
            'country_id',
            'scholarship_id',
            'country_id'
        );
    }

    // Many-to-many relationship with universities
    public function universities()
    {
        return $this->belongsToMany(
            University::class,
            'university_scholarship',
            'scholarship_id',
            'university_id',
            'scholarship_id',
            'university_id'
        );
    }

    // Scope for visible scholarships (for students/applicants)
    public function scopeVisible($query)
    {
        return $query->where('is_active', true)
                    ->where('is_hided', false)
                    ->where('closing_date', '>', now());
    }

    // Scope for admin view (shows all scholarships)
    public function scopeForAdmin($query)
    {
        return $query; // No filters for admin
    }

    // Check if scholarship is visible to public
    public function isVisible()
    {
        return $this->is_active && !$this->is_hided && $this->closing_date > now();
    }
}