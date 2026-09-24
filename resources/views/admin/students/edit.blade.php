@extends('layouts.portal')
@section('content')
<header class="header"><div class="container navbar"><a class="brand" href="{{ route('admin.dashboard') }}"><img class="logo" src="{{ route('legacy.image', ['path' => 'ncbii_logo_transparent.png']) }}" alt="NCBII logo"><div class="brand-text"><h2>North Coast Bohol Institute</h2><span>Administrator Portal</span></div></a><nav class="navigation"><a href="{{ route('admin.dashboard') }}">Dashboard</a><a class="active" href="{{ route('admin.management') }}">Management</a></nav><form method="POST" action="{{ route('logout') }}">@csrf<button class="login-btn" type="submit">Sign Out</button></form></div></header>
<main class="staff-main">
<section class="staff-hero"><div class="container staff-heading"><div><span class="welcome-label">EDIT STUDENT</span><h1>{{ $student->fullName() }}</h1><p>{{ $student->student_id }}</p></div></div></section>
<section class="container staff-content">
    <a class="admin-back-button" href="{{ route('admin.management') }}?view=students">&larr; Back to students</a>
    @if ($errors->any())
        <p class="login-error" role="alert">{{ $errors->first() }}</p>
    @endif
    @include('partials._resource_form', [
        'action' => route('admin.students.update', $student),
        'method' => 'PUT',
        'submitLabel' => 'Save changes',
        'fields' => [
            ['name' => 'first_name', 'label' => 'First name', 'required' => true, 'value' => $student->first_name],
            ['name' => 'last_name', 'label' => 'Last name', 'required' => true, 'value' => $student->last_name],
            ['name' => 'email_address', 'label' => 'Email', 'type' => 'email', 'value' => $student->email_address],
            ['name' => 'program_id', 'label' => 'Program', 'type' => 'select', 'required' => true, 'value' => $student->program_id, 'options' => $programs->pluck('program_name', 'program_id')],
            ['name' => 'year_level', 'label' => 'Year level', 'type' => 'number', 'required' => true, 'value' => $student->year_level],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'value' => $student->status, 'options' => ['Active' => 'Active', 'Inactive' => 'Inactive', 'Graduated' => 'Graduated']],
        ],
    ])
</section>
</main>
@endsection
