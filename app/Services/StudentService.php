<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Student;
use App\Models\User;

class StudentService
{
    /**
     * A student with any enrollment or ledger history is never hard-deleted:
     * enrollments carry academic records (grades, prerequisites satisfied)
     * and ledger entries are an append-only financial audit trail. Use
     * Student::status (Inactive/Graduated) to retire the record instead.
     *
     * @throws BusinessRuleException
     */
    public function delete(Student $student): void
    {
        if ($student->enrollments()->exists() || $student->ledgerEntries()->exists()) {
            throw new BusinessRuleException("Cannot remove {$student->fullName()}: they have enrollment or ledger history. Set their status to Inactive or Graduated instead of deleting the record.");
        }

        $userId = $student->user_id;
        $student->delete();
        User::where('user_id', $userId)->delete();
    }
}
