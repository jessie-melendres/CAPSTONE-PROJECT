<?php

namespace Tests\Feature;

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
