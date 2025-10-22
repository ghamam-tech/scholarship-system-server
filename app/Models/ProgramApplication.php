<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramApplication extends Model
{
    use HasFactory;

    protected $primaryKey = 'application_program_id';

    protected $fillable = [
        'student_id',
        'program_id',
        'application_status',
        'attendece_mark',
        'certificate_token',
        'comment',
        'excuse_reason',
        'excuse_file'
    ];

    protected $casts = [
        'attendece_mark' => 'decimal:2',
    ];

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
}
