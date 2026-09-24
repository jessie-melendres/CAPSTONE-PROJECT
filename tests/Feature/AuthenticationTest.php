<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;
    use AcademicTestHelpers;

    public function test_a_student_can_log_in_with_id_and_password(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $this->makeStudent($program, '2026-0001');

        $response = $this->post('/login', [
            'access_type' => 'student',
            'student_id' => '2026-0001',
            'student_password' => 'Student@2026',
        ]);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertSame('student', session('user.role'));
    }

    public function test_a_student_cannot_log_in_with_the_wrong_password(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $this->makeStudent($program, '2026-0001');

        $response = $this->post('/login', [
            'access_type' => 'student',
            'student_id' => '2026-0001',
            'student_password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertNull(session('user'));
    }

    public function test_passwords_are_hashed_not_stored_in_plain_text(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program, '2026-0001');

        $stored = User::where('user_id', $student->user_id)->value('password');

        $this->assertNotSame('Student@2026', $stored);
        $this->assertTrue(Hash::check('Student@2026', $stored));
    }

    public function test_a_deactivated_account_cannot_log_in(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program, '2026-0001');
        User::where('user_id', $student->user_id)->update(['is_active' => false]);

        $response = $this->post('/login', [
            'access_type' => 'student',
            'student_id' => '2026-0001',
            'student_password' => 'Student@2026',
        ]);

        $response->assertSessionHasErrors('login');
    }

    public function test_a_student_session_cannot_reach_the_administrator_area(): void
    {
        $program = $this->makeDepartmentAndProgram();
        $this->makeStudent($program, '2026-0001');

        $this->post('/login', ['access_type' => 'student', 'student_id' => '2026-0001', 'student_password' => 'Student@2026']);

        $response = $this->get('/administrator');

        $response->assertRedirect(route('login'));
    }

    public function test_a_faculty_session_cannot_reach_the_administrator_area(): void
    {
        $this->makeDepartmentAndProgram();
        $faculty = $this->makeFaculty('CCS');

        $this->post('/login', ['access_type' => 'staff', 'staff_email' => $faculty->user->username, 'staff_password' => 'Faculty@2026']);

        $response = $this->get('/administrator');

        $response->assertRedirect(route('login'));
    }

    public function test_guests_are_redirected_away_from_protected_dashboards(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/faculty')->assertRedirect(route('login'));
        $this->get('/administrator')->assertRedirect(route('login'));
    }
}
