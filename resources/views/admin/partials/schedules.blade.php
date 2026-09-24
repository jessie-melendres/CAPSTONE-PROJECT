<h2>Class schedules</h2>
<p>Conflict-checked: the same room or the same instructor cannot be double-booked on an overlapping day/time.</p>
@include('partials._directory_table', [
    'columns' => ['Subject', 'Faculty', 'Day', 'Time', 'Room'],
    'rows' => $schedules->map(fn ($s) => [
        'cells' => [$s->subject->subject_name ?? '—', $s->faculty->fullName() ?? '—', $s->day, "{$s->start_time}–{$s->end_time}", $s->room_assignment],
        'deleteUrl' => route('admin.schedules.destroy', $s),
    ]),
])

<h3 style="margin-top:20px; color:#65152b;">Add class schedule</h3>
@include('partials._resource_form', [
    'action' => route('admin.schedules.store'),
    'submitLabel' => 'Add schedule',
    'fields' => [
        ['name' => 'subject_id', 'label' => 'Subject', 'type' => 'select', 'required' => true, 'options' => $subjects->pluck('subject_name', 'subject_id')],
        ['name' => 'faculty_id', 'label' => 'Faculty', 'type' => 'select', 'required' => true, 'options' => $facultyMembers->mapWithKeys(fn ($f) => [$f->faculty_id => $f->fullName()])],
        ['name' => 'room_assignment', 'label' => 'Room', 'required' => true],
        ['name' => 'day', 'label' => 'Day', 'type' => 'select', 'required' => true, 'options' => ['Monday' => 'Monday', 'Tuesday' => 'Tuesday', 'Wednesday' => 'Wednesday', 'Thursday' => 'Thursday', 'Friday' => 'Friday', 'Saturday' => 'Saturday']],
        ['name' => 'start_time', 'label' => 'Start time', 'type' => 'time', 'required' => true],
        ['name' => 'end_time', 'label' => 'End time', 'type' => 'time', 'required' => true],
    ],
])
