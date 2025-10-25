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
        'passport_expiry',
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
        'is_archive',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function applications()
    {
        return $this->hasMany(ApplicantApplication::class, 'applicant_id', 'applicant_id');
    }

    public function qualifications()
    {
        // qualifications.user_id === applicants.user_id
        return $this->hasMany(Qualification::class, 'user_id', 'user_id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'applicant_id', 'applicant_id');
    }
}
