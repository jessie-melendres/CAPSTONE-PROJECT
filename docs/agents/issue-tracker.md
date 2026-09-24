# Issue tracker: none

There is no GitHub-issues-based tracker for this repo, and none is planned. Don't run `gh issue` commands here — `gh` isn't even installed on the dev machine this project is built on.

## What the spec actually is

This project implements a specific capstone paper: *"Development of a Web-Based Student Portal of North Coast Bohol Institute Incorporated"* (its ERD, Data Dictionary, and Gantt roadmap). See:

- **`README.md`** — what's implemented, project structure, tech stack.
- **`docs/ERD-ADDENDUM.md`** — every place the implementation extends, corrects, or assumes something beyond the paper's own ERD/Data Dictionary/Gantt chart, and why. Treat this as the authoritative changelog against the paper: if a change isn't listed there, it should be.

When a task changes the schema, a business rule, or anything the paper specifies, check `docs/ERD-ADDENDUM.md` first for prior art, and add an entry there if the change introduces a new deviation.

## Where work actually gets scoped

There's no ticket queue to pull from. Work is scoped directly through conversation with whoever's driving the session. When a skill says "fetch the relevant ticket" or "publish to the issue tracker," there's nothing to fetch or publish to — treat the current conversation (and `README.md` / `docs/ERD-ADDENDUM.md` for existing decisions) as the source of truth instead.
