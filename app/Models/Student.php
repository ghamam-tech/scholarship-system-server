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
}
