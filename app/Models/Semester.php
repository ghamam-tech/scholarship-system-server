<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $primaryKey = 'semester_id';

    protected $fillable = [
        'student_id',
        'semester_no',
        'courses',
        'credits',
        'start_date',
        'end_date',
        'cgpa',
        'status',
        'transcript',
        'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'cgpa' => 'decimal:2',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeBySemesterNo($query, $semesterNo)
    {
        return $query->where('semester_no', $semesterNo);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('semester_no', 'asc');
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function getDurationInDays()
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    public function getCgpaFormatted()
    {
        return $this->cgpa ? number_format($this->cgpa, 2) : 'N/A';
    }
}

