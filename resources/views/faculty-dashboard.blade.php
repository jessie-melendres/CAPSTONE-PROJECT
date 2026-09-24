@extends('layouts.portal')
@section('content')
<header class="header"><div class="container navbar"><a class="brand" href="{{ route('faculty.dashboard') }}"><img class="logo" src="{{ route('legacy.image', ['path' => 'ncbii_logo_transparent.png']) }}" alt="NCBII logo"><div class="brand-text"><h2>North Coast Bohol Institute</h2><span>Faculty Portal</span></div></a><nav class="navigation"><a class="active" href="{{ route('faculty.dashboard') }}">Dashboard</a><a href="{{ route('about') }}">About</a></nav><form method="POST" action="{{ route('logout') }}">@csrf<button class="login-btn" type="submit">Sign Out</button></form></div></header>
<main class="staff-main" data-portal="faculty">
<section class="staff-hero"><div class="container staff-heading"><div><span class="welcome-label">FACULTY WORKSPACE</span><h1>My teaching dashboard</h1><p>Manage students and grades only for subjects credited to you &mdash; {{ $semester }}, {{ $schoolYear }}.</p></div></div></section>
<section class="container staff-content">
    @if (session('status'))
        <p class="login-error" role="status" style="border-left-color:#2f7a3d;color:#2f7a3d;">{{ session('status') }}</p>
    @endif
    <div class="staff-toolbar faculty-toolbar"><div><span class="welcome-label">CREDITED SUBJECTS</span><h2>My subjects and students</h2></div></div>
    <div class="access-note"><strong>Restricted faculty access.</strong><span>Only subjects credited to your account are shown.</span></div>
    <div class="subject-grid" data-assigned-subjects>
        @forelse ($schedules as $schedule)
            <a class="subject-card credited" href="{{ route('faculty.grades', $schedule) }}">
                <div>
                    <span class="subject-status">CREDITED SUBJECT</span>
                    <h3>{{ $schedule->subject->subject_name }}</h3>
                    <p>{{ $schedule->subject->subject_id }} &middot; {{ $schedule->enrollments->count() }} students &middot; {{ $schedule->label() }}</p>
                </div>
                <span class="subject-card-link">View student list</span>
            </a>
        @empty
            <p>No classes credited to your account yet this term.</p>
        @endforelse
    </div>

    <div class="staff-toolbar faculty-toolbar" style="margin-top:32px;"><div><span class="welcome-label">COMMUNICATIONS</span><h2>Announcements</h2></div></div>
    <details id="announcements" open>
        <summary>Recent announcements <span>{{ $announcements->count() }}</span></summary>
        <div class="panel-content">
            @forelse ($announcements as $announcement)
                <p><strong>{{ $announcement->title ?? 'Announcement' }}</strong><br>{{ $announcement->content }}<br><small>{{ $announcement->date_posted->format('M d, Y') }}</small></p>
            @empty
                <p>No announcements posted yet.</p>
            @endforelse
        </div>
    </details>

    <div class="staff-toolbar faculty-toolbar" style="margin-top:32px;"><div><span class="welcome-label">COMMUNICATIONS</span><h2>Post an announcement</h2></div></div>
    @if ($errors->any())
        <p class="login-error" role="alert">{{ $errors->first() }}</p>
    @endif
    @include('partials._resource_form', [
        'action' => route('faculty.announcements.store'),
        'submitLabel' => 'Post announcement',
        'fields' => [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
            ['name' => 'content', 'label' => 'Message', 'type' => 'textarea', 'required' => true],
            ['name' => 'target_audience', 'label' => 'Audience', 'type' => 'select', 'required' => true, 'options' => ['All' => 'All (Students & Faculty)', 'Students' => 'My Students Only']],
        ],
    ])
</section>
</main>
@endsection
