<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    protected $table = 'enrollments';

    protected $primaryKey = 'enrollment_id';

    public const STATUS_ENROLLED = 'Enrolled';
    public const STATUS_DROPPED = 'Dropped';

    protected $fillable = [
        'semester',
        'school_year',
        'student_id',
        'schedule_id',
        'status',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'schedule_id');
    }

    public function grade(): HasOne
    {
        return $this->hasOne(Grade::class, 'enrollment_id', 'enrollment_id');
    }
}
