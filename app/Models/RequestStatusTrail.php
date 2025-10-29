<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStatusTrail extends Model
{
    use HasFactory;

    protected $table = 'request_status_trail';
    protected $primaryKey = 'request_status_id';

    protected $fillable = [
        'request_id',
        'status',
        'comment',
        'date',
        'document_path',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id', 'request_id');
    }
}
