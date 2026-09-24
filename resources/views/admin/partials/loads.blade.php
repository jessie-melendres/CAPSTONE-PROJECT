<h2>Subject loads (enrollments)</h2>
<p>Enforces the {{ config('academic.max_units_per_term') }}-unit cap, prerequisite checks, and student schedule conflicts. Showing the 50 most recent.</p>
@include('partials._directory_table', [
    'columns' => ['Student', 'Subject', 'Term', 'Status'],
    'rows' => $enrollments->map(fn ($e) => [
        'cells' => [$e->student->fullName() ?? '—', $e->schedule->subject->subject_name ?? '—', "{$e->semester} {$e->school_year}", $e->status],
        'deleteUrl' => $e->status === 'Enrolled' ? route('admin.enrollments.destroy', $e) : null,
    ]),
])

<h3 style="margin-top:20px; color:#65152b;">Assign student to class</h3>
@include('partials._resource_form', [
    'action' => route('admin.enrollments.store'),
    'submitLabel' => 'Enroll student',
    'fields' => [
        ['name' => 'student_id', 'label' => 'Student', 'type' => 'select', 'required' => true, 'options' => $students->mapWithKeys(fn ($s) => [$s->student_id => "{$s->fullName()} ({$s->student_id})"])],
        ['name' => 'schedule_id', 'label' => 'Class schedule', 'type' => 'select', 'required' => true, 'options' => $schedules->mapWithKeys(fn ($s) => [$s->schedule_id => "{$s->subject->subject_name} — {$s->label()}"])],
        ['name' => 'semester', 'label' => 'Semester', 'required' => true, 'value' => $currentSemester],
        ['name' => 'school_year', 'label' => 'School year', 'required' => true, 'value' => $currentSchoolYear],
    ],
])
