<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Student;
use App\Models\User;

class LedgerService
{
    /**
     * Record a manual charge or payment against a student's ledger. There is
     * no live payment gateway (out of scope per the capstone's Limitations) —
     * entries are always entered by an administrator/clerk after cash payment.
     */
    public function recordEntry(Student $student, string $entryType, float $amount, string $description, User $recordedBy, ?string $entryDate = null): LedgerEntry
    {
        return $student->ledgerEntries()->create([
            'description' => $description,
            'entry_type' => $entryType,
            'amount' => $amount,
            'recorded_by_user_id' => $recordedBy->user_id,
            'entry_date' => $entryDate ?? now()->toDateString(),
        ]);
    }

    public function balance(Student $student): float
    {
        return $student->ledgerBalance();
    }
}
