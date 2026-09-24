<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class PortalController extends Controller
{
    public function login()
    {
        return Inertia::render('Login', ['contact' => config('portal.contact')]);
    }

    public function authenticate(Request $request)
    {
        $request->validate(['access_type' => ['required', 'in:student,staff']]);

        if ($request->input('access_type') === 'student') {
            $request->validate([
                'student_id' => ['required', 'regex:/^[0-9]{4}-?[0-9]{4}$/'],
                'student_password' => ['required'],
            ], [], ['student_id' => 'Student ID Number']);

            $username = trim((string) $request->input('student_id'));
            $password = (string) $request->input('student_password');
            $allowedRoles = [User::ROLE_STUDENT];
        } else {
            $request->validate([
                'staff_email' => ['required'],
                'staff_password' => ['required'],
            ]);

            $username = strtolower(trim((string) $request->input('staff_email')));
            $password = (string) $request->input('staff_password');
            $allowedRoles = [User::ROLE_ADMIN, User::ROLE_FACULTY];
        }

        $user = User::where('username', $username)->whereIn('role', $allowedRoles)->first();

        if (! $user || ! $user->is_active || ! Hash::check($password, $user->password)) {
            return back()->withErrors(['login' => 'The username/ID or password is incorrect.'])->withInput();
        }

        $request->session()->regenerate();
        $request->session()->put('user', [
            'user_id' => $user->user_id,
            'role' => $user->role,
            'username' => $user->username,
        ]);

        // A full-page visit: some dashboards are still Blade pages, which Inertia
        // would otherwise render inside a modal over the login page.
        return Inertia::location(route(match ($user->role) {
            User::ROLE_ADMIN => 'admin.dashboard',
            User::ROLE_FACULTY => 'faculty.dashboard',
            default => 'student.dashboard',
        }));
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function home()
    {
        $announcements = Announcement::where('target_audience', Announcement::AUDIENCE_ALL)
            ->latest('date_posted')
            ->limit(3)
            ->get()
            ->map(fn (Announcement $announcement) => [
                'id' => $announcement->announcement_id,
                'title' => $announcement->title,
                'content' => $announcement->content,
                'date_posted' => $announcement->date_posted->format('M j, Y'),
            ]);

        return Inertia::render('Home', [
            'announcements' => $announcements,
            'contact' => config('portal.contact'),
            'term' => config('academic.current_semester').' · '.config('academic.current_school_year'),
        ]);
    }

    protected function currentUser(Request $request): User
    {
        return User::findOrFail($request->session()->get('user.user_id'));
    }

    public function studentDashboard(Request $request, LedgerService $ledger)
    {
        $student = $this->currentUser($request)->student()->with('program.department')->firstOrFail();

        $semester = config('academic.current_semester');
        $schoolYear = config('academic.current_school_year');

        $enrollments = $student->enrollmentsForTerm($semester, $schoolYear)
            ->with(['schedule.subject', 'schedule.faculty', 'grade'])
            ->get();

        $units = $enrollments->sum(fn ($e) => $e->schedule->subject->units ?? 0);
        $termsGraded = $enrollments->filter(fn ($e) => $e->grade && $e->grade->semestralAverage() !== null);
        $generalAverage = $termsGraded->isNotEmpty()
            ? round($termsGraded->avg(fn ($e) => $e->grade->semestralAverage()), 2)
            : null;

        $announcements = Announcement::visibleTo('student')->latest('date_posted')->limit(5)->get();

        return view('dashboard', [
            'student' => $student,
            'semester' => $semester,
            'schoolYear' => $schoolYear,
            'enrollments' => $enrollments,
            'units' => $units,
            'generalAverage' => $generalAverage,
            'balance' => $ledger->balance($student),
            'announcements' => $announcements,
        ]);
    }

    public function facultyDashboard(Request $request)
    {
        $faculty = $this->currentUser($request)->faculty()->with('department')->firstOrFail();

        $semester = config('academic.current_semester');
        $schoolYear = config('academic.current_school_year');

        $schedules = $faculty->schedules()
            ->with(['subject', 'enrollments' => fn ($q) => $q->where('semester', $semester)->where('school_year', $schoolYear)->where('status', 'Enrolled')])
            ->get();

        $announcements = Announcement::visibleTo('faculty')->latest('date_posted')->limit(5)->get();

        return view('faculty-dashboard', [
            'faculty' => $faculty,
            'schedules' => $schedules,
            'semester' => $semester,
            'schoolYear' => $schoolYear,
            'announcements' => $announcements,
        ]);
    }

    public function adminDashboard(Request $request)
    {
        return Inertia::render('Admin/Dashboard', [
            'studentCount' => \App\Models\Student::count(),
            'subjectCount' => \App\Models\Subject::count(),
            'scheduleCount' => \App\Models\Schedule::count(),
            'facultyCount' => \App\Models\Faculty::count(),
            'enrollmentCount' => \App\Models\Enrollment::where('status', 'Enrolled')->count(),
        ]);
    }
}
