<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Faculty;
use App\Models\Schedule;

class ScheduleService
{
    /**
     * Create a class schedule, rejecting it if the faculty member is not
     * currently teaching, or if the faculty member or the room is already
     * booked for an overlapping time window on the same day.
     *
     * @throws BusinessRuleException
     */
    public function create(array $attributes): Schedule
    {
        $this->assertFacultyIsTeaching($attributes['faculty_id']);
        $this->assertNoConflict($attributes['day'], $attributes['start_time'], $attributes['end_time'], $attributes['faculty_id'], $attributes['room_assignment']);

        return Schedule::create($attributes);
    }

    /**
     * @throws BusinessRuleException
     */
    public function update(Schedule $schedule, array $attributes): Schedule
    {
        $this->assertFacultyIsTeaching($attributes['faculty_id']);
        $this->assertNoConflict(
            $attributes['day'],
            $attributes['start_time'],
            $attributes['end_time'],
            $attributes['faculty_id'],
            $attributes['room_assignment'],
            $schedule->schedule_id
        );

        $schedule->update($attributes);

        return $schedule;
    }

    /**
     * @throws BusinessRuleException
     */
    public function delete(Schedule $schedule): void
    {
        if ($schedule->enrollments()->exists()) {
            throw new BusinessRuleException("Cannot remove {$schedule->label()}: students are already enrolled in it.");
        }

        $schedule->delete();
    }

    /**
     * @throws BusinessRuleException
     */
    protected function assertFacultyIsTeaching(string $facultyId): void
    {
        $faculty = Faculty::find($facultyId);

        if (! $faculty || $faculty->status !== Faculty::STATUS_TEACHING) {
            $status = $faculty->status ?? 'unknown';
            throw new BusinessRuleException("Cannot assign a class to this faculty member: status is \"{$status}\", not Teaching.");
        }
    }

    /**
     * @throws BusinessRuleException
     */
    protected function assertNoConflict(string $day, string $startTime, string $endTime, string $facultyId, string $roomAssignment, ?int $excludingScheduleId = null): void
    {
        $conflict = Schedule::conflictsQuery($day, $startTime, $endTime, $facultyId, $roomAssignment, $excludingScheduleId)
            ->with(['faculty', 'subject'])
            ->first();

        if (! $conflict) {
            return;
        }

        $reason = $conflict->faculty_id === $facultyId ? 'the instructor is already teaching another class' : 'the room is already booked';
        throw new BusinessRuleException("Scheduling conflict with {$conflict->subject->subject_name} ({$conflict->label()}) — {$reason} at that time.");
    }
}
