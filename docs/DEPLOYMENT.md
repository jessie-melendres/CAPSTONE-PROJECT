# Deployment Runbook

This is the deployment-readiness package for Gantt Phase 6 ("Production Database Deployment, Encryption Profiling, & Infrastructure Setup" / "System Launch (Go-Live)"). It has not been executed against live infrastructure — this project has no hosting account, domain, or server credentials for NCBII. Execute this runbook once that infrastructure exists.

## Prerequisites on the target server

- PHP 8.0+ with the extensions Laravel 9 needs: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`.
- MySQL 5.7+/8.0, or MariaDB 10.3+.
- Composer 2.
- A domain (or subdomain) pointed at the server, with a TLS certificate (Let's Encrypt is sufficient) — the paper's System Design (Figure 4.0) specifies HTTPS/TLS termination in front of the application layer.
- A web server (Apache or nginx) with its document root at this project's `public/` directory — never the project root.

## Step-by-step go-live

1. **Clone/deploy the code** to the server, outside the web-server-exposed path except for `public/`.
2. **Install dependencies without dev tooling:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
3. **Create the production `.env`** from the template below — never reuse the demo `.env.example` values (`APP_KEY`, DB credentials, passwords) in production.
4. **Generate a fresh application key:**
   ```bash
   php artisan key:generate --force
   ```
5. **Create the production database** and a dedicated MySQL user scoped to only that database (not `root`):
   ```sql
   CREATE DATABASE ncbii_student_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'ncbii_app'@'localhost' IDENTIFIED BY '<strong-generated-password>';
   GRANT ALL PRIVILEGES ON ncbii_student_portal.* TO 'ncbii_app'@'localhost';
   FLUSH PRIVILEGES;
   ```
6. **Run migrations** (production data — do **not** run `migrate:fresh` or the seeder against real records):
   ```bash
   php artisan migrate --force
   ```
7. **Create the real administrator/faculty/student accounts** through the Administrator portal once one bootstrap admin exists. To create that first admin account before any UI exists, use `php artisan tinker`:
   ```php
   \App\Models\User::create(['username' => 'registrar', 'password' => \Illuminate\Support\Facades\Hash::make('<strong-password>'), 'role' => 'admin']);
   ```
   Change that password immediately through a real change-password flow once one is built — there is no self-service password change yet (see Known Gaps below).
8. **Do not run `LegacyDataSeeder`** against production — it creates demo accounts with published passwords (`Admin@2026`, `Faculty@2026`, `Student@2026`). It exists for local development and defense demos only.
9. **Cache framework config/routes/views for performance:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
10. **File permissions:** `storage/` and `bootstrap/cache/` must be writable by the web server process.
11. **Point the web server's document root at `public/`**, with `.htaccess`/nginx rewrite rules routing all requests through `public/index.php` (the standard Laravel web-server config — see Laravel's deployment docs for the exact Apache/nginx snippet for your server).
12. **Enforce HTTPS**: set `APP_URL=https://...` and add HSTS/forced-HTTPS at the web server or a `App::forceScheme('https')` call, matching the paper's Figure 4.0 (HTTPS/TLS Encryption layer).

## Production `.env` checklist

```dotenv
APP_NAME=NCBII
APP_ENV=production
APP_KEY=            # generate fresh — never copy from .env.example or this repo's dev .env
APP_DEBUG=false     # never true in production — leaks stack traces to visitors
APP_URL=https://portal.ncbii.edu.ph   # replace with the real domain

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ncbii_student_portal
DB_USERNAME=ncbii_app        # a scoped user, never root
DB_PASSWORD=                 # a strong, generated secret — store in a secrets manager, not in git

SESSION_DRIVER=file          # consider 'database' or 'redis' if load-balancing across servers
SESSION_SECURE_COOKIE=true   # requires HTTPS — set once TLS is live

MAX_UNITS_PER_TERM=24        # adjust to NCBII's actual policy — see docs/ERD-ADDENDUM.md
CURRENT_SEMESTER="1st Semester"
CURRENT_SCHOOL_YEAR=2026-2027
```

## Security hardening checklist (maps to Chapter 3's "Security Control Assessment")

- [ ] `APP_DEBUG=false` in production (verified above).
- [ ] Passwords are hashed via `Hash::make()`/bcrypt before storage — already enforced everywhere accounts are created (`AdminController`, `LegacyDataSeeder`). Never add a code path that stores a plaintext password.
- [ ] Role-based access is enforced server-side by the `role` middleware (`App\Http\Middleware\EnsureRole`) on every route group, plus an explicit per-record ownership check in `Faculty\GradeController::authorizeSchedule()` — verified by `tests/Feature/FacultyGradeAccessTest.php` and `AuthenticationTest.php`.
- [ ] CSRF protection is on by default (`VerifyCsrfToken` middleware) — every state-changing form in `resources/views` includes `@csrf`.
- [ ] Database credentials and `APP_KEY` are never committed — confirm `.env` stays in `.gitignore` (already the case in this repo).
- [ ] TLS/HTTPS is enforced at the web server (step 12 above) before go-live.
- [ ] Rotate the demo seeder's known passwords (`Admin@2026` etc.) — they must never reach a production database (step 8 above).
- [x] The login endpoint is rate-limited (`throttle:6,1` — 6 attempts/minute per IP+username) to blunt credential-stuffing/brute-force attempts.

## Known gaps to close before a real go-live

These are honest limitations of the current build, not yet implemented:

- **No self-service password change/reset flow.** Admin currently sets a student/faculty's initial password directly; there's no "forgot password" or "change my password" screen yet.
- **No automated backup/restore procedure** documented yet for the production MySQL database — set up `mysqldump` on a schedule (or your host's managed-backup feature) before go-live.
