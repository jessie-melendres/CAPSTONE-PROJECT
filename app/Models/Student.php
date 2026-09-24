<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    public const STATUS_ACTIVE = 'Active';
    public const STATUS_INACTIVE = 'Inactive';
    public const STATUS_GRADUATED = 'Graduated';

    protected $table = 'students';

    protected $primaryKey = 'student_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'student_id',
        'first_name',
        'last_name',
        'email_address',
        'program_id',
        'user_id',
        'year_level',
        'status',
    ];

    protected $casts = [
        'year_level' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'student_id', 'student_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'student_id', 'student_id');
    }

    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function ledgerBalance(): float
    {
        return (float) $this->ledgerEntries()
            ->selectRaw("COALESCE(SUM(CASE WHEN entry_type = 'CHARGE' THEN amount ELSE -amount END), 0) as balance")
            ->value('balance');
    }

    public function enrollmentsForTerm(string $semester, string $schoolYear)
    {
        return $this->enrollments()
            ->where('semester', $semester)
            ->where('school_year', $schoolYear)
            ->where('status', 'Enrolled');
    }

    public function unitsForTerm(string $semester, string $schoolYear): int
    {
        return (int) $this->enrollmentsForTerm($semester, $schoolYear)
            ->with('schedule.subject')
            ->get()
            ->sum(fn (Enrollment $enrollment) => $enrollment->schedule?->subject?->units ?? 0);
    }

    public function hasPassedSubject(string $subjectId): bool
    {
        return $this->enrollments()
            ->whereHas('schedule', fn ($query) => $query->where('subject_id', $subjectId))
            ->whereHas('grade', fn ($query) => $query->where('remarks', Grade::REMARK_PASSED))
            ->exists();
    }
}
