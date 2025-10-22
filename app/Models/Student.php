<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $primaryKey = 'student_id';

    protected $fillable = [
        'user_id',
        'approved_application_id',
        'ar_name',
        'en_name',
        'nationality',
        'gender',
        'date_of_birth',
        'place_of_birth',
        'phone',
        'passport_number',
        'parent_contact_name',
        'parent_contact_phone',
        'residence_country',
        'language',
        'is_studied_in_saudi',
        'passport_copy_img',
        'personal_image',
        'volunteering_certificate_file',
        'tahsili_file',
        'qudorat_file',
        'tahseeli_percentage',
        'qudorat_percentage',
        'is_archived',
        'graduated_at'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_studied_in_saudi' => 'boolean',
        'is_archived' => 'boolean',
        'graduated_at' => 'datetime',
        'tahseeli_percentage' => 'decimal:2',
        'qudorat_percentage' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function approvedApplication()
    {
        return $this->belongsTo(ApprovedApplicantApplication::class, 'approved_application_id', 'approved_application_id');
    }

    public function qualifications()
    {
        return $this->hasMany(Qualification::class, 'user_id', 'user_id');
    }

    public function statusTrails()
    {
        return $this->hasMany(StudentStatusTrail::class, 'student_id', 'student_id');
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class, 'student_id', 'student_id');
    }

    public function currentStatus()
    {
        return $this->hasOne(StudentStatusTrail::class, 'student_id', 'student_id')
            ->latest('date');
    }

    public function activeSemester()
    {
        return $this->hasOne(Semester::class, 'student_id', 'student_id')
            ->where('status', 'active');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopeGraduated($query)
    {
        return $query->whereNotNull('graduated_at');
    }
}
