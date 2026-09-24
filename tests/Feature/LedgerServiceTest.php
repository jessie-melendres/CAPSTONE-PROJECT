<?php

namespace Tests\Feature;

use App\Models\LedgerEntry;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LedgerServiceTest extends TestCase
{
    use RefreshDatabase;
    use AcademicTestHelpers;

    public function test_balance_is_charges_minus_payments(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $admin = User::create(['username' => 'admin', 'password' => Hash::make('Admin@2026'), 'role' => User::ROLE_ADMIN]);
        $service = new LedgerService();

        $service->recordEntry($student, LedgerEntry::TYPE_CHARGE, 18500, 'Tuition Fee', $admin);
        $service->recordEntry($student, LedgerEntry::TYPE_PAYMENT, 5000, 'Partial payment', $admin);

        $this->assertSame(13500.0, $service->balance($student->fresh()));
    }

    public function test_balance_is_zero_with_no_entries(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);

        $this->assertSame(0.0, (new LedgerService())->balance($student));
    }
}
