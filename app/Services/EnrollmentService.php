<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\Student;

class EnrollmentService
{
    /**
     * Enroll a student into a schedule for a given term, enforcing:
     *  - the subject's prerequisite (if any) has already been passed;
     *  - the student is not already enrolled in that schedule this term;
     *  - the student's own schedule does not clash (same day/overlapping time);
     *  - the student's total units for the term stay within the configured cap.
     *
     * @throws BusinessRuleException
     */
    public function enroll(Student $student, Schedule $schedule, string $semester, string $schoolYear): Enrollment
    {
        if ($student->status !== Student::STATUS_ACTIVE) {
            throw new BusinessRuleException("Cannot enroll: student status is \"{$student->status}\", not Active.");
        }

        $subject = $schedule->subject;

        if ($subject->prerequisite_subject_id && ! $student->hasPassedSubject($subject->prerequisite_subject_id)) {
            $prerequisiteName = $subject->prerequisite?->subject_name ?? $subject->prerequisite_subject_id;
            throw new BusinessRuleException("Cannot enroll: prerequisite \"{$prerequisiteName}\" has not been passed.");
        }

        $alreadyEnrolled = $student->enrollmentsForTerm($semester, $schoolYear)
            ->where('schedule_id', $schedule->schedule_id)
            ->exists();

        if ($alreadyEnrolled) {
            throw new BusinessRuleException('Student is already enrolled in this class for the selected term.');
        }

        $this->assertNoStudentScheduleConflict($student, $schedule, $semester, $schoolYear);

        $projectedUnits = $student->unitsForTerm($semester, $schoolYear) + $subject->units;
        $maxUnits = (int) config('academic.max_units_per_term');

        if ($projectedUnits > $maxUnits) {
            throw new BusinessRuleException("Enrolling in {$subject->subject_name} would exceed the {$maxUnits}-unit limit for this term (currently at {$projectedUnits} units).");
        }

        return $student->enrollments()->create([
            'semester' => $semester,
            'school_year' => $schoolYear,
            'schedule_id' => $schedule->schedule_id,
            'status' => Enrollment::STATUS_ENROLLED,
        ]);
    }

    /**
     * @throws BusinessRuleException
     */
    protected function assertNoStudentScheduleConflict(Student $student, Schedule $schedule, string $semester, string $schoolYear): void
    {
        $conflict = $student->enrollmentsForTerm($semester, $schoolYear)
            ->with('schedule')
            ->get()
            ->first(fn (Enrollment $enrollment) => $enrollment->schedule
                && $enrollment->schedule->day === $schedule->day
                && $enrollment->schedule->start_time < $schedule->end_time
                && $enrollment->schedule->end_time > $schedule->start_time);

        if ($conflict) {
            throw new BusinessRuleException("Schedule conflict: overlaps with {$conflict->schedule->subject->subject_name} ({$conflict->schedule->label()}).");
        }
    }

    public function drop(Enrollment $enrollment): void
    {
        $enrollment->update(['status' => Enrollment::STATUS_DROPPED]);
    }
}
