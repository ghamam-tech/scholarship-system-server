<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    use HasFactory;

    protected $primaryKey = 'opportunity_id';

    protected $fillable = [
        'title',
        'discription',
        'date',
        'location',
        'country',
        'category',
        'image_file',
        'qr_url',
        'opportunity_coordinatior_name',
        'opportunity_coordinatior_phone',
        'opportunity_coordinatior_email',
        'opportunity_status',
        'start_date',
        'end_date',
        'volunteer_role',
        'volunteering_hours'
    ];

    protected $casts = [
        'date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the full URL for the opportunity image
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_file) {
            return asset('storage/' . $this->image_file);
        }
        return null;
    }

    /**
     * QR attendance is always enabled
     */
    public function getEnableQrAttendanceAttribute($value)
    {
        return true;
    }

    /**
     * Certificate generation is always enabled
     */
    public function getGenerateCertificatesAttribute($value)
    {
        return true;
    }

    /**
     * Get the opportunity applications for this opportunity.
     */
    public function opportunityApplications()
    {
        return $this->hasMany(ApplicationOpportunity::class, 'opportunity_id', 'opportunity_id');
    }

    /**
     * Get the students who applied to this opportunity.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'application_opportunities', 'opportunity_id', 'student_id')
            ->withPivot(['application_status', 'certificate_token', 'comment', 'attendece_mark'])
            ->withTimestamps();
    }
}
