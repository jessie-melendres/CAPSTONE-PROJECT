<?php

namespace App\Http\Controllers\Faculty;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\User;
use App\Services\GradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    /** Faculty can only encode grades for schedules credited to their own account. */
    protected function authorizeSchedule(Request $request, Schedule $schedule): void
    {
        $faculty = User::findOrFail($request->session()->get('user.user_id'))->faculty;

        abort_unless($faculty && $schedule->faculty_id === $faculty->faculty_id, 403, 'This class is not credited to your account.');
    }

    public function show(Request $request, Schedule $schedule)
    {
        $this->authorizeSchedule($request, $schedule);

        $semester = config('academic.current_semester');
        $schoolYear = config('academic.current_school_year');

        $enrollments = $schedule->enrollments()
            ->where('semester', $semester)
            ->where('school_year', $schoolYear)
            ->where('status', 'Enrolled')
            ->with(['student', 'grade'])
            ->get();

        return view('faculty.grades', [
            'schedule' => $schedule->load('subject'),
            'enrollments' => $enrollments,
            'semester' => $semester,
            'schoolYear' => $schoolYear,
        ]);
    }

    public function store(Request $request, Enrollment $enrollment, GradeService $gradeService): RedirectResponse
    {
        $this->authorizeSchedule($request, $enrollment->schedule);

        $data = $request->validate([
            'prelim_grade' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'midterm_grade' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'final_grade' => ['nullable', 'numeric', 'min:1', 'max:5'],
        ]);

        $user = User::findOrFail($request->session()->get('user.user_id'));

        try {
            $gradeService->record($enrollment, $data, $user);
        } catch (BusinessRuleException $e) {
            return back()->withErrors(['grade' => $e->getMessage()]);
        }

        return back()->with('status', "Grade saved for {$enrollment->student->fullName()}.");
    }

    public function lock(Request $request, Enrollment $enrollment, GradeService $gradeService): RedirectResponse
    {
        $this->authorizeSchedule($request, $enrollment->schedule);

        try {
            $gradeService->lock($enrollment->grade);
        } catch (BusinessRuleException $e) {
            return back()->withErrors(['grade' => $e->getMessage()]);
        }

        return back()->with('status', "Grade locked for {$enrollment->student->fullName()}.");
    }

    public function announcementStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'target_audience' => ['required', 'string', 'in:All,Students'],
        ]);

        Announcement::create($data + [
            'date_posted' => now(),
            'author_id' => $request->session()->get('user.user_id'),
        ]);

        return back()->with('status', 'Announcement posted.');
    }
}
