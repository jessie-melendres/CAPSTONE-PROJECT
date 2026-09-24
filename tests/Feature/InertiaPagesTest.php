<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InertiaPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_page_is_an_inertia_page(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Login'));
    }

    public function test_the_landing_page_is_an_inertia_page_with_contact_details(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->has('contact.email')
                ->has('announcements', 0));
    }

    public function test_the_landing_page_shows_at_most_three_all_audience_announcements(): void
    {
        $admin = User::create(['username' => 'admin', 'password' => bcrypt('x'), 'role' => User::ROLE_ADMIN]);
        foreach (range(1, 4) as $i) {
            Announcement::create(['title' => "Public {$i}", 'content' => 'c', 'target_audience' => Announcement::AUDIENCE_ALL, 'author_id' => $admin->user_id, 'date_posted' => now()->addMinutes($i)]);
        }
        Announcement::create(['title' => 'Faculty only', 'content' => 'c', 'target_audience' => Announcement::AUDIENCE_FACULTY, 'author_id' => $admin->user_id, 'date_posted' => now()->addHour()]);

        $this->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('announcements', 3)
                ->where('announcements.0.title', 'Public 4')
                ->missing('announcements.0.author_id'));
    }

    public function test_the_admin_dashboard_is_an_inertia_page_with_the_institute_counts(): void
    {
        $this->withSession(['user' => ['user_id' => 1, 'role' => User::ROLE_ADMIN, 'username' => 'admin']])
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->hasAll(['studentCount', 'subjectCount', 'scheduleCount', 'facultyCount', 'enrollmentCount'])
                ->where('auth.user.role', User::ROLE_ADMIN));
    }
}
