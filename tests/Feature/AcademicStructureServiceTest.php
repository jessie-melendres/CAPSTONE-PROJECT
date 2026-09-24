<?php

namespace Tests\Feature;

use App\Exceptions\BusinessRuleException;
use App\Models\Department;
use App\Services\AcademicStructureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureServiceTest extends TestCase
{
    use RefreshDatabase;
    use AcademicTestHelpers;

    protected AcademicStructureService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AcademicStructureService();
    }

    public function test_an_empty_department_can_be_deleted(): void
    {
        $department = Department::create(['dept_id' => 'CCS', 'dept_name' => 'College of Computing Studies']);

        $this->service->deleteDepartment($department);

        $this->assertDatabaseMissing('departments', ['dept_id' => 'CCS']);
    }

    public function test_a_department_with_a_program_cannot_be_deleted(): void
    {
        $program = $this->makeDepartmentAndProgram();

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('programs or faculty');

        $this->service->deleteDepartment($program->department);
    }

    public function test_a_department_with_faculty_cannot_be_deleted(): void
    {
        $department = Department::create(['dept_id' => 'CCS', 'dept_name' => 'College of Computing Studies']);
        $this->makeFaculty('CCS');

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('programs or faculty');

        $this->service->deleteDepartment($department);
    }

    public function test_an_empty_program_can_be_deleted(): void
    {
        $program = $this->makeDepartmentAndProgram();

        $this->service->deleteProgram($program);

        $this->assertDatabaseMissing('programs', ['program_id' => $program->program_id]);
    }

    public function test_a_program_with_students_cannot_be_deleted(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $this->makeStudent($program);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('students enrolled');

        $this->service->deleteProgram($program);
    }

    public function test_an_unused_subject_can_be_deleted(): void
    {
        $subject = $this->makeSubject('IT101');

        $this->service->deleteSubject($subject);

        $this->assertDatabaseMissing('subjects', ['subject_id' => 'IT101']);
    }

    public function test_a_subject_with_a_schedule_cannot_be_deleted(): void
    {
        $this->makeDepartmentAndProgram();
        $subject = $this->makeSubject('IT101');
        $faculty = $this->makeFaculty('CCS');
        $this->makeSchedule('IT101', $faculty->faculty_id);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('class schedules');

        $this->service->deleteSubject($subject);
    }

    public function test_a_subject_used_as_a_prerequisite_cannot_be_deleted(): void
    {
        $subject = $this->makeSubject('IT101');
        $this->makeSubject('IT102', 3, 'IT101');

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('required as a prerequisite');

        $this->service->deleteSubject($subject);
    }
}
