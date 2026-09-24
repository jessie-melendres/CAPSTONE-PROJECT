<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Faculty;
use App\Models\Schedule;
use App\Models\User;

class FacultyService
{
    /**
     * @throws BusinessRuleException
     */
    public function delete(Faculty $faculty): void
    {
        if ($faculty->schedules()->exists()) {
            throw new BusinessRuleException("Cannot remove {$faculty->fullName()}: reassign or remove their classes first.");
        }

        $userId = $faculty->user_id;
        $faculty->delete();
        User::where('user_id', $userId)->delete();
    }

    /**
     * Change a faculty member's status. Moving away from Teaching requires
     * every class currently credited to them to be reassigned to a Teaching
     * replacement in the same action — a faculty member never ends up
     * On leave/Inactive while still holding an active class assignment.
     *
     * @throws BusinessRuleException
     */
    public function updateStatus(Faculty $faculty, string $newStatus, ?string $replacementFacultyId = null): Faculty
    {
        if ($newStatus !== Faculty::STATUS_TEACHING) {
            $this->reassignSchedules($faculty, $replacementFacultyId);
        }

        $faculty->update(['status' => $newStatus]);

        return $faculty;
    }

    /**
     * @throws BusinessRuleException
     */
    protected function reassignSchedules(Faculty $faculty, ?string $replacementFacultyId): void
    {
        $schedules = $faculty->schedules()->get();

        if ($schedules->isEmpty()) {
            return;
        }

        if (! $replacementFacultyId) {
            throw new BusinessRuleException("{$faculty->fullName()} has {$schedules->count()} class(es) assigned; choose a replacement faculty member to reassign them to before changing their status.");
        }

        $replacement = Faculty::find($replacementFacultyId);

        if (! $replacement || $replacement->faculty_id === $faculty->faculty_id) {
            throw new BusinessRuleException('Choose a different, existing faculty member to reassign these classes to.');
        }

        if ($replacement->status !== Faculty::STATUS_TEACHING) {
            throw new BusinessRuleException("Cannot reassign classes to {$replacement->fullName()}: status is \"{$replacement->status}\", not Teaching.");
        }

        foreach ($schedules as $schedule) {
            if (Schedule::hasConflict($schedule->day, $schedule->start_time, $schedule->end_time, $replacement->faculty_id, $schedule->room_assignment, $schedule->schedule_id)) {
                throw new BusinessRuleException("Cannot reassign {$schedule->label()} to {$replacement->fullName()}: it conflicts with one of their existing classes.");
            }
        }

        foreach ($schedules as $schedule) {
            $schedule->update(['faculty_id' => $replacement->faculty_id]);
        }
    }
}
