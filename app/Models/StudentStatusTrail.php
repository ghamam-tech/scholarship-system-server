<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\StudentStatus;

class StudentStatusTrail extends Model
{
    use HasFactory;

    protected $primaryKey = 'status_trail_id';

    protected $fillable = [
        'student_id',
        'status_name',
        'date',
        'comment',
        'changed_by'
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_name', $status);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');
    }

    // Helper methods
    public function isWarning()
    {
        return in_array($this->status_name, [
            StudentStatus::FIRST_WARNING->value,
            StudentStatus::SECOND_WARNING->value
        ]);
    }

    public function isActive()
    {
        return $this->status_name === StudentStatus::ACTIVE->value;
    }

    public function isGraduated()
    {
        return $this->status_name === StudentStatus::GRADUATE_STUDENT->value;
    }
}

