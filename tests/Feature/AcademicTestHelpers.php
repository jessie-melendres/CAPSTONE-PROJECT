<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Shared fixture builders for feature tests, kept out of TestCase so each
 * test only pulls in what it needs.
 */
trait AcademicTestHelpers
{
    protected function makeDepartmentAndProgram(): Program
    {
        $department = Department::create(['dept_id' => 'CCS', 'dept_name' => 'College of Computing Studies']);

        return Program::create(['program_id' => 'BSIT', 'program_name' => 'BS Information Technology', 'dept_id' => $department->dept_id]);
    }

    protected function makeStudent(Program $program, string $id = '2026-0001', string $status = Student::STATUS_ACTIVE): Student
    {
        $user = User::create(['username' => $id, 'password' => Hash::make('Student@2026'), 'role' => User::ROLE_STUDENT]);

        return Student::create([
            'student_id' => $id,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'program_id' => $program->program_id,
            'user_id' => $user->user_id,
            'year_level' => 1,
            'status' => $status,
        ]);
    }

    protected function makeFaculty(string $deptId, string $id = 'FAC-001', string $status = Faculty::STATUS_TEACHING): Faculty
    {
        // The login controller lowercases the submitted staff username before
        // comparing, so the stored username must already be lowercase.
        $user = User::create(['username' => strtolower($id), 'password' => Hash::make('Faculty@2026'), 'role' => User::ROLE_FACULTY]);

        return Faculty::create([
            'faculty_id' => $id,
            'first_name' => 'Test',
            'last_name' => 'Faculty',
            'user_id' => $user->user_id,
            'dept_id' => $deptId,
            'status' => $status,
        ]);
    }

    protected function makeSubject(string $id, int $units = 3, ?string $prerequisiteId = null): Subject
    {
        return Subject::create([
            'subject_id' => $id,
            'subject_name' => "Subject {$id}",
            'units' => $units,
            'prerequisite_subject_id' => $prerequisiteId,
        ]);
    }

    protected function makeSchedule(string $subjectId, string $facultyId, string $day = 'Monday', string $start = '08:00', string $end = '10:00', string $room = 'Room 101'): Schedule
    {
        return Schedule::create([
            'subject_id' => $subjectId,
            'faculty_id' => $facultyId,
            'room_assignment' => $room,
            'day' => $day,
            'start_time' => $start,
            'end_time' => $end,
        ]);
    }
}
