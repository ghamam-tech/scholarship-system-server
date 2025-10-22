<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantApplicationStatus extends Model
{
    protected $primaryKey = 'applicationStatus_id';
    protected $fillable = ['user_id', 'status_name', 'date', 'comment'];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
