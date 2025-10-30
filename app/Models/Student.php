<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $primaryKey = 'student_id';

    protected $fillable = [
        'user_id',
        'applicant_id',
        'approved_application_id',
        'specialization',
        'offer_letter',
        'country_id',
        'university_id',
        'language_of_study',
        'yearly_tuition_fees',
        'study_period',
        'total_semesters_number',
        'current_semester_number',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    public function approvedApplication()
    {
        return $this->belongsTo(ApprovedApplicantApplication::class, 'approved_application_id', 'approved_application_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }

    public function university()
    {
        return $this->belongsTo(University::class, 'university_id', 'university_id');
    }

    public function requests()
    {
        return $this->hasMany(Request::class, 'student_id', 'student_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'student_id', 'student_id');
    }
}
