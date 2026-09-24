<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AnnouncementVisibilityTest extends TestCase
{
    use RefreshDatabase;
    use AcademicTestHelpers;

    protected function postAnnouncement(string $audience, string $content): Announcement
    {
        $admin = User::firstOrCreate(['username' => 'admin'], ['password' => Hash::make('Admin@2026'), 'role' => User::ROLE_ADMIN]);

        return Announcement::create([
            'title' => $content,
            'content' => $content,
            'target_audience' => $audience,
            'author_id' => $admin->user_id,
            'date_posted' => now(),
        ]);
    }

    public function test_a_faculty_only_announcement_appears_on_the_faculty_dashboard(): void
    {
        $this->postAnnouncement(Announcement::AUDIENCE_FACULTY, 'Faculty meeting Friday');
        $this->postAnnouncement(Announcement::AUDIENCE_STUDENTS, 'Enrollment deadline reminder');

        $this->makeDepartmentAndProgram();
        $faculty = $this->makeFaculty('CCS');
        $this->post('/login', ['access_type' => 'staff', 'staff_email' => $faculty->user->username, 'staff_password' => 'Faculty@2026']);

        $response = $this->get(route('faculty.dashboard'));

        $response->assertSee('Faculty meeting Friday');
        $response->assertDontSee('Enrollment deadline reminder');
    }

    public function test_a_students_only_announcement_appears_on_the_student_dashboard(): void
    {
        $this->postAnnouncement(Announcement::AUDIENCE_FACULTY, 'Faculty meeting Friday');
        $this->postAnnouncement(Announcement::AUDIENCE_STUDENTS, 'Enrollment deadline reminder');

        $program = $this->makeDepartmentAndProgram();
        $this->makeStudent($program);
        $this->post('/login', ['access_type' => 'student', 'student_id' => '2026-0001', 'student_password' => 'Student@2026']);

        $response = $this->get(route('student.dashboard'));

        $response->assertSee('Enrollment deadline reminder');
        $response->assertDontSee('Faculty meeting Friday');
    }

    public function test_an_all_audience_announcement_appears_on_both_dashboards(): void
    {
        $this->postAnnouncement(Announcement::AUDIENCE_ALL, 'Campus closed for holiday');

        $this->makeDepartmentAndProgram();
        $faculty = $this->makeFaculty('CCS');
        $this->post('/login', ['access_type' => 'staff', 'staff_email' => $faculty->user->username, 'staff_password' => 'Faculty@2026']);
        $this->get(route('faculty.dashboard'))->assertSee('Campus closed for holiday');
    }
}
