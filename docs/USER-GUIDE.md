# User Guide

Written quick-start guide for each portal, delivered in place of the Gantt roadmap's "video walkthroughs" (see [ERD-ADDENDUM.md](ERD-ADDENDUM.md#gantt-roadmap-notes)). **Recording an actual walkthrough video is a follow-up task for the project team** — this document covers the same ground in writing so the portal is usable and demo-able without it.

All three portals share one entry point: open the portal URL, choose **Student** or **Faculty / Administrator** at the top of the sign-in card, and enter your credentials.

---

## Student Guide

**Signing in:** choose the **Student** tab, enter your Student ID Number (format `2026-0001`) and your password, then **Continue to Dashboard**.

**Dashboard** (`/dashboard`) shows, for the current term:
- Your profile (name, program, year level).
- Account status.
- How many subjects/units you're enrolled in.
- Your **general average** — the average of your per-subject semestral averages, only counting subjects with all three term grades recorded. The institute's grading scale is **1.00 (highest) to 5.00 (lowest); 3.00 or below passes.**
- Your **class schedule** for the term (day, time, room, instructor).
- Your **tuition balance** (a summary — see My Records for the full ledger).
- Recent **announcements**.

**My Records** (`/student/records`) shows:
- Every term you've been enrolled, grouped by semester/school year, with prelim/midterm/final grades and the computed remark (**PASSED**, **FAILED**, **INC** if no final grade yet, **NG** if no grades recorded at all).
- Your full **portal ledger** — every charge and payment on your account, and your current balance. This is **read-only**: balances are updated by the registrar/clerk after a cash payment; there is no online payment option in this phase.

You cannot edit any of your own records — that's by design (data integrity).

---

## Faculty Guide

**Signing in:** choose the **Faculty / Administrator** tab, enter your username and password.

**Dashboard** (`/faculty`) lists every class schedule credited to your account this term, each showing the subject, how many students are enrolled, and the room/day/time. Click a class card to open its grade sheet. Only classes credited to you are ever shown or editable — attempting to open another instructor's class (e.g. by guessing a URL) is blocked.

**Grade encoding** (from a class card): for each enrolled student, enter Prelim / Midterm / Final grades on the institute's 1.00–5.00 scale and click **Save**. The remark (PASSED/FAILED/INC/NG) recalculates automatically — you don't set it directly. Every change is written to an audit log (who changed what, and when) automatically; nothing extra to do there.

**Locking a grade:** once a student's final grade is recorded, click **Lock**. A locked grade can no longer be edited by anyone except an administrator, who can reopen it via **Unlock** if a correction is genuinely needed after the fact.

**Posting an announcement:** from your dashboard, fill in the title (optional), message, and choose an audience — **All** (students and faculty) or **My Students Only**.

---

## Administrator Guide

**Signing in:** choose the **Faculty / Administrator** tab, enter the admin username and password.

**Dashboard** (`/administrator`) shows institute-wide counts (students, subjects, class schedules, faculty, active enrollments) — each tile links straight to the matching management section.

**Management** (`/administrator/management`) is organized into tabs. Each tab shows a table of existing records plus a form to add a new one:

| Tab | What it manages |
|---|---|
| Students | Student records; creating one also creates their login account (you set their initial password). |
| Faculty | Faculty records; creating one also creates their login account. |
| Departments | Top-level academic units (e.g. "College of Computing Studies"). |
| Programs | Degree programs under a department (e.g. "BS Information Technology"). |
| Subjects | Course offerings, units, and an optional prerequisite subject. |
| Schedules | A subject taught by a faculty member in a room, on a day, at a time. **Conflict-checked**: the same room or the same instructor cannot be double-booked on an overlapping day/time — you'll get an inline error instead of a bad schedule being saved. |
| Subject Loads | Enrolling a student into a class schedule for a term. **Rule-checked**: this blocks enrollment past the unit cap, into a subject whose prerequisite hasn't been passed, or into a class that overlaps the student's existing schedule — again with an inline error, not a silent failure. |
| Portal Ledger | Recording a manual charge (e.g. tuition fee) or payment (cash received) against a student's account. There is no live payment gateway — this is always a manual entry after the fact. |
| Announcements | Institute-wide or audience-targeted announcements (All / Students / Faculty). |

**Editing vs. removing:** Students and Faculty records have an **Edit** action for correcting details after creation. Reference data (Departments, Programs, Subjects, Schedules) and log-like data (Ledger, Announcements, Subject Loads) support **Remove** but not in-place editing — for those, remove the mistaken entry and add a corrected one, which keeps the audit trail honest.

**Unlocking a grade:** grade locking/unlocking isn't in the Management tabs (it lives on the faculty grade-encoding screen) — an administrator with a faculty-equivalent need to unlock a grade should be given a faculty account for that department, or this can be extended as a fast-follow if administrators need it directly.

---

## Follow-up task: record a video walkthrough

The Gantt roadmap calls for video walkthroughs alongside written documentation. This guide covers the written half; recording a short screen-capture per role (Student/Faculty/Admin) covering the flows above is a manual task for the project team using this guide as the script.
