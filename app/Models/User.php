<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'email',
        'password',
        'role',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Relationships */

    public function sponsor()
    {
        return $this->hasOne(Sponsor::class, 'user_id', 'user_id');
    }

    public function applicant()
    {
        return $this->hasOne(Applicant::class, 'user_id', 'user_id');
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'user_id', 'user_id');
    }

    public function qualifications()
    {
        return $this->hasMany(Qualification::class, 'user_id', 'user_id');
    }

    public function statuses()
    {
        return $this->hasMany(UserStatus::class, 'user_id', 'user_id')
            ->orderBy('date', 'desc')         // prefer business date first
            ->orderBy('created_at', 'desc');  // then tie-break
    }

    public function currentStatus()
    {
        // Ensures a single row using your business column 'date'
        return $this->hasOne(UserStatus::class, 'user_id', 'user_id')
            ->latestOfMany(['date', 'created_at']);
    }

    public function ticketMessages()
    {
        return $this->hasMany(TicketMessage::class, 'user_id', 'user_id');
    }
}
