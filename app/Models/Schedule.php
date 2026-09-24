<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    protected $table = 'schedules';

    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'room_assignment',
        'day',
        'start_time',
        'end_time',
        'faculty_id',
        'subject_id',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'faculty_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'schedule_id', 'schedule_id');
    }

    public function label(): string
    {
        return "{$this->day} {$this->start_time}-{$this->end_time} · {$this->room_assignment}";
    }

    /** MySQL TIME columns round-trip as H:i:s; display as H:i. */
    protected function startTime(): Attribute
    {
        return Attribute::make(get: fn ($value) => $value ? substr($value, 0, 5) : $value);
    }

    protected function endTime(): Attribute
    {
        return Attribute::make(get: fn ($value) => $value ? substr($value, 0, 5) : $value);
    }

    /**
     * Find conflicting schedules: same day, overlapping time window, and either
     * the same faculty (double-booked instructor) or the same room (double-booked room).
     */
    public static function conflictsQuery(string $day, string $startTime, string $endTime, string $facultyId, string $roomAssignment, ?int $excludingScheduleId = null)
    {
        return static::query()
            ->where('day', $day)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->where(function ($query) use ($facultyId, $roomAssignment) {
                $query->where('faculty_id', $facultyId)
                    ->orWhere('room_assignment', $roomAssignment);
            })
            ->when($excludingScheduleId, fn ($query) => $query->where('schedule_id', '!=', $excludingScheduleId));
    }

    public static function hasConflict(string $day, string $startTime, string $endTime, string $facultyId, string $roomAssignment, ?int $excludingScheduleId = null): bool
    {
        return static::conflictsQuery($day, $startTime, $endTime, $facultyId, $roomAssignment, $excludingScheduleId)->exists();
    }
}
