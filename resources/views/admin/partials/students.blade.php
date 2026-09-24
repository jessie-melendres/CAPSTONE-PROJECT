<h2>Students</h2>
@include('partials._directory_table', [
    'columns' => ['Student ID', 'Name', 'Program', 'Year', 'Status'],
    'rows' => $students->map(fn ($s) => [
        'cells' => [$s->student_id, $s->fullName(), $s->program->program_name ?? '—', $s->year_level, $s->status],
        'editUrl' => route('admin.students.edit', $s),
        'deleteUrl' => route('admin.students.destroy', $s),
    ]),
])

<h3 style="margin-top:20px; color:#65152b;">Add student</h3>
@include('partials._resource_form', [
    'action' => route('admin.students.store'),
    'submitLabel' => 'Create student account',
    'fields' => [
        ['name' => 'student_id', 'label' => 'Student ID (e.g. 2026-0001)', 'required' => true],
        ['name' => 'first_name', 'label' => 'First name', 'required' => true],
        ['name' => 'last_name', 'label' => 'Last name', 'required' => true],
        ['name' => 'email_address', 'label' => 'Email', 'type' => 'email'],
        ['name' => 'program_id', 'label' => 'Program', 'type' => 'select', 'required' => true, 'options' => $programs->pluck('program_name', 'program_id')],
        ['name' => 'year_level', 'label' => 'Year level', 'type' => 'number', 'required' => true, 'value' => 1],
        ['name' => 'initial_password', 'label' => 'Initial password', 'type' => 'password', 'required' => true],
    ],
])
