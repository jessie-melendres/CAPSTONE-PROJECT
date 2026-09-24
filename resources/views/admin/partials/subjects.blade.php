<h2>Subjects (course offerings)</h2>
@include('partials._directory_table', [
    'columns' => ['Code', 'Name', 'Units', 'Prerequisite'],
    'rows' => $subjects->map(fn ($s) => [
        'cells' => [$s->subject_id, $s->subject_name, $s->units, $s->prerequisite->subject_name ?? '—'],
        'deleteUrl' => route('admin.subjects.destroy', $s),
    ]),
])

<h3 style="margin-top:20px; color:#65152b;">Add subject</h3>
@include('partials._resource_form', [
    'action' => route('admin.subjects.store'),
    'submitLabel' => 'Add subject',
    'fields' => [
        ['name' => 'subject_id', 'label' => 'Code (e.g. IT104)', 'required' => true],
        ['name' => 'subject_name', 'label' => 'Name', 'required' => true],
        ['name' => 'units', 'label' => 'Units', 'type' => 'number', 'required' => true, 'value' => 3],
        ['name' => 'prerequisite_subject_id', 'label' => 'Prerequisite (optional)', 'type' => 'select', 'options' => $subjects->pluck('subject_name', 'subject_id')],
    ],
])
