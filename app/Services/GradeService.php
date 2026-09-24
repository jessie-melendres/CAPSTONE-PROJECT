<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradeAuditLog;
use App\Models\User;

class GradeService
{
    protected const TRACKED_FIELDS = ['prelim_grade', 'midterm_grade', 'final_grade'];

    /**
     * Encode/update term grades for an enrollment, recomputing the remark and
     * writing an audit-log row per changed field. Locked grades reject writes.
     *
     * @throws BusinessRuleException
     */
    public function record(Enrollment $enrollment, array $termGrades, User $changedBy): Grade
    {
        $grade = $enrollment->grade ?? new Grade(['enrollment_id' => $enrollment->enrollment_id]);

        if ($grade->exists && $grade->is_locked) {
            throw new BusinessRuleException('This grade record is locked and can no longer be modified.');
        }

        foreach (self::TRACKED_FIELDS as $field) {
            if (! array_key_exists($field, $termGrades)) {
                continue;
            }

            $oldValue = $grade->{$field};
            $newValue = $termGrades[$field];

            if ($oldValue == $newValue) {
                continue;
            }

            $grade->{$field} = $newValue;

            if ($grade->exists) {
                $this->logChange($grade, $field, $oldValue, $newValue, $changedBy);
            }
        }

        $grade->remarks = Grade::computeRemark($grade->prelim_grade, $grade->midterm_grade, $grade->final_grade);
        $grade->save();

        if (! $grade->wasRecentlyCreated) {
            return $grade;
        }

        foreach (self::TRACKED_FIELDS as $field) {
            if ($grade->{$field} !== null) {
                $this->logChange($grade, $field, null, $grade->{$field}, $changedBy);
            }
        }

        return $grade;
    }

    protected function logChange(Grade $grade, string $field, mixed $oldValue, mixed $newValue, User $changedBy): void
    {
        GradeAuditLog::create([
            'grade_id' => $grade->grade_id,
            'changed_by_user_id' => $changedBy->user_id,
            'field_changed' => $field,
            'old_value' => $oldValue === null ? null : (string) $oldValue,
            'new_value' => $newValue === null ? null : (string) $newValue,
            'changed_at' => now(),
        ]);
    }

    /**
     * @throws BusinessRuleException
     */
    public function lock(Grade $grade): Grade
    {
        if ($grade->final_grade === null) {
            throw new BusinessRuleException('A final grade must be recorded before the record can be locked.');
        }

        $grade->update(['is_locked' => true, 'locked_at' => now()]);

        return $grade;
    }

    /** Administrator override to reopen a locked grade for correction. */
    public function unlock(Grade $grade): Grade
    {
        $grade->update(['is_locked' => false, 'locked_at' => null]);

        return $grade;
    }
}
