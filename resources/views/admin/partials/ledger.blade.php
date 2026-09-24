<h2>Portal ledger</h2>
<p>Manual charges and payments only — there is no live payment gateway. Students see a read-only view of their own balance. Showing the 50 most recent entries.</p>
@include('partials._directory_table', [
    'columns' => ['Date', 'Student', 'Description', 'Type', 'Amount'],
    'rows' => $ledgerEntries->map(fn ($e) => [
        'cells' => [$e->entry_date->format('M d, Y'), $e->student->fullName() ?? '—', $e->description, $e->entry_type, 'PHP '.number_format($e->amount, 2)],
    ]),
])

<h3 style="margin-top:20px; color:#65152b;">Record charge or payment</h3>
@include('partials._resource_form', [
    'action' => route('admin.ledger.store'),
    'submitLabel' => 'Record entry',
    'fields' => [
        ['name' => 'student_id', 'label' => 'Student', 'type' => 'select', 'required' => true, 'options' => $students->mapWithKeys(fn ($s) => [$s->student_id => "{$s->fullName()} ({$s->student_id})"])],
        ['name' => 'entry_type', 'label' => 'Type', 'type' => 'select', 'required' => true, 'options' => ['CHARGE' => 'Charge (e.g. tuition fee)', 'PAYMENT' => 'Payment (cash received)']],
        ['name' => 'amount', 'label' => 'Amount (PHP)', 'type' => 'number', 'step' => '0.01', 'required' => true],
        ['name' => 'description', 'label' => 'Description', 'required' => true],
        ['name' => 'entry_date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'value' => now()->toDateString()],
    ],
])
