<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\LedgerEntry;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Extract-Transform-Load seeder that migrates the institute's only existing
 * digital records — the `mock-data.js` UI prototype dataset — into the real
 * schema. This stands in for "Phase 5: Legacy Data Migration" from the Gantt
 * roadmap: no paper/spreadsheet export from NCBII exists to import, so this
 * prototype dataset is the closest thing to a legacy source, and this class
 * demonstrates the same extract -> transform -> load pipeline against it.
 *
 * Values below (department/program codes, day/start/end times split from the
 * old combined "time" string, faculty usernames, demo passwords) are
 * transformations/assumptions not present in the original JS object; see
 * docs/ERD-ADDENDUM.md for the full list.
 */
class LegacyDataSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Extract: the legacy dataset, transcribed from mock-data.js ----
        $legacyFaculty = [
            ['id' => 'FAC-001', 'name' => 'Prof. Daniel Cruz', 'status' => 'Teaching'],
            ['id' => 'FAC-002', 'name' => 'Prof. Ana Lim', 'status' => 'Teaching'],
            ['id' => 'FAC-003', 'name' => 'Prof. Rosa Velasco', 'status' => 'On leave'],
        ];

        $legacySubjects = [
            ['code' => 'IT101', 'name' => 'Introduction to Information Technology', 'units' => 3, 'prerequisite' => null],
            ['code' => 'IT102', 'name' => 'Programming 1', 'units' => 3, 'prerequisite' => 'IT101'],
            ['code' => 'IT103', 'name' => 'Database Fundamentals', 'units' => 3, 'prerequisite' => 'IT101'],
        ];

        $legacyClasses = [
            ['id' => 'CLASS-001', 'code' => 'IT101', 'facultyId' => 'FAC-001', 'day' => 'Monday', 'start' => '08:00', 'end' => '10:00', 'room' => 'Room 101'],
            ['id' => 'CLASS-002', 'code' => 'IT102', 'facultyId' => 'FAC-002', 'day' => 'Tuesday', 'start' => '10:00', 'end' => '12:00', 'room' => 'Room 102'],
            ['id' => 'CLASS-003', 'code' => 'IT103', 'facultyId' => 'FAC-003', 'day' => 'Wednesday', 'start' => '13:00', 'end' => '15:00', 'room' => 'Laboratory 1'],
        ];

        $legacyStudents = [
            ['id' => '2026-0001', 'firstName' => 'Maria', 'lastName' => 'Santos', 'email' => 'maria.santos@ncbii.edu'],
            ['id' => '2026-0002', 'firstName' => 'John', 'lastName' => 'Reyes', 'email' => 'john.reyes@ncbii.edu'],
            ['id' => '2026-0003', 'firstName' => 'Angela', 'lastName' => 'Cruz', 'email' => 'angela.cruz@ncbii.edu'],
            ['id' => '2026-0004', 'firstName' => 'Pedro', 'lastName' => 'Santos', 'email' => 'pedro.santos@ncbii.edu'],
            ['id' => '2026-0005', 'firstName' => 'Ana', 'lastName' => 'Reyes', 'email' => 'ana.reyes@ncbii.edu'],
            ['id' => '2026-0006', 'firstName' => 'Luis', 'lastName' => 'Garcia', 'email' => 'luis.garcia@ncbii.edu'],
        ];

        $legacyLoad = [
            ['studentId' => '2026-0001', 'classId' => 'CLASS-001'],
            ['studentId' => '2026-0001', 'classId' => 'CLASS-002'],
            ['studentId' => '2026-0001', 'classId' => 'CLASS-003'],
            ['studentId' => '2026-0004', 'classId' => 'CLASS-001'],
            ['studentId' => '2026-0005', 'classId' => 'CLASS-002'],
            ['studentId' => '2026-0006', 'classId' => 'CLASS-003'],
        ];

        // Term grades as recorded in the prototype; 'INC'/'NG' are non-numeric
        // legacy markers that transform to null term grades (the remark is
        // then re-derived by Grade::computeRemark, which already treats an
        // absent final grade as INC and all-absent as NG).
        $legacyGrades = [
            'CLASS-001' => ['2026-0001' => [1.75, 1.5, 1.25], '2026-0004' => [2.25, 2.0, 1.75]],
            'CLASS-002' => ['2026-0001' => [2.0, 1.75, 1.5], '2026-0005' => [null, null, null]],
            'CLASS-003' => ['2026-0001' => [1.5, 1.25, 1.0], '2026-0006' => [null, null, null]],
        ];

        $semester = config('academic.current_semester');
        $schoolYear = config('academic.current_school_year');

        // ---- Transform + Load ----
        $admin = User::create([
            'username' => 'admin',
            'password' => Hash::make('Admin@2026'),
            'role' => User::ROLE_ADMIN,
        ]);

        $department = Department::create(['dept_id' => 'CCS', 'dept_name' => 'College of Computing Studies']);
        $program = Program::create(['program_id' => 'BSIT', 'program_name' => 'BS Information Technology', 'dept_id' => $department->dept_id]);

        $facultyByLegacyId = [];
        foreach ($legacyFaculty as $row) {
            [$firstName, $lastName] = $this->splitName($row['name']);
            $username = strtolower($firstName.'.'.$lastName);

            $user = User::create([
                'username' => $username,
                'password' => Hash::make('Faculty@2026'),
                'role' => User::ROLE_FACULTY,
            ]);

            $facultyByLegacyId[$row['id']] = Faculty::create([
                'faculty_id' => $row['id'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'user_id' => $user->user_id,
                'dept_id' => $department->dept_id,
                'status' => $row['status'],
            ]);
        }

        $subjectsByCode = [];
        foreach ($legacySubjects as $row) {
            $subjectsByCode[$row['code']] = Subject::create([
                'subject_id' => $row['code'],
                'subject_name' => $row['name'],
                'units' => $row['units'],
            ]);
        }
        // Second pass so prerequisite targets already exist.
        foreach ($legacySubjects as $row) {
            if ($row['prerequisite']) {
                $subjectsByCode[$row['code']]->update(['prerequisite_subject_id' => $row['prerequisite']]);
            }
        }

        $schedulesByLegacyId = [];
        foreach ($legacyClasses as $row) {
            $schedulesByLegacyId[$row['id']] = Schedule::create([
                'subject_id' => $row['code'],
                'faculty_id' => $row['facultyId'],
                'room_assignment' => $row['room'],
                'day' => $row['day'],
                'start_time' => $row['start'],
                'end_time' => $row['end'],
            ]);
        }

        $studentsById = [];
        foreach ($legacyStudents as $row) {
            $user = User::create([
                'username' => $row['id'],
                'password' => Hash::make('Student@2026'),
                'role' => User::ROLE_STUDENT,
            ]);

            $studentsById[$row['id']] = Student::create([
                'student_id' => $row['id'],
                'first_name' => $row['firstName'],
                'last_name' => $row['lastName'],
                'email_address' => $row['email'],
                'program_id' => $program->program_id,
                'user_id' => $user->user_id,
                'year_level' => 2,
            ]);

            // Every seeded student carries the same starting tuition charge the
            // old static prototype hard-coded (PHP 18,500), recorded by the admin.
            LedgerEntry::create([
                'student_id' => $row['id'],
                'description' => "Tuition Fee - {$semester} {$schoolYear}",
                'entry_type' => LedgerEntry::TYPE_CHARGE,
                'amount' => 18500.00,
                'recorded_by_user_id' => $admin->user_id,
                'entry_date' => now()->subMonths(2)->toDateString(),
            ]);
        }

        foreach ($legacyLoad as $row) {
            $enrollment = $studentsById[$row['studentId']]->enrollments()->create([
                'semester' => $semester,
                'school_year' => $schoolYear,
                'schedule_id' => $schedulesByLegacyId[$row['classId']]->schedule_id,
                'status' => 'Enrolled',
            ]);

            $terms = $legacyGrades[$row['classId']][$row['studentId']] ?? null;
            if ($terms !== null) {
                [$prelim, $midterm, $final] = $terms;
                Grade::create([
                    'enrollment_id' => $enrollment->enrollment_id,
                    'prelim_grade' => $prelim,
                    'midterm_grade' => $midterm,
                    'final_grade' => $final,
                    'remarks' => Grade::computeRemark($prelim, $midterm, $final),
                ]);
            }
        }

        Announcement::create([
            'title' => 'Welcome to the NCBII Student Portal',
            'content' => "The portal is now live for {$semester}, {$schoolYear}. Report any discrepancies in your records to the registrar's office.",
            'date_posted' => now()->subWeeks(1),
            'target_audience' => Announcement::AUDIENCE_ALL,
            'author_id' => $admin->user_id,
        ]);
    }

    /** @return array{0: string, 1: string} */
    protected function splitName(string $fullName): array
    {
        $parts = explode(' ', str_replace('Prof. ', '', $fullName));
        $lastName = array_pop($parts);

        return [implode(' ', $parts), $lastName];
    }
}
