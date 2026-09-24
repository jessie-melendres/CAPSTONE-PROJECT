<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    protected $table = 'grades';

    protected $primaryKey = 'grade_id';

    public const REMARK_PASSED = 'PASSED';
    public const REMARK_FAILED = 'FAILED';
    public const REMARK_INCOMPLETE = 'INC';
    public const REMARK_NO_GRADE = 'NG';

    /** Philippine 1.00 (highest) - 5.00 (lowest) scale; 3.00 or below passes. */
    public const PASSING_THRESHOLD = 3.00;

    protected $fillable = [
        'enrollment_id',
        'prelim_grade',
        'midterm_grade',
        'final_grade',
        'remarks',
        'is_locked',
        'locked_at',
    ];

    protected $casts = [
        'prelim_grade' => 'float',
        'midterm_grade' => 'float',
        'final_grade' => 'float',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id', 'enrollment_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(GradeAuditLog::class, 'grade_id', 'grade_id')->latest('changed_at');
    }

    /**
     * Derive the semestral remark from the three term grades, matching the
     * institute's 1.00 (highest) - 5.00 (lowest) scale with INC/NG statuses.
     *
     * PASSED/FAILED is decided by the semestral average of all three terms,
     * not the final term alone: see docs/adr/0001-remark-from-semestral-average.md.
     */
    public static function computeRemark(?float $prelim, ?float $midterm, ?float $final): string
    {
        if ($prelim === null && $midterm === null && $final === null) {
            return self::REMARK_NO_GRADE;
        }

        if ($prelim === null || $midterm === null || $final === null) {
            return self::REMARK_INCOMPLETE;
        }

        $average = round(($prelim + $midterm + $final) / 3, 2);

        return $average <= self::PASSING_THRESHOLD ? self::REMARK_PASSED : self::REMARK_FAILED;
    }

    public function semestralAverage(): ?float
    {
        $terms = array_filter([$this->prelim_grade, $this->midterm_grade, $this->final_grade], fn ($value) => $value !== null);

        return count($terms) === 3 ? round(array_sum($terms) / 3, 2) : null;
    }
}
