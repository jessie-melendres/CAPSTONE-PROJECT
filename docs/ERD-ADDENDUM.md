# ERD / Data Dictionary / Gantt Addendum

This document is the companion to Chapter 3 of *"Development of a Web-Based Student Portal of North Coast Bohol Institute Incorporated"* (the capstone proposal). It lists every place the shipped implementation extends, corrects, or makes an assumption beyond what's in the paper's Data Dictionary, ERD, or Gantt chart — so the defense document and the running code stay traceable to each other.

Column names below use the paper's own snake_case convention (`student_id`, `dept_id`, ...); the implementation matches this exactly for every field the paper specifies. Everything in this document is *in addition to* the paper's schema, not a replacement of it.

## Gantt chart correction

**Sprint 0 ("Environment Setup & Core Logic") lists `.NET/Angular/SQL Server/n8n`.** This contradicts the paper's own Chapter 3 body text ("System Construction: ... programmed using the PHP Laravel framework and MySQL") and the tech stack used everywhere else in the document. The implementation uses **PHP 8/Laravel 9 + MySQL**, matching the majority of the document. Treat the Sprint 0 Gantt row as a documentation error; if presenting the Gantt chart at defense, correct that row to read "PHP/Laravel, MySQL, XAMPP, VS Code" to match Chapter 3 and Figure 4.0 (System Architecture Matrix).

## Schema extensions

Each of these fills a gap between what the paper's Data Dictionary defines and what a named Gantt-roadmap deliverable or Objective actually requires to function. None of them contradict the paper's existing tables — they're additive.

### 1. Portal Ledger table (`ledger_entries`)

**Why:** Objective 4 and the Significance section both require a "Portal Ledger Module" with admin-entered charges/payments and a read-only student balance view — but no such table exists anywhere in the ERD (Figure 4.3) or Data Dictionary (Tables 3.1–3.10).

