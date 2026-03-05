# Postman to PHPUnit Migration Playbook

## Goal
Port Postman API tests into maintainable PHPUnit unit + integration tests quickly, with deterministic DB-backed runs and CI enforcement.

## What To Copy First (Starter Kit)
Copy these from this repo into the new repo, then adjust namespaces/paths:

1. `phpunit.xml`
2. `tests/bootstrap.php`
3. `tests/Support/ApiServer.php`
4. `tests/Support/HttpClient.php`
5. `tests/Support/TestDatabase.php`
6. `tests/Integration/IntegrationTestCase.php`
7. `tests/sql/schema.sql`
8. `tests/sql/seed_reference.sql`
9. `tests/sql/seed_auth.sql`
10. `tools/generate_postman_phpunit.php` (if used)
11. Preflight probes (DB bootstrap + auth roundtrip)

## Migration Strategy (Fastest Path)
1. Stabilize infra first:
   - Env-based config overrides in `core/config.php` (or loader + test config split)
   - Ephemeral or reusable test schema bootstrapping
   - API test server process with explicit child env propagation
2. Prove auth flow:
   - `/auth` success
   - invalid password
   - refresh/revoke/logout
   - protected route without token
3. Generate status-only matrix from Postman.
4. Promote generated cases into curated suites by domain.
5. Remove promoted generated cases immediately.
6. Keep CI green on every batch.

## Recommended Test Suite Layout
1. Unit:
   - `tests/Unit/WebhookHandlers/*`
   - `tests/Unit/Models/*`
2. Integration:
   - `tests/Integration/AuthApiTest.php`
   - `tests/Integration/UserApiTest.php`
   - `tests/Integration/MemberApiTest.php`
   - `tests/Integration/NameApiTest.php`
   - `tests/Integration/TransactionApiTest.php`
   - `tests/Integration/<domain>ApiTest.php` for reports/reference/webhooks

## Promotion Workflow (Per Endpoint Group)
For each Postman item:

1. Add a curated test method in the correct domain test class.
2. Reproduce auth state (login helper / bearer header).
3. Assert HTTP status.
4. Add deeper assertions from Postman script:
   - response envelope keys
   - key field/value checks
   - negative path messages/statuses
5. If stateful: do full lifecycle (create -> read -> update -> delete).
6. Remove matching generated matrix entry.
7. Run focused test:
   - `.\vendor\bin\phpunit --testsuite Integration --filter <ClassOrMethod>`

## Prioritization Order
1. Auth
2. Core CRUD (users, members, names, transactions)
3. Reference/read-only endpoints
4. Reports/filters
5. Webhooks + queue/reconciliation
6. External dependencies (optional/manual smoke only)

## Known Pitfalls and Fixes
1. Child process using stale env:
   - Ensure `ApiServer` injects current `KA_*` env into spawned PHP process.
2. DB permission failures:
   - Need `CREATE`, `DROP`, `CREATE VIEW` (if schema has views), DML privileges.
3. Unknown DB name errors:
   - Verify schema lifecycle and that child process reads current `KA_DB_NAME`.
4. Slow local runs with Xdebug:
   - Disable step-debug for test runs or run debugger listener to avoid timeout delays.
5. Empty generated provider:
   - If all generated tests are promoted, delete generated test class.

## CI Baseline
Add required PR check workflow:

1. Install PHP + Composer deps
2. Configure test env secrets
3. Run:
   - `.\vendor\bin\phpunit --testsuite Unit`
   - `.\vendor\bin\phpunit --testsuite Integration`
4. Always run teardown/drop for ephemeral DB schema

## Config and Secret Checklist
Set per environment:

1. `KA_DB_HOST`
2. `KA_DB_PORT`
3. `KA_DB_NAME`
4. `KA_DB_USER`
5. `KA_DB_PASSWORD`
6. `KA_TOKEN_ISS`
7. `KA_TOKEN_AUD`
8. `KA_MEMBER_KEY`
9. `GOCARDLESS_WEBHOOK_SECRET`
10. `GOCARDLESS_ACCESS_TOKEN`
11. `GOCARDLESS_ENVIRONMENT`

## Definition of Done
1. All internal API Postman cases are represented in curated PHPUnit suites.
2. Generated matrix is empty and removed.
3. Unit + integration suites pass locally and in CI.
4. Fixtures are deterministic; tests are order-independent.
5. Migration notes captured for the next repo.

## Reuse Template for New Repo Kickoff
1. Copy starter kit files.
2. Wire env config + DB bootstrap.
3. Confirm `/auth` and `/status` integration tests pass.
4. Generate matrix from Postman collection.
5. Promote endpoints in priority order.
6. Remove generated matrix class when finished.
