<?php

namespace Tests\Feature;

use App\Exceptions\BusinessRuleException;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\LedgerService;
use App\Services\StudentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentServiceTest extends TestCase
{
    use RefreshDatabase;
    use AcademicTestHelpers;

    protected StudentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StudentService();
    }

    public function test_a_student_with_no_history_can_be_deleted(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $userId = $student->user_id;

        $this->service->delete($student);

        $this->assertDatabaseMissing('students', ['student_id' => $student->student_id]);
        $this->assertDatabaseMissing('users', ['user_id' => $userId]);
    }

    public function test_a_student_with_an_enrollment_cannot_be_deleted(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $faculty = $this->makeFaculty('CCS');
        $this->makeSubject('IT101');
        $schedule = $this->makeSchedule('IT101', $faculty->faculty_id);
        (new EnrollmentService())->enroll($student, $schedule, '1st Semester', '2026-2027');

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('enrollment or ledger history');

        $this->service->delete($student);
    }

    public function test_a_student_with_a_ledger_entry_cannot_be_deleted(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $admin = User::create(['username' => 'admin', 'password' => Hash::make('Admin@2026'), 'role' => User::ROLE_ADMIN]);
        (new LedgerService())->recordEntry($student, 'CHARGE', 1000, 'Misc fee', $admin);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('enrollment or ledger history');

        $this->service->delete($student);
    }
}
