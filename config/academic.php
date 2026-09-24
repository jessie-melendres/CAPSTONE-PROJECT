<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Maximum Allowable Units Per Term
    |--------------------------------------------------------------------------
    |
    | Enrollment Business Control from the Gantt roadmap ("Enforce Enrollment
    | Business Controls - Max Allowable Units"). Not specified numerically in
    | the capstone paper; 24 is the typical per-semester cap for a Philippine
    | college term and is documented as an assumption in docs/ERD-ADDENDUM.md.
    |
    */
    'max_units_per_term' => env('MAX_UNITS_PER_TERM', 24),

    /*
    |--------------------------------------------------------------------------
    | Current Term
    |--------------------------------------------------------------------------
    |
    | The dashboards and new-enrollment forms default to this term. The paper
    | has no live "current term" concept in its schema, so this is an
    | operational setting an administrator updates each semester (see
    | docs/ERD-ADDENDUM.md).
    |
    */
    'current_semester' => env('CURRENT_SEMESTER', '1st Semester'),
    'current_school_year' => env('CURRENT_SCHOOL_YEAR', '2026-2027'),
];
