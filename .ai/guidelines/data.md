---

Database Safety Guideline: Destructive Commands Require Explicit Authorization

---

1) Default stance

Assume all data is production-grade and must be preserved.

Never run destructive database commands unless the user gives explicit, direct authorization in the same message.

2) What counts as “destructive”

Treat the following as destructive and blocked by default:

Laravel / Artisan

php artisan migrate:fresh

php artisan db:wipe

php artisan migrate:reset

php artisan migrate:refresh (can destroy/recreate tables depending on state)

php artisan schema:drop

Any custom commands that drop/truncate tables

SQL

DROP DATABASE, DROP SCHEMA

DROP TABLE

TRUNCATE TABLE

DELETE FROM <table> without a restrictive WHERE (or mass deletes)

ALTER TABLE ... DROP COLUMN (data loss)

Framework/ORM

Any “reset database”, “clear all tables”, “reseed from scratch” operations

Any migration that drops tables/columns without a safe, reversible plan

3) Authorization rules (must be explicit)

To proceed with a destructive action, require ALL of the following in the user’s message:

Environment confirmation: local / staging / production

Scope confirmation: what will be wiped (entire DB? specific tables?)

Backup confirmation: either:

“I have a backup taken at <time>”, or

“No backup needed (I accept the risk)”

Explicit phrase: user must type exactly:
AUTHORIZE_DB_DESTRUCTION

Command echo confirmation: user must repeat the exact command(s) that will be executed.

If any one of these is missing → refuse and propose safe alternatives.

4) Safe alternatives to propose first

When the user is trying to “reset” for debugging, offer these options:

Use a new database/schema for testing (myapp_dev_reset_2026_02_15)

Run non-destructive migrations only: php artisan migrate

Rollback limited steps: php artisan migrate:rollback --step=1

Use seeders without wiping (only insert/update needed rows)

Delete only test data with a strict WHERE clause

5) Execution safeguards

Even with authorization:

Print a destruction plan (what commands, what effect).

Verify the DB connection target (host, database name) and environment.

Prefer transactional changes where possible.

Require a final “yes” if anything indicates production.

6) Refusal template (what the agent should say)

If the user didn’t authorize properly, respond like:

“I can’t run commands that wipe or reset your database without explicit authorization.
If you really intend to do this, reply with: environment + scope + backup status + the phrase AUTHORIZE_DB_DESTRUCTION + the exact command(s).
Otherwise, here are safer options…”
