<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Http\Request;

class StudentPortalController extends Controller
{
    public function records(Request $request, LedgerService $ledger)
    {
        $student = User::findOrFail($request->session()->get('user.user_id'))
            ->student()
            ->with('program.department')
            ->firstOrFail();

        $enrollments = $student->enrollments()
            ->with(['schedule.subject', 'schedule.faculty', 'grade'])
            ->orderByDesc('school_year')
            ->orderByDesc('semester')
            ->get()
            ->groupBy(fn ($enrollment) => "{$enrollment->semester} {$enrollment->school_year}");

        return view('student.records', [
            'student' => $student,
            'enrollmentsByTerm' => $enrollments,
            'ledgerEntries' => $student->ledgerEntries()->latest('entry_date')->get(),
            'balance' => $ledger->balance($student),
        ]);
    }
}
