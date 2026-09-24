# NCBII Student Portal

A role-based academic information system for North Coast Bohol Institute Incorporated: administrators manage the academic structure, faculty encode grades for classes credited to them, and students get a read-only view of their own records.

## Language

### Scheduling & enrollment

**Prerequisite**:
A Subject that a student must have Passed before they can enroll in another Subject that names it via `prerequisite_subject_id`. A Subject still named as someone else's Prerequisite cannot be deleted. See [ADR-0004](docs/adr/0004-block-deleting-a-subject-still-used-as-a-prerequisite.md).
_Avoid_: —

**Schedule**:
One offered class section for a term — a specific Subject taught by one Faculty member, in one room, on a fixed day/time-slot. Students enroll into a Schedule, not into a Subject directly.
_Avoid_: Class, Section, Timetable (a Timetable is a student's own set of Schedules — see below)

**Timetable**:
A student's own set of enrolled Schedules for a given semester and school year, derived from their active Enrollments.
_Avoid_: Schedule, Class Schedule (reserve "Schedule" for a single offered section)

**Resource conflict**:
Two Schedules that share the same day and an overlapping time window, and also share the same Faculty member or the same room — i.e. a double-booked instructor or room. Checked when an admin creates or edits a Schedule.
_Avoid_: Conflict, Schedule conflict

**Timetable conflict**:
A student's own Enrollments overlapping in time: enrolling in a Schedule that clashes with another Schedule already in that student's Timetable for the same term, regardless of faculty or room. Checked at enrollment time.
_Avoid_: Conflict, Schedule conflict

### Grading

**Final Grade**:
The raw score for the third of three grading periods (Prelim, Midterm, Final) only. On its own it does not decide whether the student passed — see Semestral Average.
_Avoid_: using "final grade" to mean the overall subject outcome

**Semestral Average**:
The mean of the Prelim, Midterm, and Final Grade for one Grade record, rounded to 2 decimals. This is what decides the Remark, and is also what gets averaged across every subject to produce a student's General Average.
_Avoid_: Final Grade, GWA

**Remark**:
The PASSED / FAILED / INCOMPLETE / NO GRADE outcome for a student's Grade in one subject. Derived from the Semestral Average once all three terms are recorded — never from the Final Grade alone. See [ADR-0001](docs/adr/0001-remark-from-semestral-average.md).
_Avoid_: Grade (the Grade record holds the term scores; the Remark is the derived status)

**General Average**:
A student's GWA for a term: the average of the Semestral Average across every subject with a complete Grade. Shown on the student dashboard.
_Avoid_: GWA, Semestral Average (that's per-subject)

### Status & lifecycle

**Student status** (`Active` / `Inactive` / `Graduated`):
An administrator-set academic status that gates whether the student can be newly enrolled — only an `Active` student can be enrolled into a Schedule. Independent of `User.is_active`, which separately gates that student's ability to log in at all.
_Avoid_: conflating with `is_active`, "enabled"

**Faculty status** (`Teaching` / `On leave` / `Inactive`):
An administrator-set status that gates whether a Schedule can be newly assigned to that faculty member — only a `Teaching` faculty member can be assigned a class. Independent of `User.is_active`. Moving a faculty member away from `Teaching` requires every Schedule currently credited to them to be reassigned to a `Teaching` replacement in the same action — see [ADR-0003](docs/adr/0003-faculty-status-change-reassigns-schedules-synchronously.md).
_Avoid_: conflating with `is_active`, "enabled"

**Grade lock**:
Once a Grade's `is_locked` is set (by the owning Faculty member, via `GradeService::lock()`), that Faculty member can no longer modify it through `GradeService::record()`. Only an administrator can reverse this, via `GradeService::unlock()`. See [ADR-0002](docs/adr/0002-admin-can-unlock-a-locked-grade.md).
_Avoid_: treating a lock as permanent
