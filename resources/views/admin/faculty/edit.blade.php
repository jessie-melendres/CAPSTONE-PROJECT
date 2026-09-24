@extends('layouts.portal')
@section('content')
<header class="header"><div class="container navbar"><a class="brand" href="{{ route('admin.dashboard') }}"><img class="logo" src="{{ route('legacy.image', ['path' => 'ncbii_logo_transparent.png']) }}" alt="NCBII logo"><div class="brand-text"><h2>North Coast Bohol Institute</h2><span>Administrator Portal</span></div></a><nav class="navigation"><a href="{{ route('admin.dashboard') }}">Dashboard</a><a class="active" href="{{ route('admin.management') }}">Management</a></nav><form method="POST" action="{{ route('logout') }}">@csrf<button class="login-btn" type="submit">Sign Out</button></form></div></header>
<main class="staff-main">
<section class="staff-hero"><div class="container staff-heading"><div><span class="welcome-label">EDIT FACULTY</span><h1>{{ $facultyMember->fullName() }}</h1><p>{{ $facultyMember->faculty_id }}</p></div></div></section>
<section class="container staff-content">
    <a class="admin-back-button" href="{{ route('admin.management') }}?view=faculty">&larr; Back to faculty</a>
    @if ($errors->any())
        <p class="login-error" role="alert">{{ $errors->first() }}</p>
    @endif
    @include('partials._resource_form', [
        'action' => route('admin.faculty.update', $facultyMember),
        'method' => 'PUT',
        'submitLabel' => 'Save changes',
        'fields' => [
            ['name' => 'first_name', 'label' => 'First name', 'required' => true, 'value' => $facultyMember->first_name],
            ['name' => 'last_name', 'label' => 'Last name', 'required' => true, 'value' => $facultyMember->last_name],
            ['name' => 'dept_id', 'label' => 'Department', 'type' => 'select', 'required' => true, 'value' => $facultyMember->dept_id, 'options' => $departments->pluck('dept_name', 'dept_id')],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'value' => $facultyMember->status, 'options' => ['Teaching' => 'Teaching', 'On leave' => 'On leave', 'Inactive' => 'Inactive']],
        ],
    ])
</section>
</main>
@endsection
