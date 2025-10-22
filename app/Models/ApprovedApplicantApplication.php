<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovedApplicantApplication extends Model
{
    protected $primaryKey = 'approved_application_id';

    protected $fillable = [
        'benefits',
        'has_accepted_scholarship',
        'scholarship_id',
        'application_id',
        'user_id',
    ];

    protected $casts = [
        'benefits' => 'array',
        'has_accepted_scholarship' => 'boolean',
    ];

    // Relationships
    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class, 'scholarship_id', 'scholarship_id');
    }

    public function application()
    {
        return $this->belongsTo(ApplicantApplication::class, 'application_id', 'application_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'approved_application_id', 'approved_application_id');
    }
}
