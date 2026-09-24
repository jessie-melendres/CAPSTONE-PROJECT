<?php

namespace Tests\Feature;

use App\Exceptions\BusinessRuleException;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\GradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeServiceTest extends TestCase
{
    use RefreshDatabase;
    use AcademicTestHelpers;

    protected GradeService $gradeService;

    protected $enrollment;

    protected User $recorder;

    protected function setUp(): void
    {
        parent::setUp();

        $program = $this->makeDepartmentAndProgram();
        $student = $this->makeStudent($program);
        $faculty = $this->makeFaculty('CCS');
        $this->makeSubject('IT101');
        $schedule = $this->makeSchedule('IT101', $faculty->faculty_id);

        $this->enrollment = (new EnrollmentService())->enroll($student, $schedule, '1st Semester', '2026-2027');
        $this->recorder = $faculty->user;
        $this->gradeService = new GradeService();
    }

    public function test_recording_grades_computes_the_remark_and_logs_an_audit_entry(): void
    {
        $grade = $this->gradeService->record($this->enrollment, ['prelim_grade' => 1.5, 'midterm_grade' => 1.5, 'final_grade' => 1.25], $this->recorder);

        $this->assertSame('PASSED', $grade->remarks);
        $this->assertDatabaseHas('grade_audit_logs', ['grade_id' => $grade->grade_id, 'field_changed' => 'final_grade', 'new_value' => '1.25']);
    }

    public function test_updating_a_grade_logs_the_old_and_new_value(): void
    {
        // Only final_grade is set here (prelim/midterm are still missing), so the
        // remark stays INCOMPLETE regardless of the final term's value: the remark
        // is decided by the semestral average of all three terms, not this field alone.
        $grade = $this->gradeService->record($this->enrollment, ['final_grade' => 4.0], $this->recorder);
        $this->assertSame('INC', $grade->remarks);

        $updated = $this->gradeService->record($this->enrollment->fresh(), ['final_grade' => 2.0], $this->recorder);
        $this->assertSame('INC', $updated->remarks);

        $this->assertDatabaseHas('grade_audit_logs', [
            'grade_id' => $grade->grade_id,
            'field_changed' => 'final_grade',
            'old_value' => '4',
            'new_value' => '2',
        ]);
    }

    public function test_a_grade_cannot_be_recorded_without_a_final_grade(): void
    {
        $grade = $this->gradeService->record($this->enrollment, ['prelim_grade' => 1.5], $this->recorder);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('final grade must be recorded');

        $this->gradeService->lock($grade);
    }

    public function test_a_locked_grade_rejects_further_writes(): void
    {
        $grade = $this->gradeService->record($this->enrollment, ['final_grade' => 1.5], $this->recorder);
        $this->gradeService->lock($grade->fresh());

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('locked');

        $this->gradeService->record($this->enrollment->fresh(), ['final_grade' => 2.5], $this->recorder);
    }

    public function test_unlocking_allows_edits_again(): void
    {
        $grade = $this->gradeService->record($this->enrollment, ['final_grade' => 1.5], $this->recorder);
        $locked = $this->gradeService->lock($grade->fresh());
        $this->assertTrue($locked->is_locked);

        $unlocked = $this->gradeService->unlock($locked);
        $this->assertFalse($unlocked->is_locked);

        $edited = $this->gradeService->record($this->enrollment->fresh(), ['final_grade' => 2.75], $this->recorder);
        $this->assertSame(2.75, $edited->final_grade);
    }
}
