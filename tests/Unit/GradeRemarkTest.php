<?php

namespace Tests\Unit;

use App\Models\Grade;
use Tests\TestCase;

class GradeRemarkTest extends TestCase
{
    public function test_a_final_grade_at_or_below_the_threshold_passes(): void
    {
        $this->assertSame(Grade::REMARK_PASSED, Grade::computeRemark(2.0, 2.0, 3.00));
        $this->assertSame(Grade::REMARK_PASSED, Grade::computeRemark(1.0, 1.0, 1.00));
    }

    public function test_a_final_grade_above_the_threshold_fails(): void
    {
        $this->assertSame(Grade::REMARK_FAILED, Grade::computeRemark(3.0, 3.5, 3.01));
        $this->assertSame(Grade::REMARK_FAILED, Grade::computeRemark(4.0, 5.0, 5.00));
    }

    public function test_no_final_grade_yet_is_incomplete(): void
    {
        $this->assertSame(Grade::REMARK_INCOMPLETE, Grade::computeRemark(1.5, 1.5, null));
    }

    public function test_no_grades_at_all_has_no_grade_remark(): void
    {
        $this->assertSame(Grade::REMARK_NO_GRADE, Grade::computeRemark(null, null, null));
    }

    public function test_semestral_average_requires_all_three_terms(): void
    {
        $grade = new Grade(['prelim_grade' => 1.5, 'midterm_grade' => 2.0, 'final_grade' => 2.5]);
        $this->assertSame(2.0, $grade->semestralAverage());

        $incomplete = new Grade(['prelim_grade' => 1.5, 'midterm_grade' => null, 'final_grade' => null]);
        $this->assertNull($incomplete->semestralAverage());
    }

    public function test_the_semestral_average_decides_the_remark_not_the_final_term_alone(): void
    {
        // Final term alone would fail (3.5 > 3.00), but the semestral average (1.83) passes.
        $this->assertSame(Grade::REMARK_PASSED, Grade::computeRemark(1.0, 1.0, 3.5));

        // Final term alone would pass (2.0 <= 3.00), but the semestral average (3.83) fails.
        $this->assertSame(Grade::REMARK_FAILED, Grade::computeRemark(5.0, 4.5, 2.0));
    }

    public function test_a_missing_prelim_or_midterm_is_incomplete_even_with_a_final_grade(): void
    {
        $this->assertSame(Grade::REMARK_INCOMPLETE, Grade::computeRemark(null, 2.0, 2.0));
        $this->assertSame(Grade::REMARK_INCOMPLETE, Grade::computeRemark(2.0, null, 2.0));
    }
}