**Fields:** `ledger_entry_id` (PK), `student_id` (FK), `description`, `entry_type` (`CHARGE`|`PAYMENT`), `amount`, `recorded_by_user_id` (FK → `users`, always an admin/clerk — there is no live payment gateway per the paper's own Limitations), `entry_date`.

The student's balance is **computed**, not stored: `SUM(CHARGE) − SUM(PAYMENT)` over their entries (see `Student::ledgerBalance()`). This keeps every entry an auditable, append-only record rather than a single mutable balance column.

### 2. Structured class scheduling (`schedules.day` / `start_time` / `end_time` instead of one `time_slot` string)

**Why:** the Gantt roadmap names "Automated Scheduling Conflict Detection (Room Allocation & Faculty Loading Flags)" as a deliverable. Detecting an overlapping room or instructor booking against a single freeform `time_slot` VARCHAR is not reliably possible; it needs comparable start/end values. `schedules.room_assignment` is kept as named in the paper.

`ScheduleService::hasConflict()` rejects any new/edited schedule where another schedule shares the same `day` and an overlapping `[start_time, end_time)` window *and* shares either the same `faculty_id` or the same `room_assignment`.

### 3. Subject self-referential prerequisite (`subjects.prerequisite_subject_id`)

**Why:** the Gantt roadmap names "Self-Referential Prerequisite Verification Rules Engine Layer." The paper's Subjects table (3.6) has no prerequisite field. The pre-existing UI prototype (`mock-data.js`) already modeled this informally as a plain subject-code string, which this formalizes as a nullable foreign key back onto `subjects.subject_id`.

`EnrollmentService::enroll()` rejects enrollment into a subject whose prerequisite the student has not already passed (a prior enrollment whose `grades.remarks = 'PASSED'`).

### 4. Grade audit/history log (`grade_audit_logs`)

**Why:** the Gantt roadmap names "Immutable Historical Records System & Hardened Grade Modification Post-Closing Locks." The paper's Grades table (3.9) has no versioning or lock mechanism.

Added to `grades`: `is_locked` (boolean), `locked_at`. Added table `grade_audit_logs`: `audit_id` (PK), `grade_id` (FK), `changed_by_user_id` (FK → `users`), `field_changed`, `old_value`, `new_value`, `changed_at`. `GradeService::record()` writes one audit row per changed field; `GradeService::lock()` requires a final grade to exist before locking, after which further writes are rejected until an administrator calls `unlock()`.

### 5. Announcement target-audience field (`announcements.target_audience`)

**Why:** the Gantt roadmap names "Targeted Notification Dispatcher System (TargetAudience Matrix Sorting)." The paper's Announcement table (3.10) ties every announcement to a single `faculty_id` with no audience concept.

Added: `target_audience` (`All` | `Students` | `Faculty`), and `author_id` (FK → `users`) replacing the paper's `faculty_id` FK, since administrators post institute-wide announcements too, not faculty alone. `title` was also added (the paper only has `content`).

### 6. `grades.prelim_grade`

**Why:** the paper's Grades table (3.9) lists only `midterm_grade` and `final_grade`, but Sprint 6 of the Gantt explicitly names "Formulaic Automation Layer for Grade Calculation (**Prelim**, Midterm, Final Grades)," and the pre-existing prototype (`mock-data.js`) already records three terms per class. `prelim_grade` (`DECIMAL(5,2)`, nullable) was added so the schema matches what both the roadmap and the working prototype actually need.

### 7. Enrollment unit cap (business rule, not a table)

**Why:** the Gantt roadmap names "Enforce Enrollment Business Controls (Max Allowable Units & Prerequisites Checking)," but no numeric cap is specified anywhere in the paper.

**Assumption:** 24 units per term (typical for a Philippine college semester), configurable via `config('academic.max_units_per_term')` / the `MAX_UNITS_PER_TERM` env var. Change this in `.env` if NCBII's actual policy differs — it is a single config value, not hard-coded into the business logic.

### 8. "Current term" configuration

**Why:** the paper's schema has no notion of an operationally "current" semester/school year, but the dashboards and the enrollment form need a sensible default. `config('academic.current_semester')` / `current_school_year` (env: `CURRENT_SEMESTER`, `CURRENT_SCHOOL_YEAR`) hold this; an administrator updates it each term. This is an operational setting, not a database table, since it changes on a fixed cadence an admin controls directly.

### 9. Minor additions for operability

- `users.is_active` — lets an administrator deactivate an account without deleting history (referenced by the login check).
- `students.status`, `faculty.status` — `Active`/`Inactive`/`Graduated` and `Teaching`/`On leave`/`Inactive` respectively; the pre-existing UI prototype already displayed these.
- `enrollments.status` (`Enrolled`/`Dropped`) — so "dropping" a class preserves history instead of deleting the row.
- Standard Laravel `created_at`/`updated_at` timestamps on every table, and `remember_token` on `users` — framework conventions, not paper-relevant fields.

## Column naming

Every field the paper specifies keeps its exact snake_case name (`student_id`, `dept_id`, `program_name`, ...). The pre-existing (pre-migration) Eloquent models in this repo used PascalCase column names (`StudentID`, `FirstName`); those were rewritten to match the paper's Data Dictionary exactly, since no migrations existed yet to lock the old convention in.

## Legacy data migration

**Gantt Phase 5** ("Legacy Data Migration & Parallel Shadow Execution") assumes an existing digital export from NCBII's paper/spreadsheet records. No such export exists — NCBII's current records are physical logbooks per the paper's own Background of the Study. The only pre-existing digital dataset in this project is `mock-data.js`, the static UI prototype's placeholder data.

`database/seeders/LegacyDataSeeder.php` treats that dataset as the legacy source and performs a real **extract → transform → load** pass: extracting the prototype's students/faculty/subjects/classes/grades, transforming them into the new normalized schema (splitting `"8:00 AM - 10:00 AM"` into `start_time`/`end_time`, resolving foreign keys, hashing demo passwords, re-deriving grade remarks through `Grade::computeRemark()`), and loading them via Eloquent. This demonstrates the same pipeline a real migration would use; it is not a simulation of one. When NCBII produces an actual data export, replace the `$legacy*` arrays at the top of that seeder with a reader for the real file (CSV/Excel) and the rest of the pipeline (the transform + load steps) needs no changes.

## Gantt roadmap notes

Three Gantt deliverables need something outside a code repository's control to *execute*, as opposed to being buildable now:

- **Phase 6, "Production Database Deployment... & Infrastructure Setup" / "System Launch (Go-Live)"** — this project has no hosting account, domain, or server credentials. Delivered as [docs/DEPLOYMENT.md](DEPLOYMENT.md): a concrete runbook + production `.env` template + hardening checklist, ready to execute against real infrastructure once NCBII provisions it.
- **Phase 6, "Interactive Instructional Material Generation (... Video Walkthroughs & Documentation)"** — delivered as the written half, [docs/USER-GUIDE.md](USER-GUIDE.md); recording a walkthrough video is listed there as a manual follow-up task.
- **Phase 5, legacy data migration** — see above; a real migration additionally needs an actual data export from NCBII, which doesn't yet exist.

Everything else named in the Gantt chart (enrollment engine, conflict detection, grade computation/locking/audit trail, announcements, RBAC, full system integration) is implemented and covered by the test suite (`tests/Feature`, `tests/Unit`).
