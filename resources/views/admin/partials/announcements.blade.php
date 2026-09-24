<h2>Announcements</h2>
@include('partials._directory_table', [
    'columns' => ['Posted', 'Title', 'Audience', 'Author'],
    'rows' => $announcements->map(fn ($a) => [
        'cells' => [$a->date_posted->format('M d, Y'), $a->title ?? '(untitled)', $a->target_audience, $a->author->username ?? '—'],
        'deleteUrl' => route('admin.announcements.destroy', $a),
    ]),
])

<h3 style="margin-top:20px; color:#65152b;">Post announcement</h3>
@include('partials._resource_form', [
    'action' => route('admin.announcements.store'),
    'submitLabel' => 'Post announcement',
    'fields' => [
        ['name' => 'title', 'label' => 'Title'],
        ['name' => 'content', 'label' => 'Message', 'type' => 'textarea', 'required' => true],
        ['name' => 'target_audience', 'label' => 'Audience', 'type' => 'select', 'required' => true, 'options' => ['All' => 'All', 'Students' => 'Students only', 'Faculty' => 'Faculty only']],
    ],
])
