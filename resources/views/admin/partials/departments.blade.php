<h2>Departments</h2>
@include('partials._directory_table', [
    'columns' => ['Code', 'Name'],
    'rows' => $departments->map(fn ($d) => [
        'cells' => [$d->dept_id, $d->dept_name],
        'deleteUrl' => route('admin.departments.destroy', $d),
    ]),
])

<h3 style="margin-top:20px; color:#65152b;">Add department</h3>
@include('partials._resource_form', [
    'action' => route('admin.departments.store'),
    'submitLabel' => 'Add department',
    'fields' => [
        ['name' => 'dept_id', 'label' => 'Code (e.g. CS)', 'required' => true],
        ['name' => 'dept_name', 'label' => 'Name', 'required' => true],
    ],
])
