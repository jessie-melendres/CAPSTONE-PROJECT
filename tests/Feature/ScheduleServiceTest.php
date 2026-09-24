<?php

namespace Tests\Feature;

use App\Exceptions\BusinessRuleException;
use App\Models\Faculty;
use App\Models\Program;
use App\Services\EnrollmentService;
use App\Services\ScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleServiceTest extends TestCase
{
    use RefreshDatabase;
    use AcademicTestHelpers;

    protected ScheduleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ScheduleService();
        $this->makeDepartmentAndProgram();
        $this->makeSubject('IT101');
        $this->makeSubject('IT102');
        $this->makeFaculty('CCS', 'FAC-001');
        $this->makeFaculty('CCS', 'FAC-002');
    }

    public function test_non_overlapping_schedules_are_accepted(): void
    {
        $this->service->create(['subject_id' => 'IT101', 'faculty_id' => 'FAC-001', 'room_assignment' => 'Room 101', 'day' => 'Monday', 'start_time' => '08:00', 'end_time' => '10:00']);
        $schedule = $this->service->create(['subject_id' => 'IT102', 'faculty_id' => 'FAC-001', 'room_assignment' => 'Room 101', 'day' => 'Monday', 'start_time' => '10:00', 'end_time' => '12:00']);

        $this->assertDatabaseHas('schedules', ['schedule_id' => $schedule->schedule_id]);
    }

    public function test_the_same_room_cannot_be_double_booked_on_an_overlapping_time(): void
    {
        $this->service->create(['subject_id' => 'IT101', 'faculty_id' => 'FAC-001', 'room_assignment' => 'Room 101', 'day' => 'Monday', 'start_time' => '08:00', 'end_time' => '10:00']);

        $this->expectException(BusinessRuleException::class);

        // Different faculty, same room, overlapping time window.
        $this->service->create(['subject_id' => 'IT102', 'faculty_id' => 'FAC-002', 'room_assignment' => 'Room 101', 'day' => 'Monday', 'start_time' => '09:00', 'end_time' => '11:00']);
    }

    public function test_the_same_faculty_cannot_teach_two_overlapping_classes(): void
    {
        $this->service->create(['subject_id' => 'IT101', 'faculty_id' => 'FAC-001', 'room_assignment' => 'Room 101', 'day' => 'Monday', 'start_time' => '08:00', 'end_time' => '10:00']);

        $this->expectException(BusinessRuleException::class);

        // Same faculty, different room, overlapping time window.
        $this->service->create(['subject_id' => 'IT102', 'faculty_id' => 'FAC-001', 'room_assignment' => 'Room 202', 'day' => 'Monday', 'start_time' => '09:30', 'end_time' => '11:00']);
    }

    public function test_back_to_back_classes_do_not_conflict(): void
    {
        $this->service->create(['subject_id' => 'IT101', 'faculty_id' => 'FAC-001', 'room_assignment' => 'Room 101', 'day' => 'Monday', 'start_time' => '08:00', 'end_time' => '10:00']);

        // Starts exactly when the other ends — not an overlap.
        $schedule = $this->service->create(['subject_id' => 'IT102', 'faculty_id' => 'FAC-001', 'room_assignment' => 'Room 101', 'day' => 'Monday', 'start_time' => '10:00', 'end_time' => '12:00']);

        $this->assertDatabaseHas('schedules', ['schedule_id' => $schedule->schedule_id]);
    }

    public function test_a_different_day_never_conflicts(): void
    {
        $this->service->create(['subject_id' => 'IT101', 'faculty_id' => 'FAC-001', 'room_assignment' => 'Room 101', 'day' => 'Monday', 'start_time' => '08:00', 'end_time' => '10:00']);

        $schedule = $this->service->create(['subject_id' => 'IT102', 'faculty_id' => 'FAC-001', 'room_assignment' => 'Room 101', 'day' => 'Tuesday', 'start_time' => '08:00', 'end_time' => '10:00']);

        $this->assertDatabaseHas('schedules', ['schedule_id' => $schedule->schedule_id]);
    }

    public function test_a_class_cannot_be_assigned_to_a_faculty_member_who_is_not_teaching(): void
    {
        $this->makeFaculty('CCS', 'FAC-003', Faculty::STATUS_ON_LEAVE);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('not Teaching');

        $this->service->create(['subject_id' => 'IT101', 'faculty_id' => 'FAC-003', 'room_assignment' => 'Room 101', 'day' => 'Monday', 'start_time' => '08:00', 'end_time' => '10:00']);
    }

    public function test_a_schedules_room_or_time_can_be_edited(): void
    {
        $schedule = $this->service->create(['subject_id' => 'IT101', 'faculty_id' => 'FAC-001', 'room_assignment' => 'Room 101', 'day' => 'Monday', 'start_time' => '08:00', 'end_time' => '10:00']);

        $updated = $this->service->update($schedule, ['subject_id' => 'IT101', 'faculty_id' => 'FAC-001', 'room_assignment' => 'Room 202', 'day' => 'Tuesday', 'start_time' => '13:00', 'end_time' => '15:00']);

        $this->assertSame('Room 202', $updated->fresh()->room_assignment);
        $this->assertSame('Tuesday', $updated->fresh()->day);
    }

    public function test_a_schedule_with_no_enrollments_can_be_deleted(): void
    {
        $schedule = $this->service->create(['subject_id' => 'IT101', 'faculty_id' => 'FAC-001', 'room_assignment' => 'Room 101', 'day' => 'Monday', 'start_time' => '08:00', 'end_time' => '10:00']);

        $this->service->delete($schedule);

        $this->assertDatabaseMissing('schedules', ['schedule_id' => $schedule->schedule_id]);
    }

    public function test_a_schedule_with_enrollments_cannot_be_deleted(): void
    {
        $student = $this->makeStudent(Program::find('BSIT'));
        $schedule = $this->service->create(['subject_id' => 'IT101', 'faculty_id' => 'FAC-001', 'room_assignment' => 'Room 101', 'day' => 'Monday', 'start_time' => '08:00', 'end_time' => '10:00']);
        (new EnrollmentService())->enroll($student, $schedule, '1st Semester', '2026-2027');

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('enrolled');

        $this->service->delete($schedule);
    }
}
