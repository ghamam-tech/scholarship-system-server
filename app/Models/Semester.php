<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $primaryKey = 'semester_id';

    protected $fillable = [
        'credit_hours',
        'total_subjects',
        'status',
        'cgpa',
        'cgpa_out_of',
        'semester_number',
        'starting_date',
        'ending_date',
        'transcript_path',
        'user_id',
    ];

    protected $casts = [
        'credit_hours' => 'decimal:2',
        'cgpa' => 'decimal:2',
        'cgpa_out_of' => 'decimal:2',
        'starting_date' => 'date',
        'ending_date' => 'date',
    ];

    /**
     * The user that owns the semester record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
