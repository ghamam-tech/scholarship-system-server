<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Applicant extends Model
{
    use HasFactory;

    protected $primaryKey = 'applicant_id';
    protected $fillable = [
        'user_id',
        'ar_name',
        'en_name',
        'nationality',
        'gender',
        'place_of_birth',
        'phone',
        'passport_number',
        'date_of_birth',
        'parent_contact_name',
        'parent_contact_phone',
        'residence_country',
        'passport_copy_img',
        'personal_image', // ✅ ADD THIS
        'volunteering_certificate_file',
        'language',
        'is_studied_in_saudi',
        'tahsili_file',
        'qudorat_file',
        'tahseeli_percentage',
        'qudorat_percentage',
        'is_completed',
        'is_archived',
        'migrated_to_student_at',
        'reactivated_from_student_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(ApplicantApplication::class, 'applicant_id', 'applicant_id');
    }

    public function qualifications()
    {
        return $this->hasMany(Qualification::class, 'applicant_id', 'applicant_id');
    }

    protected $casts = [
        'is_studied_in_saudi' => 'boolean',
        'is_completed' => 'boolean',
        'is_archived' => 'boolean',
        'migrated_to_student_at' => 'datetime',
        'reactivated_from_student_at' => 'datetime',
        'tahseeli_percentage' => 'decimal:2',
        'qudorat_percentage' => 'decimal:2',
    ];
}
