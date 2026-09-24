# Coding standards

## Formatting

[Laravel Pint](https://laravel.com/docs/9.x/pint) (default preset, no custom config) is the formatter. Run before committing:

```bash
composer fix   # applies formatting
composer lint  # checks only, fails on violations (CI-safe)
```

Don't hand-format around what Pint would already fix.

## Business rules live in `app/Services`, not controllers or models

Controllers stay thin: validate input, call a service, translate the result into a response. Anything that enforces an institutional rule (unit caps, prerequisites, scheduling conflicts, grade locking, ledger math) belongs in `app/Services` — see `EnrollmentService`, `ScheduleService`, `GradeService`, `LedgerService` for the existing pattern.

A rule violation is signaled by throwing `App\Exceptions\BusinessRuleException` with a user-facing message, not by returning null/false or an ad hoc error array. Controllers catch it and surface the message as a validation error.

## Controllers are namespaced by portal

`app/Http/Controllers/Admin`, `.../Faculty`, `.../Student` — one family per portal, matching the paper's Administrator/Faculty/Student module boundaries. A new controller goes in the namespace for the portal it serves, not in the root `Controllers` namespace (the exception is `PortalController`, which predates the split and handles cross-portal concerns like login).

## Database columns match the paper's Data Dictionary exactly

Every field the capstone paper specifies keeps its exact snake_case name (`student_id`, `dept_id`, `program_name`, ...) — see `docs/ERD-ADDENDUM.md`. Fields added beyond the paper follow the same snake_case convention for consistency, and any such addition gets documented in `docs/ERD-ADDENDUM.md` with a reason, not added silently.

## Tunable business policy is config, not a literal

A business-policy number that isn't specified in the paper (the unit cap, the current term) lives in `config/academic.php` with an `env()` default, the way `max_units_per_term` and `current_semester` already do — never hard-coded into a service.

## Tests are organized by service/concern, not by controller

`tests/Feature` mirrors the things being tested (`EnrollmentServiceTest`, `GradeServiceTest`, `ScheduleServiceTest`, `LedgerServiceTest`, `AuthenticationTest`, `AdminManagementTest`, `FacultyGradeAccessTest`) rather than one test class per controller. See [docs/agents/testing.md](docs/agents/testing.md) for the testing workflow itself.

## Baseline

On top of the above, standard Fowler code-smell judgement applies (long methods, feature envy, duplicated conditionals, etc.) wherever this document is silent — but a convention documented here always overrides that baseline.
