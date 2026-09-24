@extends('layouts.portal')
@section('content')
<header class="header"><div class="container navbar"><a class="brand" href="{{ route('student.dashboard') }}"><img class="logo" src="{{ route('legacy.image', ['path' => 'ncbii_logo_transparent.png']) }}" alt="NCBII logo"><div class="brand-text"><h2>North Coast Bohol Institute</h2><span>Student Portal</span></div></a><nav class="navigation"><a class="active" href="{{ route('student.dashboard') }}">Dashboard</a><a href="{{ route('student.records') }}">My Records</a></nav><form method="POST" action="{{ route('logout') }}">@csrf<button class="login-btn" type="submit">Sign Out</button></form></div></header>
<main data-portal="student">
<section class="dashboard page-section"><div class="container">
    <div class="dashboard-heading"><span class="welcome-label">STUDENT DASHBOARD</span><h1>Welcome back, {{ $student->first_name }}</h1><p>Here is your current academic overview for {{ $semester }}, {{ $schoolYear }}.</p></div>

    @if (session('status'))
        <p class="login-error" role="status" style="border-left-color:#2f7a3d;color:#2f7a3d;">{{ session('status') }}</p>
    @endif

    <div class="dashboard-grid">
        <article class="dashboard-card dashboard-profile">
            <span>STUDENT PROFILE</span>
            <h2>{{ $student->fullName() }}</h2>
            <p>{{ $student->program->program_name ?? 'No program assigned' }}</p>
            <strong>{{ $schoolYear }} | Year {{ $student->year_level }}</strong>
        </article>
        <article class="dashboard-card">
            <span>ACADEMIC STATUS</span>
            <h2>{{ $student->status }}</h2>
            <p>Your account is ready for the current term.</p>
        </article>
        <article class="dashboard-card">
            <span>REGISTERED SUBJECTS</span>
            <h2>{{ $enrollments->count() }} Subjects</h2>
            <p>{{ $units }} units this semester</p>
        </article>
    </div>

    <div class="dashboard-panels">
        <details id="records" open>
            <summary>Academic Records <span>View grades</span></summary>
            <div class="panel-content">
                <p>General average</p>
                <strong>{{ $generalAverage ?? 'N/A' }}</strong>
                <p>Based on recorded final grades ({{ 1 }}.00 highest, {{ 5 }}.00 lowest; {{ number_format(\App\Models\Grade::PASSING_THRESHOLD, 2) }} or below passes)</p>
            </div>
        </details>
        <details id="schedule">
            <summary>Class Schedule <span>{{ $enrollments->count() }} classes</span></summary>
            <div class="panel-content">
                @forelse ($enrollments as $enrollment)
                    <p>{{ $enrollment->schedule->day }}, {{ $enrollment->schedule->start_time }}&ndash;{{ $enrollment->schedule->end_time }} &mdash; {{ $enrollment->schedule->subject->subject_name }} | {{ $enrollment->schedule->room_assignment }} | {{ $enrollment->schedule->faculty->fullName() }}</p>
                @empty
                    <p>No classes enrolled yet for this term.</p>
                @endforelse
            </div>
        </details>
        <details id="tuition">
            <summary>Tuition Payment <span>Balance: PHP {{ number_format($balance, 2) }}</span></summary>
            <div class="panel-content"><strong>PHP {{ number_format($balance, 2) }}</strong><p><a href="{{ route('student.records') }}">View full ledger &amp; grade history &rarr;</a></p></div>
        </details>
        <details id="announcements">
            <summary>Announcements <span>{{ $announcements->count() }} recent</span></summary>
            <div class="panel-content">
                @forelse ($announcements as $announcement)
                    <p><strong>{{ $announcement->title ?? 'Announcement' }}</strong><br>{{ $announcement->content }}<br><small>{{ $announcement->date_posted->format('M d, Y') }}</small></p>
                @empty
                    <p>No announcements posted yet.</p>
                @endforelse
            </div>
        </details>
    </div>
</div></section>
</main>
@endsection
