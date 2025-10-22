<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $primaryKey = 'program_id';

    protected $fillable = [
        'title',
        'discription',
        'date',
        'location',
        'country',
        'category',
        'image_file',
        'qr_url',
        'program_coordinatior_name',
        'program_coordinatior_phone',
        'program_coordinatior_email',
        'program_status',
        'start_date',
        'end_date',
        'enable_qr_attendance',
        'generate_certificates'
    ];

    protected $casts = [
        'date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'enable_qr_attendance' => 'boolean',
        'generate_certificates' => 'boolean',
    ];

    /**
     * Get the full URL for the program image
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_file) {
            return asset('storage/' . $this->image_file);
        }
        return null;
    }

    /**
     * Ensure enable_qr_attendance is always returned as boolean
     */
    public function getEnableQrAttendanceAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Ensure generate_certificates is always returned as boolean
     */
    public function getGenerateCertificatesAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Get the program applications for this program.
     */
    public function programApplications()
    {
        return $this->hasMany(ProgramApplication::class, 'program_id', 'program_id');
    }

    /**
     * Get the students who applied to this program.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'program_applications', 'program_id', 'student_id')
            ->withPivot(['application_status', 'attendece_mark', 'certificate_token', 'comment'])
            ->withTimestamps();
    }
}
