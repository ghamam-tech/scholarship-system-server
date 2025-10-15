<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Sponsor.php
class Sponsor extends Model
{
    protected $primaryKey = 'sponsor_id';
    protected $fillable = ['user_id','name','country','is_active'];
    protected $casts = ['is_active' => 'boolean'];

    // 👇 Tell Laravel which column to use for route model binding
    public function getRouteKeyName()
    {
        return 'sponsor_id';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
