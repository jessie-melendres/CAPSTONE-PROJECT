<h2>Programs</h2>
@include('partials._directory_table', [
    'columns' => ['Code', 'Name', 'Department'],
    'rows' => $programs->map(fn ($p) => [
        'cells' => [$p->program_id, $p->program_name, $p->department->dept_name ?? '—'],
        'deleteUrl' => route('admin.programs.destroy', $p),
    ]),
])

<h3 style="margin-top:20px; color:#65152b;">Add program</h3>
@include('partials._resource_form', [
    'action' => route('admin.programs.store'),
    'submitLabel' => 'Add program',
    'fields' => [
        ['name' => 'program_id', 'label' => 'Code (e.g. BSIT)', 'required' => true],
        ['name' => 'program_name', 'label' => 'Name', 'required' => true],
        ['name' => 'dept_id', 'label' => 'Department', 'type' => 'select', 'required' => true, 'options' => $departments->pluck('dept_name', 'dept_id')],
    ],
])
