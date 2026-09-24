<?php

namespace Tests\Feature;

use App\Services\EnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyGradeAccessTest extends TestCase
{
    use RefreshDatabase;
    use AcademicTestHelpers;

    public function test_a_faculty_member_cannot_encode_grades_for_a_class_not_credited_to_them(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $owner = $this->makeFaculty('CCS', 'FAC-001');
        $intruder = $this->makeFaculty('CCS', 'FAC-002');
        $this->makeSubject('IT101');
        $schedule = $this->makeSchedule('IT101', $owner->faculty_id);
        $enrollment = (new EnrollmentService())->enroll($student, $schedule, '1st Semester', '2026-2027');

        $this->post('/login', ['access_type' => 'staff', 'staff_email' => $intruder->user->username, 'staff_password' => 'Faculty@2026']);

        $response = $this->get(route('faculty.grades', $schedule));
        $response->assertForbidden();

        $storeResponse = $this->post(route('faculty.grades.store', $enrollment), ['final_grade' => 1.0]);
        $storeResponse->assertForbidden();
    }

    public function test_the_owning_faculty_member_can_encode_grades(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $faculty = $this->makeFaculty('CCS', 'FAC-001');
        $this->makeSubject('IT101');
        $schedule = $this->makeSchedule('IT101', $faculty->faculty_id);
        $enrollment = (new EnrollmentService())->enroll($student, $schedule, '1st Semester', '2026-2027');

        $this->post('/login', ['access_type' => 'staff', 'staff_email' => $faculty->user->username, 'staff_password' => 'Faculty@2026']);

        $this->get(route('faculty.grades', $schedule))->assertOk();

        $response = $this->post(route('faculty.grades.store', $enrollment), [
            'prelim_grade' => 1.5,
            'midterm_grade' => 1.5,
            'final_grade' => 1.5,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('grades', ['enrollment_id' => $enrollment->enrollment_id, 'final_grade' => 1.5, 'remarks' => 'PASSED']);
    }
}
