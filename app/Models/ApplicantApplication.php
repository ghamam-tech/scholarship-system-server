<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ApplicationStatus;

class ApplicantApplication extends Model
{
    protected $primaryKey = 'application_id';
    protected $fillable = [
        'specialization_1',
        'specialization_2',
        'specialization_3',
        'university_name',
        'country_name',
        'tuition_fee',
        'has_active_program',
        'current_semester_number',
        'cgpa',
        'cgpa_out_of',
        'terms_and_condition',
        'offer_letter_file',
        'applicant_id',
        'scholarship_id', // Changed from scholarship_id_1
    ];

    protected $casts = [
        'has_active_program' => 'boolean',
        'terms_and_condition' => 'boolean',
    ];

    // Relationships
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class, 'scholarship_id', 'scholarship_id');
    }

    // ... keep other methods the same
    public function statuses()
    {
        return $this->hasMany(ApplicantApplicationStatus::class, 'application_id', 'application_id')
            ->orderBy('created_at', 'desc');
    }

    public function currentStatus()
    {
        return $this->hasOne(ApplicantApplicationStatus::class, 'application_id', 'application_id')
            ->latest('date');
    }

    // Helper methods
    public function isFinalApproval()
    {
        return $this->currentStatus && $this->currentStatus->status_name === ApplicationStatus::FINAL_APPROVAL->value;
    }

    public function canBeRejected()
    {
        return !$this->isFinalApproval();
    }

    public function canScheduleMeeting()
    {
        return $this->currentStatus &&
            $this->currentStatus->status_name === ApplicationStatus::FIRST_APPROVAL->value;
    }
}
