# Testing

PHPUnit, configured in `phpunit.xml` to run against an in-memory SQLite database — tests never touch the local MySQL data. Run with:

```bash
composer test
# or directly
vendor/bin/phpunit
```

## TDD is the default for new work

New features and bug fixes are built test-first (red → green → refactor), per the `tdd` skill. This applies in particular to anything touched by the ongoing controller/service refactor: a new business rule gets a failing test before the implementation, not after.

Before writing a test, check `CONTEXT.md` (once it exists) and `docs/adr/` for the vocabulary and decisions already established, so test names match the project's domain language instead of drifting to synonyms.

## Organization

- `tests/Feature` — one class per service/concern being exercised through the HTTP layer (`EnrollmentServiceTest`, `GradeServiceTest`, `ScheduleServiceTest`, `LedgerServiceTest`), plus cross-cutting concerns (`AuthenticationTest`, `AdminManagementTest`, `FacultyGradeAccessTest`). Not one class per controller — a controller with three unrelated actions doesn't need three unrelated test classes stapled together.
- `tests/Unit` — pure logic with no framework bootstrap, e.g. `GradeRemarkTest` for `Grade::computeRemark()`.
- Shared setup (seeding a student/schedule/enrollment fixture, etc.) goes in a helper trait alongside the tests that need it (see `tests/Feature/AcademicTestHelpers.php`) rather than duplicated per test class.

## What's covered

The suite targets the business rules the paper's own evaluation criteria (ISO/IEC 25010 Functional Suitability and Security Control Assessment) care about: enrollment rules (unit cap, prerequisites, schedule conflicts), scheduling conflict detection, grade computation/locking/audit trail, ledger balance math, password hashing, and role-based access control. A change to any of these needs a test covering the new behavior, not just a manual check.
