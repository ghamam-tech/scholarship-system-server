<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProgramApplication extends Model
{
    use HasFactory;

    protected $primaryKey = 'application_program_id';

    protected $fillable = [
        'student_id',
        'program_id',
        'application_status',
        'certificate_token',
        'comment',
        'excuse_reason',
        'excuse_file'
    ];

    /**
     * Boot the model and add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // No need to generate ID - let auto-increment handle it

        // Automatically generate certificate token when status becomes 'attend'
        static::updating(function ($programApplication) {
            if (
                $programApplication->isDirty('application_status') &&
                $programApplication->application_status === 'attend' &&
                !$programApplication->certificate_token
            ) {

                // Load the program relationship to check conditions
                $program = $programApplication->program;

                if (
                    $program &&
                    $program->program_status === 'completed' &&
                    $program->generate_certificates
                ) {
                    $programApplication->certificate_token = Str::random(32);
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
     * Get the program that the application is for.
     */
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }

    /**
     * Get the formatted application ID with prefix
     */
    public function getFormattedIdAttribute()
    {
        return 'prog_' . str_pad($this->application_program_id, 7, '0', STR_PAD_LEFT);
    }
}
