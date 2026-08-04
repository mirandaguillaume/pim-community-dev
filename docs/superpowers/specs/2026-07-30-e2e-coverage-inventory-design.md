# E2E coverage inventory — per-scenario PHP + JS for Behat and Playwright — Design

**Goal:** produce a **migration inventory**: for each E2E test, which `src/**` PHP and which front-end JS it exercises — plus the inverse, file → tests. The purpose is migrating the Behat suite to Playwright without silently losing coverage, and watching Behat's covered set shrink as that migration proceeds.

**This is not a Codecov percentage.** That distinction resets almost every constraint from `2026-07-29-behat-coverage-raw-collect-design.md`.

## Why the previous constraints do not apply

The raw-collect rework fought to make PHP coverage cheap enough to run **nightly without turning the suite red**. Two follow-up levers then failed (see that spec's dead-ends section): raising the `Spin` timeout made things reproducibly worse, and amortising `\pcov\collect()` across requests is impossible because `PHP_RSHUTDOWN_FUNCTION(pcov)` destroys every table each request.

An inventory has no nightly constraint. It runs **on demand**, so:

- the ~2.8x per-request overhead is irrelevant — a 25-minute shard is fine;
- shard failures are largely irrelevant — a scenario that fails still exercised everything up to its failure point, and that is data we want;
- no `Spin` timeout tuning is needed or wanted.

**Consequence that is easy to miss:** every coverage step needs `if: always()`. Today a red shard *skips* the merge entirely — observed on Gate 1, where the failed shard produced no diagnostics at all. Defensible for a percentage; wrong for an inventory.

## Scope note

This spec deliberately covers **two subsystems** — server-side PHP collection and browser-side JS collection. They share only the inventory builder. That was flagged as a decomposition candidate and the decision was to design both together; recording it here so the coupling is a known choice rather than an oversight. They remain independently implementable and independently testable.

## Architecture

```
                     PHP (server-side, php-fpm)      JS (browser-side)
                     ──────────────────────────      ─────────────────
  Behat          auto_prepend shim  ← CHANGED        se:cdp Profiler  ← NEW
  Playwright     auto_prepend shim  ← now works      page.coverage    ← EXISTS

              <pid>.dump, records stamped          coverage-v8/<shard>/<testid>.json
                  with the test id
                            │                                    │
                            │                    monocart (e2e-coverage-report.js, EXISTS)
                            │                                    │
                            └────────► inventory builder ◄───────┘
                                              │
                       docs/coverage-inventory/{scenarios,files}.json  (committed)
```

## Decisions locked during brainstorming

1. **PHP collection moves from a Symfony subscriber to `auto_prepend_file`.** This is what makes Playwright PHP coverage possible at all — see below. `auto_prepend_file` is `PHP_INI_PERDIR`, so `docker/php-coverage.d/pcov-on.ini` can install it alongside `pcov.enabled=1`: one ini file turns on both the driver and the collector.
2. **Per-test attribution via a marker file**, not a cookie. Behat writes the current scenario id in `@BeforeScenario`; Playwright writes it in a fixture; the PHP shim reads it and stamps each dump record.
3. **Behat JS via Selenium CDP**, not an instrumented bundle. Verified reachable (below), and it reuses the existing monocart pipeline untouched — no second build artifact, no SWC/Babel instrumentation, no `window.__coverage__` flushing across Backbone page loads.
4. **The `nightly` Behat suite**, because it includes `@critical` and `@optional` — the inventory must cover everything that needs migrating, not just what PRs run.
5. **Output committed to the repo** as JSON, so successive runs diff and migration progress is visible in history.
6. **Dedicated `workflow_dispatch` workflow.** Not attached to the nightly, which keeps the overhead question permanently out of scope and leaves the `e2e-behat` Codecov flag decision independent of this work.

## Why Playwright has no PHP coverage today, and the fix

`src/Kernel.php:51-52` loads services from `config/services/*.yml` then `config/services/<APP_ENV>/**/*.yml`. The collector is registered in `config/services/behat/coverage.yml`, so it exists **only** when `APP_ENV=behat`. Behat runs `APP_ENV=behat` (`ci.yml`); Playwright runs `APP_ENV=prod`. Everything else in the PHP pipeline — collector, dump format, merger, Clover, the denominator backfill — is entirely env-agnostic and already correct.

The obvious fix is a trap: adding `config/services/prod/coverage.yml` would break real production builds, because the Coverage classes live under `autoload-dev` and Symfony validates service classes at **container compile time**, so a `--no-dev` install would fail to build.

`auto_prepend_file` avoids the container altogether. It runs before the framework, needs no service registration, and works in any `APP_ENV` — so **one mechanism serves both suites**. It is also the canonical remote-coverage pattern (php.ini prepend; Codeception's `c3`), which the subscriber approach diverged from.

## Verified: Selenium exposes CDP

Checked empirically rather than from documentation, because three ideas in the preceding work died on unverified assumptions. `docker-compose.yml` runs `selenium/standalone-chrome:4.27.0`; creating a Chrome session and reading its capabilities returns:

```
se:cdp        : ws://192.168.176.2:4444/session/<sid>/se/cdp
se:cdpVersion : 131.0.6778.204
se:bidiEnabled: false
```

CDP is available (BiDi is not, and is not needed). The websocket URL is on the `pim` Docker network, which the Behat CLI can reach — it runs inside the `httpd` container via `docker-compose exec` in `.github/scripts/run_behat.sh`.

**Port 4444 is not published to the host** (only VNC on 7900), so any helper must run inside a container on the `pim` network.

## Components

| Component | Action |
| --- | --- |
| `docker/php-coverage.d/pcov-on.ini` | **Modify** — add `auto_prepend_file` pointing at the shim |
| `docker/coverage-prepend.php` | **Create** — starts pcov, registers a shutdown handler, reads the marker, delegates to the existing recorder |
| `config/services/behat/coverage.yml` + `BehatCoverageSubscriber` | **Delete** — superseded by the shim; removes the `APP_ENV=behat` coupling |
| `RawCoverageRecorder` | **Extend** — records carry a test id |
| `CoverageMerger` | **Extend** — group by test id; keep the executable-line backfill and single `append()` |
| `tests/legacy/features/Context/CoverageMarkerContext.php` | **Create** — `@BeforeScenario` writes `<feature>:<line>` to the marker |
| `tests/front/e2e/fixtures/coverage-fixture.ts` | **Extend** — also write the marker, so Playwright gets PHP attribution |
| `tests/front/e2e/coverage/behat-cdp-coverage.js` | **Create** — connects to `se:cdp`, drives `Profiler`, writes per-scenario V8 dumps in the shape monocart already consumes |
| `tests/front/e2e/coverage/e2e-coverage-report.js` | **Unchanged** — already reads `coverage-v8/**/*.json` and rebuilds full denominators from source maps |
| `tests/front/e2e/coverage/build-inventory.js` | **Create** — joins per-test PHP and JS into the two JSON views |
| `.github/workflows/coverage-inventory.yml` | **Create** — `workflow_dispatch` only, `if: always()` on every coverage step |

## Per-test attribution

The marker is a single file, `var/tests/behat-coverage/.current-test`, holding the current test id as plain text. This is unambiguous because **scenarios run sequentially within a shard, and each shard is its own container and workspace** — no cross-shard bleed, no concurrent writers.

**Dump files keep their existing `<pid>.dump` naming**; the test id goes *inside* each appended record, not into the filename. One file per fpm worker rather than one per (scenario × worker) — which would be thousands of files per shard — and the merge groups by the stamp. This preserves the append-only, no-locking property the current format relies on.

Chosen over the cookie approach deliberately: setting a cookie via Selenium while the browser is on `about:blank` raises `invalid cookie domain`, a documented trap in this pattern, and a cookie only attributes requests the *browser* makes.

**Known imprecision, accepted:** async requests outliving a scenario are attributed to the next one. For a migration inventory this is noise, not error — it can only over-attribute, never lose coverage.

**Known limitation, accepted for now:** the shim only starts PCOV for HTTP-serving SAPIs, never `cli` (`docker/coverage-prepend.php`). This is necessary, not incidental — without a `PHP_SAPI` guard, `vendor/bin/behat` itself (a two-hour-lived CLI process) would be the thing PCOV instruments: one `pcov\start()` at process start, one `stopAndDump()` at process exit, unioning every line executed by every non-`@javascript` scenario (Mink's `symfony` session drives `$kernel->handle()` in-process, never over HTTP) into a single record stamped with whichever scenario the marker happened to hold at shutdown. That is strictly worse than no coverage: it reads as one scenario covering nearly all of `src/**`.

The consequence is that **non-`@javascript` scenarios report `php: {}`** — their PHP execution happens entirely inside the guarded CLI process, which now writes nothing. This is the safe direction of error for a migration inventory: a file whose only coverage came from such scenarios reads as "not yet covered by anything with a JS side, still needs a Playwright equivalent", which over-reports remaining work rather than falsely declaring a file migrated. `@javascript` scenarios are unaffected — Mink's `selenium2` session drives the browser, so every request they cause is a real HTTP request into an fpm worker, which the shim still instruments.

The fix — flush the accumulated coverage and restart PCOV per scenario from `CoverageMarkerContext::recordCurrentScenario()` (`@BeforeScenario`), rather than once per process — would restore in-process attribution for the `symfony` session. That is a deliberate follow-up, not an oversight: it changes the shim's lifecycle (per-scenario start/stop instead of per-process) and needs its own verification against a live suite, which is out of scope for this fix round.

## Output

Two views under `docs/coverage-inventory/`, committed:

- `scenarios.json` — `{"<suite>:<feature>:<line>": {"php": {"<file>": [lines]}, "js": {"<file>": [lines]}}}`
- `files.json` — the inverse, `{"<file>": ["<scenario>", …]}`

`files.json` is the one that answers the migration question directly: when a file's last remaining Behat scenario has moved to Playwright, that file's coverage is safe to consider migrated.

## Error handling

Unchanged in spirit from the existing design, and it all still applies:

- the shim wraps everything in `try/catch (\Throwable)` and can never affect a request;
- the merge CLI always `exit 0`;
- the CDP helper is best-effort: a websocket failure logs and never fails a scenario;
- zero records and zero-covered-lines both emit loud warnings — a silently empty inventory is the failure mode most likely to go unnoticed;
- `if: always()` so a red shard still yields its inventory.

## Testing

- **Unit:** the shim's marker parsing and record stamping; the merger's per-test grouping; the inventory builder's join and inverse, including a test that a file covered by two scenarios lists both.
- **Integration:** the existing `MergeCliTest` pattern — real dumps through the real CLI.
- **The CDP helper needs a live browser**, so it gets a focused check against a running Selenium rather than a unit test, plus an assertion in the workflow that the dump count is non-zero.
- **Acceptance:** one `workflow_dispatch` run producing non-empty `scenarios.json` with both `php` and `js` populated for at least one scenario, and a plausible file count (Gate 1 measured 2,516 PHP files suite-wide — per-scenario should be far smaller).

## Non-goals

- Any nightly Codecov figure. The `e2e-behat` flag stays off; that decision is independent.
- Reducing the ~2.8x per-request overhead. Established as inherent to per-request collection; irrelevant here.
- Branch or path coverage. PCOV is line-only, matching the existing reports.
- Instrumenting the front bundle. The CDP route makes it unnecessary.

## Risks

- **The shim runs on every request in the coverage image, outside the framework.** A fault there is far more dangerous than a subscriber fault — it precedes error handling. Mitigation: the entire body inside `try/catch (\Throwable)`, no autoloader dependency beyond an explicit `require`, and a smoke check that the app still serves a page with the shim installed before trusting any run.
- **CDP coverage granularity across page loads.** `Profiler.takePreciseCoverage` returns data for currently-loaded scripts; a Backbone full page load may reset it. Needs measuring during implementation — if per-scenario JS turns out to capture only the last page, fall back to taking coverage per navigation rather than per scenario.
- **Committed JSON churn.** Per-line data for thousands of files could make large diffs. If it proves unwieldy, reduce `scenarios.json` to file-level granularity and keep line detail in the CI artifact only.
- **Deleting `BehatCoverageSubscriber` removes the only currently-working PHP path.** Implement and verify the shim before deleting the subscriber, not alongside.
