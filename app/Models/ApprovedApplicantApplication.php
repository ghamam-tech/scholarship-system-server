<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovedApplicantApplication extends Model
{
    use HasFactory;

    protected $primaryKey = 'approved_application_id';
    
    protected $fillable = [
        'application_id',
        'scholarship_id',
        'user_id'
    ];

    // Relationships
    public function application()
    {
        return $this->belongsTo(ApplicantApplication::class, 'application_id', 'application_id');
    }

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class, 'scholarship_id', 'scholarship_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'approved_application_id', 'approved_application_id');
    }
}

