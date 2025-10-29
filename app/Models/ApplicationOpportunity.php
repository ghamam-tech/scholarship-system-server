<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApplicationOpportunity extends Model
{
    use HasFactory;

    protected $primaryKey = 'application_opportunity_id';

    protected $fillable = [
        'student_id',
        'opportunity_id',
        'application_status',
        'certificate_token',
        'comment',
        'excuse_reason',
        'excuse_file',
        'attendece_mark'
    ];

    /**
     * Boot the model and add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // No need to generate ID - let auto-increment handle it

        // Automatically generate certificate token when status becomes 'attend'
        static::updating(function ($applicationOpportunity) {
            if (
                $applicationOpportunity->isDirty('application_status') &&
                $applicationOpportunity->application_status === 'attend' &&
                !$applicationOpportunity->certificate_token
            ) {

                // Load the opportunity relationship to check conditions
                $opportunity = $applicationOpportunity->opportunity;

                if (
                    $opportunity &&
                    $opportunity->opportunity_status === 'completed' &&
                    $opportunity->generate_certificates
                ) {
                    $applicationOpportunity->certificate_token = Str::random(32);
                }
            }
        });
    }

    /**
     * Get the student that owns the application.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the opportunity that the application is for.
     */
    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class, 'opportunity_id', 'opportunity_id');
    }

    /**
     * Get the formatted application ID with prefix
     */
    public function getFormattedIdAttribute()
    {
        return 'opp_' . str_pad($this->application_opportunity_id, 7, '0', STR_PAD_LEFT);
    }
}
