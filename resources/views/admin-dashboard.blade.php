@extends('layouts.portal')
@section('content')
<header class="header"><div class="container navbar"><a class="brand" href="{{ route('admin.dashboard') }}"><img class="logo" src="{{ route('legacy.image', ['path' => 'ncbii_logo_transparent.png']) }}" alt="NCBII logo"><div class="brand-text"><h2>North Coast Bohol Institute</h2><span>Administrator Portal</span></div></a><nav class="navigation"><a class="active" href="{{ route('admin.dashboard') }}">Dashboard</a><a href="{{ route('admin.management') }}">Management</a></nav><form method="POST" action="{{ route('logout') }}">@csrf<button class="login-btn" type="submit">Sign Out</button></form></div></header>
<main class="staff-main" data-portal="admin"><section class="staff-hero" id="overview"><div class="container staff-heading"><div><span class="welcome-label">ADMINISTRATOR WORKSPACE</span><h1>Institute overview</h1><p>Monitor students, subjects, instructors, programs, and academic grades.</p></div></div></section><section class="container staff-content"><div class="staff-stats">
    <a class="staff-stat" href="{{ route('admin.management') }}?view=students"><span>Total students</span><strong>{{ $studentCount }}</strong><small>Manage student records</small></a>
    <a class="staff-stat" href="{{ route('admin.management') }}?view=subjects"><span>Subjects</span><strong>{{ $subjectCount }}</strong><small>Manage academic offerings</small></a>
    <a class="staff-stat" href="{{ route('admin.management') }}?view=schedules"><span>Class schedules</span><strong>{{ $scheduleCount }}</strong><small>Room / faculty / time slots</small></a>
    <a class="staff-stat" href="{{ route('admin.management') }}?view=faculty"><span>Faculty</span><strong>{{ $facultyCount }}</strong><small>Manage faculty accounts</small></a>
    <a class="staff-stat" href="{{ route('admin.management') }}?view=loads"><span>Active enrollments</span><strong>{{ $enrollmentCount }}</strong><small>Assign classes to students</small></a>
    <a class="staff-stat" href="{{ route('admin.management') }}?view=ledger"><span>Portal ledger</span><strong>&rarr;</strong><small>Record charges &amp; payments</small></a>
</div></section></main>
@endsection
