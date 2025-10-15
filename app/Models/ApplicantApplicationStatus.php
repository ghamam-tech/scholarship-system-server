<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantApplicationStatus extends Model
{
    protected $primaryKey = 'applicationStatus_id';
    protected $fillable = ['application_id','status_name','date','comment'];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(ApplicantApplication::class, 'application_id', 'application_id');
    }
}
