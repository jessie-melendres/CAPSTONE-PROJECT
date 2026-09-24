<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Faculty\GradeController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\Student\StudentPortalController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortalController::class, 'home'])->name('home');
Route::get('/login', [PortalController::class, 'login'])->name('login');
Route::post('/login', [PortalController::class, 'authenticate'])->middleware('throttle:6,1')->name('login.authenticate');
Route::post('/logout', [PortalController::class, 'logout'])->name('logout');

Route::middleware('role:student')->group(function () {
    Route::get('/dashboard', [PortalController::class, 'studentDashboard'])->name('student.dashboard');
    Route::get('/student/records', [StudentPortalController::class, 'records'])->name('student.records');
});

Route::middleware('role:faculty')->group(function () {
    Route::get('/faculty', [PortalController::class, 'facultyDashboard'])->name('faculty.dashboard');
    Route::view('/faculty/about', 'legacy-page', ['portal' => 'Faculty Portal', 'heading' => 'Faculty portal information', 'description' => 'A focused workspace for teaching responsibilities.'])->name('faculty.about');

    Route::get('/faculty/grades/{schedule}', [GradeController::class, 'show'])->name('faculty.grades');
    Route::post('/faculty/grades/enrollment/{enrollment}', [GradeController::class, 'store'])->name('faculty.grades.store');
    Route::post('/faculty/grades/enrollment/{enrollment}/lock', [GradeController::class, 'lock'])->name('faculty.grades.lock');
    Route::post('/faculty/announcements', [GradeController::class, 'announcementStore'])->name('faculty.announcements.store');
});

Route::middleware('role:admin')->group(function () {
    Route::get('/administrator', [PortalController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::view('/administrator/statistics', 'legacy-page', ['portal' => 'Administrator Portal', 'heading' => 'Institute statistics', 'description' => 'Select a directory to review records and current status.'])->name('admin.statistics');

    Route::get('/administrator/management', [AdminController::class, 'management'])->name('admin.management');

    Route::post('/administrator/departments', [AdminController::class, 'departmentStore'])->name('admin.departments.store');
    Route::delete('/administrator/departments/{department}', [AdminController::class, 'departmentDestroy'])->name('admin.departments.destroy');

    Route::post('/administrator/programs', [AdminController::class, 'programStore'])->name('admin.programs.store');
    Route::delete('/administrator/programs/{program}', [AdminController::class, 'programDestroy'])->name('admin.programs.destroy');

    Route::post('/administrator/students', [AdminController::class, 'studentStore'])->name('admin.students.store');
    Route::get('/administrator/students/{student}/edit', [AdminController::class, 'studentEdit'])->name('admin.students.edit');
    Route::put('/administrator/students/{student}', [AdminController::class, 'studentUpdate'])->name('admin.students.update');
    Route::delete('/administrator/students/{student}', [AdminController::class, 'studentDestroy'])->name('admin.students.destroy');

    Route::post('/administrator/faculty', [AdminController::class, 'facultyStore'])->name('admin.faculty.store');
    Route::get('/administrator/faculty/{facultyMember}/edit', [AdminController::class, 'facultyEdit'])->name('admin.faculty.edit');
    Route::put('/administrator/faculty/{facultyMember}', [AdminController::class, 'facultyUpdate'])->name('admin.faculty.update');
    Route::delete('/administrator/faculty/{facultyMember}', [AdminController::class, 'facultyDestroy'])->name('admin.faculty.destroy');

    Route::post('/administrator/subjects', [AdminController::class, 'subjectStore'])->name('admin.subjects.store');
    Route::put('/administrator/subjects/{subject}', [AdminController::class, 'subjectUpdate'])->name('admin.subjects.update');
    Route::delete('/administrator/subjects/{subject}', [AdminController::class, 'subjectDestroy'])->name('admin.subjects.destroy');

    Route::post('/administrator/schedules', [AdminController::class, 'scheduleStore'])->name('admin.schedules.store');
    Route::put('/administrator/schedules/{schedule}', [AdminController::class, 'scheduleUpdate'])->name('admin.schedules.update');
    Route::delete('/administrator/schedules/{schedule}', [AdminController::class, 'scheduleDestroy'])->name('admin.schedules.destroy');

    Route::post('/administrator/enrollments', [AdminController::class, 'enrollmentStore'])->name('admin.enrollments.store');
    Route::delete('/administrator/enrollments/{enrollment}', [AdminController::class, 'enrollmentDestroy'])->name('admin.enrollments.destroy');

    Route::post('/administrator/grades/enrollment/{enrollment}/unlock', [AdminController::class, 'gradeUnlock'])->name('admin.grades.unlock');

    Route::post('/administrator/ledger', [AdminController::class, 'ledgerStore'])->name('admin.ledger.store');

    Route::post('/administrator/announcements', [AdminController::class, 'announcementStore'])->name('admin.announcements.store');
    Route::delete('/administrator/announcements/{announcement}', [AdminController::class, 'announcementDestroy'])->name('admin.announcements.destroy');
});

Route::middleware('role:faculty,admin')->group(function () {
    Route::get('/staff', function () {
        return redirect()->route(session()->get('user.role') === 'admin' ? 'admin.dashboard' : 'faculty.dashboard');
    })->name('staff.portal');
});

Route::view('/about', 'about')->name('about');
Route::view('/features', 'features')->name('features');

// Keep the existing visual assets working while the static files are migrated.
Route::get('/legacy/style.css', function () {
    return Response::file(base_path('style.css'), ['Content-Type' => 'text/css']);
})->name('legacy.style');

Route::get('/legacy/images/{path}', function (string $path) {
    $file = base_path('images/'.str_replace('..', '', $path));
    abort_unless(is_file($file), 404);

    return Response::file($file);
})->where('path', '.*')->name('legacy.image');
