<?php

namespace App\Models;

use App\Enums\AnnouncementStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Announcement extends Model
{
    use HasFactory;

    protected $primaryKey = 'announcement_id';

    protected $fillable = [
        'title',
        'content',
        'status',
        'publishing_date',
        'disappearing_date',
        'filters',
    ];

    protected function casts(): array
    {
        return [
            'status' => AnnouncementStatus::class,
            'publishing_date' => 'datetime',
            'disappearing_date' => 'datetime',
            'filters' => 'array',
        ];
    }

    /**
     * Scope announcements that are published and currently active.
     */
    public function scopeActive(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query
            ->where('status', AnnouncementStatus::PUBLISHED)
            ->where(function (Builder $query) use ($now) {
                $query
                    ->whereNull('publishing_date')
                    ->orWhere('publishing_date', '<=', $now);
            })
            ->where(function (Builder $query) use ($now) {
                $query
                    ->whereNull('disappearing_date')
                    ->orWhere('disappearing_date', '>', $now);
            })
            ->orderByDesc('publishing_date')
            ->orderByDesc('created_at');
    }
}
