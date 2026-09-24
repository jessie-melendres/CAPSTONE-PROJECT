<h2>Faculty</h2>
@include('partials._directory_table', [
    'columns' => ['Faculty ID', 'Name', 'Department', 'Status'],
    'rows' => $facultyMembers->map(fn ($f) => [
        'cells' => [$f->faculty_id, $f->fullName(), $f->department->dept_name ?? '—', $f->status],
        'editUrl' => route('admin.faculty.edit', $f),
        'deleteUrl' => route('admin.faculty.destroy', $f),
    ]),
])

<h3 style="margin-top:20px; color:#65152b;">Add faculty</h3>
@include('partials._resource_form', [
    'action' => route('admin.faculty.store'),
    'submitLabel' => 'Create faculty account',
    'fields' => [
        ['name' => 'faculty_id', 'label' => 'Faculty ID (e.g. FAC-004)', 'required' => true],
        ['name' => 'first_name', 'label' => 'First name', 'required' => true],
        ['name' => 'last_name', 'label' => 'Last name', 'required' => true],
        ['name' => 'dept_id', 'label' => 'Department', 'type' => 'select', 'required' => true, 'options' => $departments->pluck('dept_name', 'dept_id')],
        ['name' => 'username', 'label' => 'Login username', 'required' => true],
        ['name' => 'initial_password', 'label' => 'Initial password', 'type' => 'password', 'required' => true],
    ],
])
