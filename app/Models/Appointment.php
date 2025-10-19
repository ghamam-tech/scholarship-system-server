<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    protected $primaryKey = 'appointment_id';

    protected $fillable = [
        'starts_at_utc',
        'ends_at_utc',
        'owner_timezone',
        'duration_min',
        'meeting_url',
        'status',
        'user_id',
        'booked_at',
        'canceled_at',
    ];

    protected $casts = [
        'starts_at_utc' => 'datetime',
        'ends_at_utc' => 'datetime',
        'booked_at' => 'datetime',
        'canceled_at' => 'datetime',
        'duration_min' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeBooked($query)
    {
        return $query->where('status', 'booked');
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at_utc', '>', now());
    }

    // Helper methods
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    public function isBooked()
    {
        return $this->status === 'booked';
    }

    public function isCanceled()
    {
        return $this->status === 'canceled';
    }

    public function canBeBooked()
    {
        return $this->isAvailable() && $this->starts_at_utc > now();
    }

    public function canBeCanceled()
    {
        return $this->isBooked() && $this->starts_at_utc > now();
    }
}
