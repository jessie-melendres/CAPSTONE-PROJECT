<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Department;
use App\Models\Program;
use App\Models\Subject;

/**
 * Deletion guards for the reference-data entities admins manage directly
 * (departments, programs, subjects): reject a delete that would otherwise
 * hit a restrictOnDelete foreign key as a raw DB error, or — for a subject
 * still named as a prerequisite — silently null out another subject's
 * prerequisite requirement.
 */
class AcademicStructureService
{
    /**
     * @throws BusinessRuleException
     */
    public function deleteDepartment(Department $department): void
    {
        if ($department->programs()->exists() || $department->faculty()->exists()) {
            throw new BusinessRuleException("Cannot remove {$department->dept_name}: it still has programs or faculty assigned to it.");
        }

        $department->delete();
    }

    /**
     * @throws BusinessRuleException
     */
    public function deleteProgram(Program $program): void
    {
        if ($program->students()->exists()) {
            throw new BusinessRuleException("Cannot remove {$program->program_name}: it still has students enrolled in it.");
        }

        $program->delete();
    }

    /**
     * @throws BusinessRuleException
     */
    public function deleteSubject(Subject $subject): void
    {
        if ($subject->schedules()->exists()) {
            throw new BusinessRuleException("Cannot remove {$subject->subject_name}: it still has class schedules using it.");
        }

        if ($subject->dependents()->exists()) {
            throw new BusinessRuleException("Cannot remove {$subject->subject_name}: it is still required as a prerequisite by another subject.");
        }

        $subject->delete();
    }
}
