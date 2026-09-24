# NCBII Web-Based Student Portal

A centralized academic information system for **North Coast Bohol Institute Incorporated (NCBII)** — built in PHP/Laravel + MySQL per the capstone proposal *"Development of a Web-Based Student Portal of North Coast Bohol Institute Incorporated."*

Three role-based portals, backed by a real relational database (not a static mockup):

- **Administrator** — manage departments, programs, students, faculty, subjects, class schedules, subject loads (enrollment), the portal ledger, and announcements.
- **Faculty** — encode/lock prelim/midterm/final grades for classes credited to them, and post announcements.
- **Student** — read-only self-service dashboard: profile, class schedule, grades with computed remarks, general average, and tuition ledger balance.

See [docs/ERD-ADDENDUM.md](docs/ERD-ADDENDUM.md) for everywhere the implementation extends or deviates from the paper's ERD/Data Dictionary/Gantt chart, and why. See [docs/USER-GUIDE.md](docs/USER-GUIDE.md) and [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) for operational documentation.

## Tech stack

PHP 8.0+/Laravel 9, MySQL, Blade + the project's original hand-rolled CSS (no frontend framework/build step — the pre-existing static HTML/CSS design is the visual source of truth, per `resources/views` and `style.css`).

## Setup

1. Install PHP 8.0+, Composer, and a MySQL server (this project was developed against XAMPP's bundled MySQL on Windows).
2. `composer install`
3. Copy `.env.example` to `.env`, then `php artisan key:generate`.
4. Create the database named in `.env` (`DB_DATABASE`, default `ncbii_student_portal`):
   ```sql
   CREATE DATABASE ncbii_student_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
5. Run migrations and seed the demo dataset:
   ```bash
   php artisan migrate --seed
   ```
6. `php artisan serve` and open `http://127.0.0.1:8000`.

### Demo accounts (seeded by `LegacyDataSeeder`)

| Role | Username / ID | Password |
|---|---|---|
| Administrator | `admin` | `Admin@2026` |
| Faculty | `daniel.cruz`, `ana.lim`, `rosa.velasco` | `Faculty@2026` |
| Student | `2026-0001` … `2026-0006` | `Student@2026` |

These are demo credentials seeded for local development/defense only — see [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) before any real deployment.

## Running tests

```bash
composer test
```

or directly: `vendor/bin/phpunit`. Tests run against an in-memory SQLite database (configured in `phpunit.xml`) so they don't touch your local MySQL data. 36 tests cover the business rules that matter for the paper's own evaluation criteria (ISO/IEC 25010 Functional Suitability and Security Control Assessment): unit-cap/prerequisite/conflict enrollment rules, schedule double-booking detection, grade computation/locking/audit logging, ledger balance math, password hashing, and role-based access control.

## Project structure

- `app/Models` — Eloquent models, one per table in the (extended) Data Dictionary.
- `app/Services` — business rules that don't belong on a controller or model: `EnrollmentService` (unit cap, prerequisites, schedule conflicts), `ScheduleService` (room/faculty double-booking), `GradeService` (remark computation, locking, audit trail), `LedgerService` (balance calculation).
- `app/Http/Controllers/Admin`, `.../Faculty`, `.../Student` — one controller family per portal/module, matching the paper's Administrator/Faculty/Student Module boundaries.
- `database/migrations` — the schema, in dependency order.
- `database/seeders/LegacyDataSeeder.php` — an ETL seeder that migrates the pre-existing prototype dataset (`mock-data.js`) into the real schema; see [docs/ERD-ADDENDUM.md](docs/ERD-ADDENDUM.md#legacy-data-migration).
- `resources/views` — Blade templates, reusing the original static site's CSS classes (`style.css`) so the visual design carries over unchanged.
- `tests/Feature`, `tests/Unit` — PHPUnit tests, organized by service/concern rather than by controller.

## What's implemented vs. documented as follow-up

This repo targets the capstone's Objectives 1–5 (pre-oral defense scope) *plus* every named Gantt-roadmap deliverable that can meaningfully be built without infrastructure this project doesn't have. A few Gantt items are delivered as **documentation/runbooks** rather than as executed actions, because they require things outside a code repository's control — see [docs/ERD-ADDENDUM.md](docs/ERD-ADDENDUM.md#gantt-roadmap-notes) for the full breakdown (legacy data migration, production go-live, and video walkthroughs, specifically).
