<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\LedgerEntry;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\AcademicStructureService;
use App\Services\EnrollmentService;
use App\Services\FacultyService;
use App\Services\GradeService;
use App\Services\LedgerService;
use App\Services\ScheduleService;
use App\Services\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    protected const VIEWS = ['departments', 'programs', 'students', 'faculty', 'subjects', 'schedules', 'loads', 'ledger', 'announcements'];

    public function management(Request $request)
    {
        $view = in_array($request->query('view'), self::VIEWS, true) ? $request->query('view') : 'students';

        return view('admin.management', [
            'activeView' => $view,
            'departments' => Department::orderBy('dept_name')->get(),
            'programs' => Program::with('department')->orderBy('program_name')->get(),
            'students' => Student::with('program')->orderBy('last_name')->get(),
            'facultyMembers' => Faculty::with('department')->orderBy('last_name')->get(),
            'subjects' => Subject::with('prerequisite')->orderBy('subject_id')->get(),
            'schedules' => Schedule::with(['subject', 'faculty'])->orderBy('day')->get(),
            'enrollments' => Enrollment::with(['student', 'schedule.subject'])->latest('enrollment_id')->limit(50)->get(),
            'ledgerEntries' => LedgerEntry::with('student')->latest('entry_date')->limit(50)->get(),
            'announcements' => Announcement::with('author')->latest('date_posted')->get(),
            'currentSemester' => config('academic.current_semester'),
            'currentSchoolYear' => config('academic.current_school_year'),
        ]);
    }

    // ------------------------------------------------------------------
    // Departments
    // ------------------------------------------------------------------

    public function departmentStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'dept_id' => ['required', 'string', 'max:20', 'unique:departments,dept_id'],
            'dept_name' => ['required', 'string', 'max:100'],
        ]);

        Department::create($data);

        return back()->with('status', 'Department added.');
    }

    public function departmentDestroy(Department $department, AcademicStructureService $academicStructureService): RedirectResponse
    {
        try {
            $academicStructureService->deleteDepartment($department);
        } catch (BusinessRuleException $e) {
            return back()->withErrors(['department' => $e->getMessage()]);
        }

        return back()->with('status', 'Department removed.');
    }

    // ------------------------------------------------------------------
    // Programs
    // ------------------------------------------------------------------

    public function programStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'program_id' => ['required', 'string', 'max:20', 'unique:programs,program_id'],
            'program_name' => ['required', 'string', 'max:100'],
            'dept_id' => ['required', 'string', 'exists:departments,dept_id'],
        ]);

        Program::create($data);

        return back()->with('status', 'Program added.');
    }

    public function programDestroy(Program $program, AcademicStructureService $academicStructureService): RedirectResponse
    {
        try {
            $academicStructureService->deleteProgram($program);
        } catch (BusinessRuleException $e) {
            return back()->withErrors(['program' => $e->getMessage()]);
        }

        return back()->with('status', 'Program removed.');
    }

    // ------------------------------------------------------------------
    // Students (creates the linked user account)
    // ------------------------------------------------------------------

    public function studentStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'regex:/^[0-9]{4}-[0-9]{4}$/', 'unique:students,student_id'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email_address' => ['nullable', 'email', 'max:100', 'unique:students,email_address'],
            'program_id' => ['required', 'string', 'exists:programs,program_id'],
            'year_level' => ['required', 'integer', 'min:1', 'max:6'],
            'initial_password' => ['required', 'string', 'min:6'],
        ], [], ['student_id' => 'Student ID Number']);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'username' => $data['student_id'],
                'password' => Hash::make($data['initial_password']),
                'role' => User::ROLE_STUDENT,
            ]);

            Student::create([
                'student_id' => $data['student_id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email_address' => $data['email_address'] ?? null,
                'program_id' => $data['program_id'],
                'year_level' => $data['year_level'],
                'user_id' => $user->user_id,
            ]);
        });

        return back()->with('status', 'Student account created.');
    }

    public function studentEdit(Student $student)
    {
        return view('admin.students.edit', [
            'student' => $student->load('program'),
            'programs' => Program::orderBy('program_name')->get(),
        ]);
    }

    public function studentUpdate(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email_address' => ['nullable', 'email', 'max:100', 'unique:students,email_address,'.$student->student_id.',student_id'],
            'program_id' => ['required', 'string', 'exists:programs,program_id'],
            'year_level' => ['required', 'integer', 'min:1', 'max:6'],
            'status' => ['required', 'string', 'in:Active,Inactive,Graduated'],
        ]);

        $student->update($data);

        return back()->with('status', 'Student record updated.');
    }

    public function studentDestroy(Student $student, StudentService $studentService): RedirectResponse
    {
        try {
            $studentService->delete($student);
        } catch (BusinessRuleException $e) {
            return back()->withErrors(['student' => $e->getMessage()]);
        }

        return back()->with('status', 'Student record removed.');
    }

    // ------------------------------------------------------------------
    // Faculty (creates the linked user account)
    // ------------------------------------------------------------------

    public function facultyStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'faculty_id' => ['required', 'string', 'max:20', 'unique:faculty,faculty_id'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'dept_id' => ['required', 'string', 'exists:departments,dept_id'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'initial_password' => ['required', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['initial_password']),
                'role' => User::ROLE_FACULTY,
            ]);

            Faculty::create([
                'faculty_id' => $data['faculty_id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'dept_id' => $data['dept_id'],
                'user_id' => $user->user_id,
            ]);
        });

        return back()->with('status', 'Faculty account created.');
    }

    public function facultyEdit(Faculty $facultyMember)
    {
        return view('admin.faculty.edit', [
            'facultyMember' => $facultyMember->load('department'),
            'departments' => Department::orderBy('dept_name')->get(),
        ]);
    }

    public function facultyUpdate(Request $request, Faculty $facultyMember, FacultyService $facultyService): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'dept_id' => ['required', 'string', 'exists:departments,dept_id'],
            'status' => ['required', 'string', 'in:Teaching,On leave,Inactive'],
            'replacement_faculty_id' => ['nullable', 'string', 'exists:faculty,faculty_id'],
        ]);

        try {
            DB::transaction(function () use ($facultyMember, $data, $facultyService) {
                $facultyMember->update([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'dept_id' => $data['dept_id'],
                ]);

                $facultyService->updateStatus($facultyMember, $data['status'], $data['replacement_faculty_id'] ?? null);
            });
        } catch (BusinessRuleException $e) {
            return back()->withErrors(['faculty' => $e->getMessage()])->withInput();
        }

        return back()->with('status', 'Faculty record updated.');
    }

    public function facultyDestroy(Faculty $facultyMember, FacultyService $facultyService): RedirectResponse
    {
        try {
            $facultyService->delete($facultyMember);
        } catch (BusinessRuleException $e) {
            return back()->withErrors(['faculty' => $e->getMessage()]);
        }

        return back()->with('status', 'Faculty record removed.');
    }

    // ------------------------------------------------------------------
    // Subjects (course offerings)
    // ------------------------------------------------------------------

    public function subjectStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject_id' => ['required', 'string', 'max:20', 'unique:subjects,subject_id'],
            'subject_name' => ['required', 'string', 'max:100'],
            'units' => ['required', 'integer', 'min:1', 'max:6'],
            'prerequisite_subject_id' => ['nullable', 'string', 'exists:subjects,subject_id'],
        ]);

        Subject::create($data);

        return back()->with('status', 'Subject added.');
    }

    public function subjectUpdate(Request $request, Subject $subject): RedirectResponse
    {
        $data = $request->validate([
            'subject_name' => ['required', 'string', 'max:100'],
            'units' => ['required', 'integer', 'min:1', 'max:6'],
            'prerequisite_subject_id' => ['nullable', 'string', 'exists:subjects,subject_id'],
        ]);

        $subject->update($data);

        return back()->with('status', 'Subject updated.');
    }

    public function subjectDestroy(Subject $subject, AcademicStructureService $academicStructureService): RedirectResponse
    {
        try {
            $academicStructureService->deleteSubject($subject);
        } catch (BusinessRuleException $e) {
            return back()->withErrors(['subject' => $e->getMessage()]);
        }

        return back()->with('status', 'Subject removed.');
    }

    // ------------------------------------------------------------------
    // Schedules (class offerings — conflict-checked)
    // ------------------------------------------------------------------

    public function scheduleStore(Request $request, ScheduleService $scheduleService): RedirectResponse
    {
        $data = $request->validate([
            'subject_id' => ['required', 'string', 'exists:subjects,subject_id'],
            'faculty_id' => ['required', 'string', 'exists:faculty,faculty_id'],
            'room_assignment' => ['required', 'string', 'max:50'],
            'day' => ['required', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        try {
            $scheduleService->create($data);
        } catch (BusinessRuleException $e) {
            return back()->withErrors(['schedule' => $e->getMessage()])->withInput();
        }

        return back()->with('status', 'Class schedule added.');
    }

    public function scheduleUpdate(Request $request, Schedule $schedule, ScheduleService $scheduleService): RedirectResponse
    {
        $data = $request->validate([
            'subject_id' => ['required', 'string', 'exists:subjects,subject_id'],
            'faculty_id' => ['required', 'string', 'exists:faculty,faculty_id'],
            'room_assignment' => ['required', 'string', 'max:50'],
            'day' => ['required', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        try {
            $scheduleService->update($schedule, $data);
        } catch (BusinessRuleException $e) {
            return back()->withErrors(['schedule' => $e->getMessage()])->withInput();
        }

        return back()->with('status', 'Class schedule updated.');
    }

    public function scheduleDestroy(Schedule $schedule, ScheduleService $scheduleService): RedirectResponse
    {
        try {
            $scheduleService->delete($schedule);
        } catch (BusinessRuleException $e) {
            return back()->withErrors(['schedule' => $e->getMessage()]);
        }

        return back()->with('status', 'Class schedule removed.');
    }

    // ------------------------------------------------------------------
    // Enrollments / subject loads
    // ------------------------------------------------------------------

    public function enrollmentStore(Request $request, EnrollmentService $enrollmentService): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'string', 'exists:students,student_id'],
            'schedule_id' => ['required', 'integer', 'exists:schedules,schedule_id'],
            'semester' => ['required', 'string', 'max:20'],
            'school_year' => ['required', 'string', 'max:20'],
        ]);

        $student = Student::findOrFail($data['student_id']);
        $schedule = Schedule::findOrFail($data['schedule_id']);

        try {
            $enrollmentService->enroll($student, $schedule, $data['semester'], $data['school_year']);
        } catch (BusinessRuleException $e) {
            return back()->withErrors(['enrollment' => $e->getMessage()])->withInput();
        }

        return back()->with('status', 'Student enrolled in class.');
    }

    public function enrollmentDestroy(Enrollment $enrollment, EnrollmentService $enrollmentService): RedirectResponse
    {
        $enrollmentService->drop($enrollment);

        return back()->with('status', 'Enrollment dropped.');
    }

    // ------------------------------------------------------------------
    // Grades (admin override — reopening a faculty-locked grade)
    // ------------------------------------------------------------------

    public function gradeUnlock(Enrollment $enrollment, GradeService $gradeService): RedirectResponse
    {
        abort_unless($enrollment->grade, 404);

        $gradeService->unlock($enrollment->grade);

        return back()->with('status', "Grade unlocked for {$enrollment->student->fullName()}.");
    }

    // ------------------------------------------------------------------
    // Portal Ledger (manual charges/payments; read-only for students)
    // ------------------------------------------------------------------

    public function ledgerStore(Request $request, LedgerService $ledgerService): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'string', 'exists:students,student_id'],
            'entry_type' => ['required', 'string', 'in:CHARGE,PAYMENT'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string', 'max:150'],
            'entry_date' => ['required', 'date'],
        ]);

        $student = Student::findOrFail($data['student_id']);

        $ledgerService->recordEntry(
            $student,
            $data['entry_type'],
            (float) $data['amount'],
            $data['description'],
            User::findOrFail($request->session()->get('user.user_id')),
            $data['entry_date']
        );

        return back()->with('status', 'Ledger entry recorded.');
    }

    // ------------------------------------------------------------------
    // Announcements (targeted notifications)
    // ------------------------------------------------------------------

    public function announcementStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'target_audience' => ['required', 'string', 'in:All,Students,Faculty'],
        ]);

        Announcement::create($data + [
            'date_posted' => now(),
            'author_id' => $request->session()->get('user.user_id'),
        ]);

        return back()->with('status', 'Announcement posted.');
    }

    public function announcementDestroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('status', 'Announcement removed.');
    }
}
