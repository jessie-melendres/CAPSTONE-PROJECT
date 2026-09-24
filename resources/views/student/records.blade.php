@extends('layouts.portal')
@section('content')
<header class="header"><div class="container navbar"><a class="brand" href="{{ route('student.dashboard') }}"><img class="logo" src="{{ route('legacy.image', ['path' => 'ncbii_logo_transparent.png']) }}" alt="NCBII logo"><div class="brand-text"><h2>North Coast Bohol Institute</h2><span>Student Portal</span></div></a><nav class="navigation"><a href="{{ route('student.dashboard') }}">Dashboard</a><a class="active" href="{{ route('student.records') }}">My Records</a></nav><form method="POST" action="{{ route('logout') }}">@csrf<button class="login-btn" type="submit">Sign Out</button></form></div></header>
<main class="staff-main">
<section class="staff-hero"><div class="container staff-heading"><div><span class="welcome-label">READ-ONLY</span><h1>My records</h1><p>Academic history and tuition ledger for {{ $student->fullName() }}.</p></div></div></section>
<section class="container staff-content">
    <h2>Grades by term</h2>
    @forelse ($enrollmentsByTerm as $term => $enrollments)
        <h3 style="color:#65152b; margin-top:24px;">{{ $term }}</h3>
        <div class="directory-table-wrap">
            <table class="directory-table">
                <thead><tr><th>Subject</th><th>Schedule</th><th>Prelim</th><th>Midterm</th><th>Final</th><th>Remark</th></tr></thead>
                <tbody>
                    @foreach ($enrollments as $enrollment)
                        <tr>
                            <td><strong>{{ $enrollment->schedule->subject->subject_name }}</strong><small>{{ $enrollment->schedule->subject->subject_id }}</small></td>
                            <td>{{ $enrollment->schedule->label() }}</td>
                            <td>{{ $enrollment->grade->prelim_grade ?? '—' }}</td>
                            <td>{{ $enrollment->grade->midterm_grade ?? '—' }}</td>
                            <td>{{ $enrollment->grade->final_grade ?? '—' }}</td>
                            <td><b class="grade {{ $enrollment->grade?->remarks === 'PASSED' ? 'good' : 'pending' }}">{{ $enrollment->grade->remarks ?? 'NG' }}</b></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <p>No enrollment history yet.</p>
    @endforelse

    <h2 style="margin-top:40px;">Portal ledger</h2>
    <div class="access-note"><strong>Current balance: PHP {{ number_format($balance, 2) }}</strong><span>Read-only &mdash; updated manually by the registrar/clerk upon cash payment.</span></div>
    <div class="directory-table-wrap">
        <table class="directory-table">
            <thead><tr><th>Date</th><th>Description</th><th>Type</th><th>Amount</th></tr></thead>
            <tbody>
                @forelse ($ledgerEntries as $entry)
                    <tr>
                        <td>{{ $entry->entry_date->format('M d, Y') }}</td>
                        <td>{{ $entry->description }}</td>
                        <td>{{ $entry->entry_type }}</td>
                        <td>PHP {{ number_format($entry->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No ledger entries recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
</main>
@endsection
