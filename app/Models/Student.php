<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Student extends Model
{
    use HasFactory;

    protected $primaryKey = 'student_id';

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
        'volunteering_certificate_file',
        'language',
        'is_studied_in_saudi',
        'tahsili_file',
        'qudorat_file',
        'tahseeli_percentage',
        'qudorat_percentage'
    ];

    /**
     * Get the user that owns the student.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the program applications for this student.
     */
    public function programApplications()
    {
        return $this->hasMany(ProgramApplication::class, 'student_id', 'student_id');
    }

    /**
     * Get the programs that this student has applied to.
     */
    public function programs()
    {
        return $this->belongsToMany(Program::class, 'program_applications', 'student_id', 'program_id')
            ->withPivot(['application_status', 'attendece_mark', 'certificate_token', 'comment'])
            ->withTimestamps();
    }
}
