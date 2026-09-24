<?php

namespace Tests\Feature;

use App\Exceptions\BusinessRuleException;
use App\Models\Faculty;
use App\Services\FacultyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyServiceTest extends TestCase
{
    use RefreshDatabase;
    use AcademicTestHelpers;

    protected FacultyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FacultyService();
        $this->makeDepartmentAndProgram();
        $this->makeSubject('IT101');
        $this->makeSubject('IT102');
    }

    public function test_a_faculty_member_with_no_classes_can_go_on_leave_without_a_replacement(): void
    {
        $faculty = $this->makeFaculty('CCS', 'FAC-001');

        $this->service->updateStatus($faculty, Faculty::STATUS_ON_LEAVE);

        $this->assertSame(Faculty::STATUS_ON_LEAVE, $faculty->fresh()->status);
    }

    public function test_going_on_leave_with_classes_and_no_replacement_is_rejected(): void
    {
        $faculty = $this->makeFaculty('CCS', 'FAC-001');
        $this->makeSchedule('IT101', $faculty->faculty_id);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('choose a replacement');

        $this->service->updateStatus($faculty, Faculty::STATUS_ON_LEAVE);
    }

    public function test_going_on_leave_reassigns_existing_classes_to_the_chosen_replacement(): void
    {
        $faculty = $this->makeFaculty('CCS', 'FAC-001');
        $replacement = $this->makeFaculty('CCS', 'FAC-002');
        $schedule = $this->makeSchedule('IT101', $faculty->faculty_id, 'Monday', '08:00', '10:00', 'Room 101');

        $this->service->updateStatus($faculty, Faculty::STATUS_ON_LEAVE, $replacement->faculty_id);

        $this->assertSame(Faculty::STATUS_ON_LEAVE, $faculty->fresh()->status);
        $this->assertSame($replacement->faculty_id, $schedule->fresh()->faculty_id);
    }

    public function test_the_replacement_must_themselves_be_teaching(): void
    {
        $faculty = $this->makeFaculty('CCS', 'FAC-001');
        $replacement = $this->makeFaculty('CCS', 'FAC-002', Faculty::STATUS_ON_LEAVE);
        $this->makeSchedule('IT101', $faculty->faculty_id);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('not Teaching');

        $this->service->updateStatus($faculty, Faculty::STATUS_ON_LEAVE, $replacement->faculty_id);
    }

    public function test_reassignment_is_rejected_if_it_would_double_book_the_replacement(): void
    {
        $faculty = $this->makeFaculty('CCS', 'FAC-001');
        $replacement = $this->makeFaculty('CCS', 'FAC-002');
        $this->makeSchedule('IT101', $faculty->faculty_id, 'Monday', '08:00', '10:00', 'Room 101');
        // The replacement already teaches an overlapping class elsewhere.
        $this->makeSchedule('IT102', $replacement->faculty_id, 'Monday', '09:00', '11:00', 'Room 202');

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('conflicts');

        $this->service->updateStatus($faculty, Faculty::STATUS_ON_LEAVE, $replacement->faculty_id);
    }

    public function test_a_faculty_member_with_no_classes_can_be_deleted(): void
    {
        $faculty = $this->makeFaculty('CCS', 'FAC-001');
        $userId = $faculty->user_id;

        $this->service->delete($faculty);

        $this->assertDatabaseMissing('faculty', ['faculty_id' => 'FAC-001']);
        $this->assertDatabaseMissing('users', ['user_id' => $userId]);
    }

    public function test_a_faculty_member_with_existing_classes_cannot_be_deleted(): void
    {
        $faculty = $this->makeFaculty('CCS', 'FAC-001');
        $this->makeSchedule('IT101', $faculty->faculty_id);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('reassign or remove their classes first');

        $this->service->delete($faculty);
    }
}
