<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantApplication extends Model
{
    protected $primaryKey = 'application_id';
    protected $fillable = [
        'specialization_1',
        'specialization_2',
        'specialization_3',
        'tuition_fee',
        'has_active_program',
        'current_semester_number',
        'cgpa',
        'cgpa_out_of',
        'terms_and_condition',
        'offer_letter_file',
        'applicant_id',
        'scholarship_id_1',
        'scholarship_id_2',
        'scholarship_id_3'
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    public function specialization1()
    {
        return $this->belongsTo(Specialization::class, 'specialization_1', 'specialization_id');
    }
    public function specialization2()
    {
        return $this->belongsTo(Specialization::class, 'specialization_2', 'specialization_id');
    }
    public function specialization3()
    {
        return $this->belongsTo(Specialization::class, 'specialization_3', 'specialization_id');
    }

    public function scholarship1()
    {
        return $this->belongsTo(Scholarship::class, 'scholarship_id_1', 'scholarship_id');
    }
    public function scholarship2()
    {
        return $this->belongsTo(Scholarship::class, 'scholarship_id_2', 'scholarship_id');
    }
    public function scholarship3()
    {
        return $this->belongsTo(Scholarship::class, 'scholarship_id_3', 'scholarship_id');
    }

    public function qualifications()
    {
        return $this->hasMany(Qualification::class, 'application_id', 'application_id');
    }

    public function statuses()
    {
        return $this->hasMany(ApplicantApplicationStatus::class, 'application_id', 'application_id')->latest('date');
    }
}
