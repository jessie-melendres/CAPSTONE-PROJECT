@extends('layouts.portal')
@section('content')
<header class="header"><div class="container navbar"><a class="brand" href="{{ route('faculty.dashboard') }}"><img class="logo" src="{{ route('legacy.image', ['path' => 'ncbii_logo_transparent.png']) }}" alt="NCBII logo"><div class="brand-text"><h2>North Coast Bohol Institute</h2><span>Faculty Portal</span></div></a><nav class="navigation"><a href="{{ route('faculty.dashboard') }}">Dashboard</a></nav><form method="POST" action="{{ route('logout') }}">@csrf<button class="login-btn" type="submit">Sign Out</button></form></div></header>
<main class="staff-main">
<section class="staff-hero"><div class="container staff-heading"><div>
    <span class="welcome-label">GRADE ENCODING</span>
    <h1>{{ $schedule->subject->subject_name }}</h1>
    <p>{{ $schedule->subject->subject_id }} &middot; {{ $schedule->label() }} &middot; {{ $semester }}, {{ $schoolYear }}</p>
</div></div></section>
<section class="container staff-content">
    <a class="grade-back-button" href="{{ route('faculty.dashboard') }}">&larr; Back to dashboard</a>

    @if (session('status'))
        <p class="login-error" role="status" style="border-left-color:#2f7a3d;color:#2f7a3d;">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <p class="login-error" role="alert">{{ $errors->first() }}</p>
    @endif

    <div class="directory-table-wrap">
        <table class="directory-table">
            <thead>
                <tr><th>Student</th><th>Prelim</th><th>Midterm</th><th>Final</th><th>Remark</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($enrollments as $enrollment)
                    @php($grade = $enrollment->grade)
                    <tr>
                        <td><strong>{{ $enrollment->student->fullName() }}</strong><small>{{ $enrollment->student->student_id }}</small></td>
                        <td colspan="3">
                            <form method="POST" action="{{ route('faculty.grades.store', $enrollment) }}" style="display:flex; gap:8px; align-items:center;">
                                @csrf
                                <input class="grade-input" type="number" step="0.01" min="1" max="5" name="prelim_grade" value="{{ $grade->prelim_grade ?? '' }}" placeholder="Prelim" @disabled($grade?->is_locked)>
                                <input class="grade-input" type="number" step="0.01" min="1" max="5" name="midterm_grade" value="{{ $grade->midterm_grade ?? '' }}" placeholder="Midterm" @disabled($grade?->is_locked)>
                                <input class="grade-input" type="number" step="0.01" min="1" max="5" name="final_grade" value="{{ $grade->final_grade ?? '' }}" placeholder="Final" @disabled($grade?->is_locked)>
                                @unless ($grade?->is_locked)
                                    <button type="submit">Save</button>
                                @endunless
                            </form>
                        </td>
                        <td><b class="grade {{ $grade?->remarks === 'PASSED' ? 'good' : 'pending' }}">{{ $grade->remarks ?? 'NG' }}</b></td>
                        <td class="table-actions">
                            @if ($grade && ! $grade->is_locked)
                                <form method="POST" action="{{ route('faculty.grades.lock', $enrollment) }}" onsubmit="return confirm('Locking prevents further edits to this grade. Continue?');">
                                    @csrf
                                    <button type="submit">Lock</button>
                                </form>
                            @elseif ($grade?->is_locked)
                                <span>Locked {{ $grade->locked_at?->format('M d, Y') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No students enrolled in this class yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
</main>
@endsection
