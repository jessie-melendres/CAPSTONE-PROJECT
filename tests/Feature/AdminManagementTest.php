<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\GradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;
    use AcademicTestHelpers;

    protected function loginAsAdmin(): void
    {
        User::create(['username' => 'admin', 'password' => Hash::make('Admin@2026'), 'role' => User::ROLE_ADMIN]);
        $this->post('/login', ['access_type' => 'staff', 'staff_email' => 'admin', 'staff_password' => 'Admin@2026'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_create_a_department(): void
    {
        $this->loginAsAdmin();

        $response = $this->post(route('admin.departments.store'), ['dept_id' => 'COE', 'dept_name' => 'College of Engineering']);

        $response->assertRedirect();
        $this->assertDatabaseHas('departments', ['dept_id' => 'COE']);
    }

    public function test_admin_can_create_a_student_account_with_a_hashed_password(): void
    {
        $this->loginAsAdmin();
        $program = $this->makeDepartmentAndProgram();

        $response = $this->post(route('admin.students.store'), [
            'student_id' => '2026-0099',
            'first_name' => 'New',
            'last_name' => 'Student',
            'program_id' => $program->program_id,
            'year_level' => 1,
            'initial_password' => 'Secret123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', ['student_id' => '2026-0099']);

        $stored = User::where('username', '2026-0099')->first();
        $this->assertNotNull($stored);
        $this->assertTrue(Hash::check('Secret123', $stored->password));
    }

    public function test_creating_a_schedule_that_conflicts_shows_a_validation_error_instead_of_a_500(): void
    {
        $this->loginAsAdmin();
        $this->makeDepartmentAndProgram();
        $this->makeSubject('IT101');
        $this->makeSubject('IT102');
        $faculty = $this->makeFaculty('CCS');
        $this->makeSchedule('IT101', $faculty->faculty_id, 'Monday', '08:00', '10:00', 'Room 101');

        $response = $this->post(route('admin.schedules.store'), [
            'subject_id' => 'IT102',
            'faculty_id' => $faculty->faculty_id,
            'room_assignment' => 'Room 101',
            'day' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);

        $response->assertSessionHasErrors('schedule');
        $this->assertDatabaseMissing('schedules', ['subject_id' => 'IT102']);
    }

    public function test_admin_can_unlock_a_faculty_locked_grade(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $faculty = $this->makeFaculty('CCS');
        $this->makeSubject('IT101');
        $schedule = $this->makeSchedule('IT101', $faculty->faculty_id);
        $enrollment = (new EnrollmentService())->enroll($student, $schedule, '1st Semester', '2026-2027');

        $grade = (new GradeService())->record($enrollment, ['prelim_grade' => 1.5, 'midterm_grade' => 1.5, 'final_grade' => 1.5], $faculty->user);
        (new GradeService())->lock($grade);
        $this->assertTrue($grade->fresh()->is_locked);

        $this->loginAsAdmin();

        $response = $this->post(route('admin.grades.unlock', $enrollment));

        $response->assertRedirect();
        $this->assertFalse($grade->fresh()->is_locked);
    }

    public function test_admin_reassigns_a_faculty_members_classes_when_taking_them_off_teaching_status(): void
    {
        $this->loginAsAdmin();
        $this->makeDepartmentAndProgram();
        $this->makeSubject('IT101');
        $faculty = $this->makeFaculty('CCS', 'FAC-001');
        $replacement = $this->makeFaculty('CCS', 'FAC-002');
        $schedule = $this->makeSchedule('IT101', $faculty->faculty_id);

        $response = $this->put(route('admin.faculty.update', $faculty), [
            'first_name' => $faculty->first_name,
            'last_name' => $faculty->last_name,
            'dept_id' => $faculty->dept_id,
            'status' => 'On leave',
            'replacement_faculty_id' => $replacement->faculty_id,
        ]);

        $response->assertRedirect();
        $this->assertSame('On leave', $faculty->fresh()->status);
        $this->assertSame($replacement->faculty_id, $schedule->fresh()->faculty_id);
    }

    public function test_deleting_a_faculty_member_with_existing_classes_shows_a_validation_error_instead_of_a_500(): void
    {
        $this->loginAsAdmin();
        $this->makeDepartmentAndProgram();
        $this->makeSubject('IT101');
        $faculty = $this->makeFaculty('CCS', 'FAC-001');
        $this->makeSchedule('IT101', $faculty->faculty_id);

        $response = $this->delete(route('admin.faculty.destroy', $faculty));

        $response->assertSessionHasErrors('faculty');
        $this->assertDatabaseHas('faculty', ['faculty_id' => 'FAC-001']);
    }

    public function test_deleting_a_student_with_an_enrollment_shows_a_validation_error_instead_of_a_500(): void
    {
        $this->loginAsAdmin();
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $faculty = $this->makeFaculty('CCS');
        $this->makeSubject('IT101');
        $schedule = $this->makeSchedule('IT101', $faculty->faculty_id);
        (new EnrollmentService())->enroll($student, $schedule, '1st Semester', '2026-2027');

        $response = $this->delete(route('admin.students.destroy', $student));

        $response->assertSessionHasErrors('student');
        $this->assertDatabaseHas('students', ['student_id' => $student->student_id]);
    }

    public function test_deleting_a_department_with_a_program_shows_a_validation_error_instead_of_a_500(): void
    {
        $this->loginAsAdmin();
        $program = $this->makeDepartmentAndProgram();

        $response = $this->delete(route('admin.departments.destroy', $program->department));

        $response->assertSessionHasErrors('department');
        $this->assertDatabaseHas('departments', ['dept_id' => 'CCS']);
    }

    public function test_deleting_a_program_with_a_student_shows_a_validation_error_instead_of_a_500(): void
    {
        $this->loginAsAdmin();
        $program = $this->makeDepartmentAndProgram();
        $this->makeStudent($program);

        $response = $this->delete(route('admin.programs.destroy', $program));

        $response->assertSessionHasErrors('program');
        $this->assertDatabaseHas('programs', ['program_id' => $program->program_id]);
    }

    public function test_deleting_a_subject_with_a_schedule_shows_a_validation_error_instead_of_a_500(): void
    {
        $this->loginAsAdmin();
        $this->makeDepartmentAndProgram();
        $subject = $this->makeSubject('IT101');
        $faculty = $this->makeFaculty('CCS');
        $this->makeSchedule('IT101', $faculty->faculty_id);

        $response = $this->delete(route('admin.subjects.destroy', $subject));

        $response->assertSessionHasErrors('subject');
        $this->assertDatabaseHas('subjects', ['subject_id' => 'IT101']);
    }

    public function test_deleting_a_schedule_with_an_enrollment_shows_a_validation_error_instead_of_a_500(): void
    {
        $this->loginAsAdmin();
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $faculty = $this->makeFaculty('CCS');
        $this->makeSubject('IT101');
        $schedule = $this->makeSchedule('IT101', $faculty->faculty_id);
        (new EnrollmentService())->enroll($student, $schedule, '1st Semester', '2026-2027');

        $response = $this->delete(route('admin.schedules.destroy', $schedule));

        $response->assertSessionHasErrors('schedule');
        $this->assertDatabaseHas('schedules', ['schedule_id' => $schedule->schedule_id]);
    }

    public function test_admin_can_edit_a_class_schedule(): void
    {
        $this->loginAsAdmin();
        $this->makeDepartmentAndProgram();
        $this->makeSubject('IT101');
        $faculty = $this->makeFaculty('CCS');
        $schedule = $this->makeSchedule('IT101', $faculty->faculty_id, 'Monday', '08:00', '10:00', 'Room 101');

        $response = $this->put(route('admin.schedules.update', $schedule), [
            'subject_id' => 'IT101',
            'faculty_id' => $faculty->faculty_id,
            'room_assignment' => 'Room 202',
            'day' => 'Tuesday',
            'start_time' => '13:00',
            'end_time' => '15:00',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('schedules', ['schedule_id' => $schedule->schedule_id, 'room_assignment' => 'Room 202', 'day' => 'Tuesday']);
    }

    public function test_a_non_admin_cannot_access_management_routes(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $this->makeStudent($program, '2026-0001');
        $this->post('/login', ['access_type' => 'student', 'student_id' => '2026-0001', 'student_password' => 'Student@2026']);

        $this->get(route('admin.management'))->assertRedirect(route('login'));
        $this->post(route('admin.departments.store'), ['dept_id' => 'X', 'dept_name' => 'X'])->assertRedirect(route('login'));
    }
}
