<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    protected $primaryKey = 'university_id';
    protected $fillable = ['country_id', 'university_name', 'city', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }

    // Many-to-many relationship with scholarships - UPDATED TABLE NAME
    public function scholarships()
    {
        return $this->belongsToMany(
            Scholarship::class,
            'university_scholarship', // Changed to match your table name
            'university_id',
            'scholarship_id',
            'university_id',
            'scholarship_id'
        );
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'university_id', 'university_id');
    }
}
