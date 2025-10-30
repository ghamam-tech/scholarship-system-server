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

        // Automatically manage certificate token based on status and opportunity completion
        static::updating(function ($applicationOpportunity) {
            // Load the opportunity relationship to check conditions
            $opportunity = $applicationOpportunity->opportunity;

            // Check if we should have a certificate token
            $shouldHaveToken = $applicationOpportunity->application_status === 'attend' 
                && $opportunity 
                && $opportunity->opportunity_status === 'completed' 
                && $opportunity->generate_certificates;

            if ($shouldHaveToken) {
                // Generate token if we should have one and don't
                if (!$applicationOpportunity->certificate_token) {
                    $applicationOpportunity->certificate_token = Str::random(32);
                }
            } else {
                // Remove token if we shouldn't have one but do
                if ($applicationOpportunity->certificate_token) {
                    $applicationOpportunity->certificate_token = null;
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
