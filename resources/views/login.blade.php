@extends('layouts.portal')

@section('content')
<main class="login-page">
    <div class="login-layout">
        <div class="login-brand-panel">
            <img class="login-watermark" src="{{ route('legacy.image', ['path' => 'ncbii_logo_transparent.png']) }}" alt="">
            <div class="brand login-brand">
                <img class="logo" src="{{ route('legacy.image', ['path' => 'ncbii_logo_transparent.png']) }}" alt="NCBII logo">
                <div class="brand-text"><h2>North Coast Bohol Institute</h2><span>Student Portal</span></div>
            </div>
            <div class="login-intro"><span class="welcome-label">NCBII ACADEMIC INFORMATION SYSTEM</span><h1>Your academic journey starts here.</h1><p>Sign in to access your academic dashboard.</p></div>
        </div>
        <section class="login-panel" aria-labelledby="login-title">
            <div class="login-form-wrap">
                <span class="welcome-label">PORTAL ACCESS</span><h1 id="login-title">Welcome back</h1>
                @if ($errors->any()) <p class="login-error" role="alert">{{ $errors->first() }}</p> @endif
                <form method="POST" action="{{ route('login.authenticate') }}">
                    @csrf
                    <span class="login-form-label">Access type</span>
                    <div class="access-tabs" role="tablist" aria-label="Access type">
                        <button class="access-tab active" type="button" role="tab" aria-selected="true" data-access="student">Student</button>
                        <button class="access-tab" type="button" role="tab" aria-selected="false" data-access="staff">Faculty / Administrator</button>
                    </div>
                    <input id="access_type" name="access_type" type="hidden" value="student">
                    <div id="studentFields"><label for="student_id">Student ID Number</label><input id="student_id" name="student_id" type="text" placeholder="e.g. 2026-0001"><label for="student_password">Password</label><input id="student_password" name="student_password" type="password"></div>
                    <div id="staffFields" hidden><label for="staff_email">Username or staff email</label><input id="staff_email" name="staff_email" type="text"><label for="staff_password">Password</label><input id="staff_password" name="staff_password" type="password"></div>
                    <button class="primary-btn login-submit" type="submit">Continue to Dashboard</button>
                </form>
            </div>
        </section>
    </div>
</main>
@endsection
