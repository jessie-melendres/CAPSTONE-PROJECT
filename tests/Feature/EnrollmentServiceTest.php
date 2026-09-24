<?php

namespace Tests\Feature;

use App\Exceptions\BusinessRuleException;
use App\Models\Grade;
use App\Models\Student;
use App\Services\EnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentServiceTest extends TestCase
{
    use RefreshDatabase;
    use AcademicTestHelpers;

    protected EnrollmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EnrollmentService();
    }

    public function test_a_student_can_enroll_in_an_open_schedule(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $faculty = $this->makeFaculty('CCS');
        $this->makeSubject('IT101');
        $schedule = $this->makeSchedule('IT101', $faculty->faculty_id);

        $enrollment = $this->service->enroll($student, $schedule, '1st Semester', '2026-2027');

        $this->assertDatabaseHas('enrollments', ['enrollment_id' => $enrollment->enrollment_id, 'status' => 'Enrolled']);
    }

    public function test_enrollment_is_rejected_when_the_prerequisite_has_not_been_passed(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $faculty = $this->makeFaculty('CCS');
        $this->makeSubject('IT101');
        $this->makeSubject('IT102', 3, 'IT101');
        $schedule = $this->makeSchedule('IT102', $faculty->faculty_id);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('prerequisite');

        $this->service->enroll($student, $schedule, '1st Semester', '2026-2027');
    }

    public function test_enrollment_succeeds_once_the_prerequisite_is_passed(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $faculty = $this->makeFaculty('CCS');
        $this->makeSubject('IT101');
        $this->makeSubject('IT102', 3, 'IT101');

        // Give the student a passed IT101 enrollment from a prior term.
        $priorSchedule = $this->makeSchedule('IT101', $faculty->faculty_id, 'Tuesday', '08:00', '10:00', 'Room 100');
        $priorEnrollment = $this->service->enroll($student, $priorSchedule, '2nd Semester', '2025-2026');
        Grade::create([
            'enrollment_id' => $priorEnrollment->enrollment_id,
            'final_grade' => 1.5,
            'remarks' => Grade::REMARK_PASSED,
        ]);

        $schedule = $this->makeSchedule('IT102', $faculty->faculty_id, 'Wednesday');
        $enrollment = $this->service->enroll($student, $schedule, '1st Semester', '2026-2027');

        $this->assertDatabaseHas('enrollments', ['enrollment_id' => $enrollment->enrollment_id]);
    }

    public function test_enrollment_is_rejected_once_the_unit_cap_is_exceeded(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $faculty = $this->makeFaculty('CCS');
        config(['academic.max_units_per_term' => 6]);

        $this->makeSubject('IT101', 3);
        $this->makeSubject('IT102', 3);
        $this->makeSubject('IT103', 3);

        $this->service->enroll($student, $this->makeSchedule('IT101', $faculty->faculty_id, 'Monday'), '1st Semester', '2026-2027');
        $this->service->enroll($student, $this->makeSchedule('IT102', $faculty->faculty_id, 'Tuesday'), '1st Semester', '2026-2027');

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('exceed');

        $this->service->enroll($student, $this->makeSchedule('IT103', $faculty->faculty_id, 'Wednesday'), '1st Semester', '2026-2027');
    }

    public function test_enrollment_is_rejected_when_the_students_own_schedule_overlaps(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $faculty = $this->makeFaculty('CCS');
        $this->makeSubject('IT101');
        $this->makeSubject('IT102');

        $this->service->enroll($student, $this->makeSchedule('IT101', $faculty->faculty_id, 'Monday', '08:00', '10:00', 'Room 101'), '1st Semester', '2026-2027');

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('conflict');

        $this->service->enroll($student, $this->makeSchedule('IT102', $faculty->faculty_id, 'Monday', '09:00', '11:00', 'Room 102'), '1st Semester', '2026-2027');
    }

    public function test_an_inactive_student_cannot_be_enrolled(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program, '2026-0001', Student::STATUS_INACTIVE);
        $faculty = $this->makeFaculty('CCS');
        $this->makeSubject('IT101');
        $schedule = $this->makeSchedule('IT101', $faculty->faculty_id);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('not Active');

        $this->service->enroll($student, $schedule, '1st Semester', '2026-2027');
    }

    public function test_a_graduated_student_cannot_be_enrolled(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program, '2026-0001', Student::STATUS_GRADUATED);
        $faculty = $this->makeFaculty('CCS');
        $this->makeSubject('IT101');
        $schedule = $this->makeSchedule('IT101', $faculty->faculty_id);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('not Active');

        $this->service->enroll($student, $schedule, '1st Semester', '2026-2027');
    }

    public function test_a_student_cannot_be_enrolled_twice_in_the_same_class_for_the_same_term(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $faculty = $this->makeFaculty('CCS');
        $this->makeSubject('IT101');
        $schedule = $this->makeSchedule('IT101', $faculty->faculty_id);

        $this->service->enroll($student, $schedule, '1st Semester', '2026-2027');

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('already enrolled');

        $this->service->enroll($student, $schedule, '1st Semester', '2026-2027');
    }
}
