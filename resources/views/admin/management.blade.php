@extends('layouts.portal')
@section('content')
<header class="header"><div class="container navbar"><a class="brand" href="{{ route('admin.dashboard') }}"><img class="logo" src="{{ route('legacy.image', ['path' => 'ncbii_logo_transparent.png']) }}" alt="NCBII logo"><div class="brand-text"><h2>North Coast Bohol Institute</h2><span>Administrator Portal</span></div></a><nav class="navigation"><a href="{{ route('admin.dashboard') }}">Dashboard</a><a class="active" href="{{ route('admin.management') }}">Management</a></nav><form method="POST" action="{{ route('logout') }}">@csrf<button class="login-btn" type="submit">Sign Out</button></form></div></header>
<main class="staff-main admin-window">
<section class="staff-hero"><div class="container staff-heading"><div><span class="welcome-label">RECORD MANAGEMENT</span><h1>Administrator management</h1><p>Create, review, and maintain academic records.</p></div></div></section>
<section class="container staff-content">
    <a class="admin-back-button" href="{{ route('admin.dashboard') }}">&larr; Dashboard</a>

    @if (session('status'))
        <p class="login-error" role="status" style="border-left-color:#2f7a3d;color:#2f7a3d;">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <p class="login-error" role="alert">{{ $errors->first() }}</p>
    @endif

    @php
        $tabs = [
            'students' => 'Students',
            'faculty' => 'Faculty',
            'departments' => 'Departments',
            'programs' => 'Programs',
            'subjects' => 'Subjects',
            'schedules' => 'Schedules',
            'loads' => 'Subject Loads',
            'ledger' => 'Portal Ledger',
            'announcements' => 'Announcements',
        ];
    @endphp
    <div class="access-tabs" role="tablist" aria-label="Management section" style="margin-bottom:20px; flex-wrap:wrap;">
        @foreach ($tabs as $key => $label)
            <a class="access-tab {{ $activeView === $key ? 'active' : '' }}" href="{{ route('admin.management') }}?view={{ $key }}">{{ $label }}</a>
        @endforeach
    </div>

    @include('admin.partials.'.$activeView)
</section>
</main>
@endsection
