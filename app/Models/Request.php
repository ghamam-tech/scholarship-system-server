<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $primaryKey = 'request_id';

    protected $fillable = [
        'student_id',
        'request_type',
        'amount',
        'body',
        'current_status',
        'document_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function statusTrails()
    {
        return $this->hasMany(RequestStatusTrail::class, 'request_id', 'request_id');
    }
}
