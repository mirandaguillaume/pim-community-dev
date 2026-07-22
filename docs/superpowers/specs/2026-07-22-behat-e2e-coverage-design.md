# Behat E2E PHP coverage (remote php-fpm, PCOV, nightly-only) — Design

**Goal:** Measure which PHP application code (`src/**`) is exercised by the legacy **Behat** end-to-end suite, and upload it to Codecov under a dedicated `e2e-behat` flag — **nightly-only**, mirroring the Playwright JS E2E coverage architecture (PRs #339 / #343). This is sub-project #2 of the "coverage des tests fonctionnels" intent; the Playwright half (JS) is done, this is the PHP half.

**The core problem (why this is "the hard half"):** Behat's meaningful scenarios are `@javascript` — a headless Chrome drives real HTTP requests served by **php-fpm**, a process wholly separate from the `vendor/bin/behat` runner. Coverage therefore cannot be collected in-process like PHPUnit; it must be collected **server-side, per HTTP request** ("remote coverage") and merged offline.

## Decisions locked during brainstorming

1. **Scope = remote php-fpm** (not in-process-only). We instrument the php-fpm workers so the `@javascript` scenarios that exercise the full stack are what gets measured — the real functional coverage. In-process-only (a Behat CLI listener) was rejected: it would capture only the minority of non-JS BrowserKit scenarios, which are closer to the PHPUnit-integration tests already run.
2. **Driver = PCOV** (not Xdebug). PCOV is line-only with ~10–20% overhead; the repo's existing PHPUnit clover is already line-level, so no parity is lost. Xdebug's 2–5× per-request overhead was rejected as too risky against an already 120-minute, 10-shard, flaky Behat suite on shared bare-metal runners. PCOV loaded-but-disabled is genuinely inert, so it can be baked into the shared image at zero cost to PR runs.
3. **Single toggle = `pcov.enabled`.** Because `pcov.enabled` is `INI_SYSTEM` (readable via `ini_get()` but not settable at runtime), it is enabled at php-fpm startup for the nightly only, and the app-side subscriber gates on the *same* `ini_get('pcov.enabled')` value — one signal drives both PCOV collection and the subscriber, so there is only one thing to verify (the #328 silent-no-op trap), not two.

## Architecture

```
Nightly test-behat[shard N]  (PCOV enabled in the httpd php-fpm master)
  ├─ @javascript scenario → headless Chrome → HTTP → Apache → php-fpm (separate process)
  │     BehatCoverageSubscriber (APP_ENV=behat only):
  │       kernel.request  → CoverageCollector::start()      (only if ini_get('pcov.enabled')===1)
  │       register_shutdown_function → CoverageCollector::stopAndDump($dir)
  │       → var/tests/behat-coverage/<shard>/<pid>-<uniqid>.cov   (serialized CodeCoverage)
  ├─ NEW step (nightly-gated): merge-behat-coverage.php collapse
  │       --in var/tests/behat-coverage/<shard>  --out var/tests/behat-coverage/shard-<N>.cov
  │     (incremental unserialize→merge→free; thousands of per-request dumps → ONE per shard)
  └─ shard-<N>.cov rides in the EXISTING behat-results-<shard> artifact tar (bind-mount ./:/srv/pim, no docker cp)

coverage-summary  (already fans-in all behat-results-* artifacts)
  └─ NEW step: merge-behat-coverage.php final
        --in <all downloaded shard-*.cov>  --clover coverage-behat/clover.xml  --lcov coverage-behat/lcov.info
      → codecov-action (flags: e2e-behat, fail_ci_if_error: false)
      → fills the coverage % column of the existing "Behat E2E" row in GITHUB_STEP_SUMMARY

Per-PR runs: pcov.enabled=0 → subscriber no-ops → zero dumps → collapse no-ops → nothing uploaded → zero PR cost.
```

**No new composer dependency.** `phpunit/php-code-coverage` 10.1.16 is already vendored (transitively via phpunit). We use its API directly: `Driver\Selector::forLineCoverage($filter)`, `CodeCoverage`, `CodeCoverage::merge()`, `Report\Clover`, `Report\Lcov`.

## Components

### 1. `CoverageCollector` (new, `tests/`-autoloaded namespace)
Wraps `SebastianBergmann\CodeCoverage\CodeCoverage`.
- Constructor builds a `Filter` scoped to `src/**`, reusing `phpunit.xml.dist`'s `<source>` include/exclude (excludes `*Test.php`, `*Integration.php`, `*EndToEnd.php`), and a line-coverage driver via `Driver\Selector::forLineCoverage($filter)` (auto-selects PCOV when `extension_loaded('pcov') && ini_get('pcov.enabled')`).
- `start(): void` → `$coverage->start($id)` with a per-request id.
- `stopAndDump(string $dir): void` → `$coverage->stop()`, then `file_put_contents("$dir/".getmypid()."-".uniqid('', true).".cov", serialize($coverage))`. The `<pid>-<uniqid>` name guarantees no collision across concurrent fpm workers or shards.
- **What it does / how to use it / depends on:** it captures line coverage of `src/**` for one request and serializes it; you call `start()`/`stopAndDump()`; it depends on php-code-coverage + a live PCOV driver.

### 2. `BehatCoverageSubscriber` (new, `tests/`-autoloaded namespace)
A Symfony `EventSubscriberInterface`, registered as a service **only in `APP_ENV=behat`** (a behat-only services file), never wired in prod.
- Gate: on construction (or on first event) it checks `extension_loaded('pcov') && (int) ini_get('pcov.enabled') === 1`; if false, every handler is a no-op (near-zero cost on normal behat runs).
- `kernel.request` → `CoverageCollector::start()`, and registers a `register_shutdown_function` that calls `stopAndDump(var/tests/behat-coverage/<shard>)`. Using a shutdown function (rather than depending solely on `kernel.terminate`) guarantees the dump even on fatal/early exit.
- The `<shard>` directory segment comes from the same env the CI job uses for Playwright sharding (e.g. `PW_SHARD`/a behat shard var); if unset it falls back to `local`.
- Wrapped in `try/catch` — a coverage failure is logged and **never** breaks a scenario.

### 3. Image change — `Dockerfile` (shared `ci` target)
Add PCOV in a stage inherited by the `ci` target:
```
pecl install pcov   # + build deps as the existing extension installs do
# conf.d/25-pcov.ini:
extension=pcov.so
pcov.enabled=0                 # default OFF → inert on every PR run
pcov.directory=/srv/pim/src    # restrict instrumentation to src/ (perf + scope)
```
`pcov.enabled=0` makes PCOV inert, so baking it into the shared image costs PR runs nothing.

### 4. Nightly enablement — `docker/php-coverage.d/pcov-on.ini` + `docker-compose.yml`
- New committed file `docker/php-coverage.d/pcov-on.ini` containing exactly `pcov.enabled=1` (rides the `./:/srv/pim` bind-mount, so it is present inside the container without rebuilding).
- `docker-compose.yml` `httpd` service gains `PHP_INI_SCAN_DIR: ${PHP_INI_SCAN_DIR:-}` (default empty → unchanged behaviour).
- The **nightly** `test-behat` job sets `PHP_INI_SCAN_DIR=/usr/local/etc/php/conf.d:/srv/pim/docker/php-coverage.d` on the `httpd` service before `docker-compose up`. The php-fpm **master** reads `PHP_INI_SCAN_DIR` at startup; the extra dir's `pcov.enabled=1` overrides the baked `0`. On PR runs the var is unset → PCOV stays disabled.
- (The exact `conf.d` path is confirmed against the built image during implementation.)

### 5. `merge-behat-coverage.php` (new, one script, two modes)
A plain PHP CLI script (no framework boot) using the vendored php-code-coverage.
- **collapse mode** `--in <dir> --out <file.cov>`: glob `<dir>/*.cov`, incrementally `unserialize()` + `CodeCoverage::merge()` (load-merge-free one at a time to bound memory), then `serialize()` the merged object to `--out`. Used per shard inside `test-behat`.
- **final mode** `--in <glob/dir of shard-*.cov> --clover <path> --lcov <path>`: same incremental merge, then write `Report\Clover` and `Report\Lcov`. Used in `coverage-summary`.
- Best-effort: any error is logged and the script **exits 0**. If it finds **zero** `.cov`/`shard-*.cov` inputs it prints a loud warning (`[behat-coverage] WARNING: 0 dumps found — PCOV likely not active in the fpm SAPI`) and exits 0 (nothing to upload). This warning is the anti-#328 tripwire.

### 6. CI wiring — `.github/workflows/ci.yml`
- **`test-behat`** (sharded matrix): a new step after the behat passes, gated to `schedule`/`workflow_dispatch` (the `detect-changes` force-override already cascades to `test-behat` on those events — no new gating logic), running the collapse mode into `shard-<N>.cov`, and ensuring `var/tests/behat-coverage/shard-<N>.cov` is included in the existing `behat-results-<shard>` archive step (~ci.yml:1515). It also sets `PHP_INI_SCAN_DIR` for the `httpd` service (component 4) on those events.
- **`coverage-summary`** (~ci.yml:1826, already fans-in `behat-results-*`): a new step running the final merge over all downloaded `shard-*.cov`, then `codecov/codecov-action` with `files: coverage-behat/lcov.info`, `flags: e2e-behat`, `fail_ci_if_error: false`; plus filling the coverage % of the existing "Behat E2E" summary row. `continue-on-error: true`.

### 7. `codecov.yml`
Add a flag `e2e-behat` with `paths: [src/]` and `carryforward: true`, mirroring `e2e-playwright`.

## Error handling / best-effort (never fail a job)
- Subscriber: `try/catch`, logs, never breaks a scenario.
- Collapse & final scripts: exit 0 always; loud warning on zero dumps.
- CI steps: `continue-on-error: true`; Codecov `fail_ci_if_error: false`.
- PR runs: `pcov.enabled=0` → no dumps → collapse no-op → nothing uploaded. **Zero PR cost.**

## Testing / validation
No local Jest/PHPUnit (Jest crashes the machine; the behat env is heavy) — rely on CI + careful review, exactly as the Playwright coverage did.
- **Unit tests (PHPUnit, run in CI):**
  - `BehatCoverageSubscriber` gate logic: no-op when the gate is false, active when true (the `pcov.enabled` decision is injected/abstracted so it is testable without a live driver).
  - `CoverageCollector` dump-filename format + uniqueness, and that `stopAndDump` writes a file under the given dir.
  - `merge-behat-coverage.php`: over **committed fixture `.cov` files** (pre-serialized `CodeCoverage` samples) assert the merged clover/lcov reports the expected covered/total lines; assert the zero-input path warns and exits 0. This is the PHP analogue of the Playwright monocart local validation.
- **Mutation:** the new `src/`/`tests/` PHP files enter Infection mutation shards; the unit tests above keep the MSI up (analogous to the js→ts mutation lesson).
- **Real proof = the next nightly:** the `e2e-behat` Codecov flag reads a realistic sub-100% line % over `src/**`, and the merge log prints a non-zero dump count.

## Non-goals
- No in-process (BrowserKit `symfony` session) coverage — remote fpm only.
- No Xdebug, no branch/path coverage — PCOV line-only.
- No new composer dependency (`phpcov` not added; use the vendored php-code-coverage API).
- No change to the Playwright JS coverage, the PHPUnit/Jest pipelines, or the per-PR Behat behaviour.
- No new Behat scenarios — the subscriber is transparent; the existing suite is the sole input (byte-identical Behat contract preserved).

## Risks / open items (resolve during implementation)
1. **FPM startup env propagation (the #328 trap).** The design hinges on the php-fpm master seeing `PHP_INI_SCAN_DIR` (via supervisord → child) so `pcov.enabled=1` takes effect in the request SAPI, not just CLI. The implementer must **prove** PCOV is active in the fpm request context (e.g. a one-off debug assertion during a dry nightly, or simply the zero-dump tripwire). If supervisord does not forward the env, the fallback is a tiny env-gated startup shim in the fpm program that writes `pcov-on.ini` before `exec php-fpm` — same outcome, more explicit.
2. **`conf.d` path.** The exact `PHP_INI_SCAN_DIR` default path (`/usr/local/etc/php/conf.d` vs distro path) depends on the base image; confirm against the built image.
3. **Dump volume / memory.** A shard may issue thousands of requests → thousands of `.cov`. The per-shard collapse (incremental merge, load-merge-free) is what keeps both the artifact and the final merge bounded; verify peak memory on a real nightly and, if needed, collapse in sub-batches.
4. **Codecov path normalization.** The lcov paths must resolve to `src/**` for Codecov attribution; the collector's absolute `/srv/pim/src` paths may need trimming to repo-relative in the final report. Validate on the first nightly; add a path prefix strip if attribution is off.
5. **`register_shutdown_function` vs `kernel.terminate`.** Confirm the front controller (`public/index.php`) calls `$kernel->terminate()`; regardless, the shutdown-function dump is the robust path and should be the primary mechanism.
6. **Still-empty risk.** If the nightly yields zero dumps or ~0% despite PCOV being active, escalate to the in-process listener as a supplementary source — a separate decision, out of scope here.
