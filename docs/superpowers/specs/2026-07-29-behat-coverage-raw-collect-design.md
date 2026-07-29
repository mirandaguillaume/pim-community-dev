# Behat E2E PHP coverage — raw-collect rework — Design

**Goal:** Cut the per-request cost of the Behat PHP coverage collector far enough that the nightly `e2e-behat` flag can be switched back on. The feature itself already exists (#348, perf-patched by #351) but was **disabled on 2026-07-28** because per-request collection added a **~7x request overhead** — a Behat shard went from ~6 min to ~45 min and timing-sensitive scenarios blew past the 40s `Spin` timeout, turning every `test-behat` and `test-playwright` shard red from 07-25 on.

This supersedes `2026-07-22-behat-e2e-coverage-design.md`. That design's plumbing was correct and is kept wholesale — image, ini toggle, subscriber, per-shard collapse, artifact ride-along, Codecov flag. Only the *collection strategy* changes.

**The falsified assumption.** The 07-22 spec locked PCOV on the premise of "line-only with ~10–20% overhead". PCOV itself does cost about that (published benchmarks put PCOV line collection at ~1.3x against Xdebug's ~3.8x). The measured 7x therefore was never PCOV — roughly 5.4x of it is **userland `SebastianBergmann\CodeCoverage` work executing inside every HTTP request**.

## The diagnosis

Three costs run on every single request today, all of them avoidable:

1. **Static analysis, uncached.** `CoverageCollector::stopAndDump()` calls `CodeCoverage::stop()` → `append()` (`CodeCoverage.php:205`), which runs `applyExecutableLinesFilter()`. That resolves executable lines through `analyser()` (`CodeCoverage.php:609`), which returns a bare `ParsingFileAnalyser` — a full **nikic/php-parser** parse of every file touched — and only wraps it in `CachingFileAnalyser` when `cacheStaticAnalysis()` has been called. **#348 never calls it.** A PIM page touches hundreds of classes, so this is hundreds of AST parses per request. This is the dominant cost.
2. **A redundant filter intersect.** `PcovDriver::stop()` runs `array_intersect(waiting(), $this->filter->files())` on every request, against a Filter holding every path under `src/`. It is redundant: `pcov.directory=/srv/pim/src` (`docker/build/pcov.ini:2`) already scopes collection *in C*.
3. **An oversized dump.** `Report\PHP::process()` serializes the whole `CodeCoverage` object graph — including that Filter — to a fresh `.cov` on every request. #351 stopped *rebuilding* the Filter per request; it still gets *serialized* into each of thousands of dumps.

## Decisions locked during brainstorming

1. **The request records; the merge computes.** All `php-code-coverage` work leaves the request path. In-request we call PCOV directly and write a plain hit-line map. Every `CodeCoverage` object, the `Filter`, static analysis and Clover rendering happen **once**, offline, in the merge.
2. **Keep PCOV, keep the toggle.** `pcov.enabled` as the single `INI_SYSTEM` signal driving both collection and the subscriber gate is unchanged and still correct. No image, `docker-compose.yml`, or `codecov.yml` change.
3. **Nightly-only, as before.** Re-enable exactly the expression already sitting commented at `ci.yml:1248` (`schedule || workflow_dispatch`). `codecov.yml` already carries `carryforward: true` on `e2e-behat`, so PRs keep showing an honest combined total without paying any collection cost.
4. **The Filter changes job rather than disappearing.** It is removed from the request but is **mandatory at merge time**: `$includeUncoveredFiles` defaults to `true` (`CodeCoverage.php:52`) and `getReport()` calls `addUncoveredFilesFromFilter()` (`CodeCoverage.php:131-132`), which diffs `filter->files()` against covered files. Without a populated Filter at merge, never-touched files vanish from the denominator and the report reads as a degenerately high percentage — the same failure class #343 fixed on the JS side.
5. **Two measurement gates before rollout** (see below). Assuming a cost instead of measuring it is what sank #348; this design does not repeat that.

## Architecture

```
PER REQUEST (nightly only, ×thousands)
  BehatCoverageSubscriber            (unchanged: kernel.request prio 1024, APP_ENV=behat,
    kernel.request → \pcov\start()    gated on ini_get('pcov.enabled')===1, best-effort)
    register_shutdown_function:
      \pcov\stop()
      $files = \pcov\waiting()
      $raw   = \pcov\collect(\pcov\inclusive, $files)   ← NO filter intersect
      \pcov\clear()
      keep hits>0 only → line-number lists
      append one record to var/tests/behat-coverage/<shard>/<pid>.dump
      (starting encoding — Gate 1 confirms or retunes it)

PER SHARD (×1, offline, in the httpd container)
  merge-behat-coverage.php
    union all records                        ← plain PHP arrays, associative
    RawCodeCoverageData::fromXdebugWithoutPathCoverage($union)
    Filter: includeDirectory('/srv/pim/src') + exclude *Test/*Integration/*EndToEnd
    new CodeCoverage(new FakeCoverageDriver(), $filter)
      ->cacheStaticAnalysis(var/cache/behat-coverage-sa)   ← static analysis ×1, cached
      ->append($raw, 'behat')
    Report\Clover → shard-<N>.clover.xml
      → rides the existing behat-results-<shard> artifact → Codecov flag e2e-behat
```

## Components

| Component | Change |
| --- | --- |
| `BehatCoverageSubscriber` | **Keep as-is.** Same `kernel.request` priority, `register_shutdown_function`, and best-effort `try/catch`. Only which collector it calls changes. |
| `CoverageCollectorInterface` | **Keep.** The `start()` / `stopAndDump(string $dir)` contract survives the rewrite unchanged. |
| `CoverageCollector` | **Rewrite.** Drop `CodeCoverage`, `Selector`, `Filter`, `Report\PHP`. Call `\pcov\start()`, and on dump `\pcov\stop()` / `waiting()` / `collect()` / `clear()`, reduce to `hits>0` line lists, append one record to a per-PID file. |
| `CoverageMerger` | **Extend.** Add raw-record union, Filter construction, `cacheStaticAnalysis()`, and the single `append()`. `writeClover()` is unchanged. `mergeDir()` (which `include`s serialized `CodeCoverage` objects) is replaced by the raw path. |
| `FakeCoverageDriver` | **Promote** from test fixture to the production merge path — the merge needs a `Driver` to construct `CodeCoverage` but never starts one. |
| `merge-behat-coverage.php` | **Keep the CLI shape and `exit 0`**, add a loud non-zero-line assertion on the merged result (phpcov has a documented silent empty-merge failure class; the same risk applies here). |
| `Dockerfile`, `docker/*.ini`, `docker-compose.yml`, `codecov.yml` | **No change.** `pcov.directory`, the `php-coverage.d` toggle, `PHP_INI_SCAN_DIR: ${VAR:-:}` and both `carryforward: true` flags are already correct. |
| `.github/workflows/ci.yml` | Restore the commented expression at line 1248; keep the surrounding comment block rewritten to describe the new design. |

## Gate 0 — isolate where the 7x actually lives

Two cheap experiments, run before any rewrite. Together they answer the only question that can invalidate this whole design: **is the 7x ours, or is it PCOV's floor?**

### 0a. The no-op isolation experiment (decisive)

Make `stopAndDump()` a no-op — PCOV still starts and collects on every request, but zero `php-code-coverage` work and zero I/O happen — then re-measure a shard via `gh workflow run ci.yml --ref <branch>` (a `pull_request` run will *not* exercise this path; PCOV is gated to `schedule`/`workflow_dispatch`).

- **Still ~7x** → the cost is PCOV-native and irreducible. This design does not help, and the real options become the single-worker/flush-per-scenario variant or dropping Behat PHP coverage and keeping only the Playwright half.
- **Materially faster** → the cost is our per-request collect/serialize, exactly as diagnosed, and the raw-collect rework is the right fix.

**This gate is blocking.** If it comes back "still ~7x", stop and re-brainstorm rather than implement.

### 0b. Verify the php-fpm statics premise (diagnostic)

`CoverageCollector` caches its Filter in `private static ?Filter $filter`, documented as "built once per php-fpm worker and cached across the requests that worker serves".

**This premise is suspect.** php-fpm reuses the *process*, but PHP tears down the request context between requests, and static class properties are reset with it. If that is right, #351's cache never survived a single request — the recursive `src/` scan has been running on every request all along, which would explain why the 7x survived the fix.

**Verification:** a static counter incremented on `kernel.request`, logged, with two requests issued against a coverage-enabled container. If it reads `1, 1` the premise is false; `1, 2` and it holds.

This gate is cheap and it is diagnostic, not blocking: the raw-collect design removes the Filter from the request either way. Its value is telling us whether we have found the whole cost or only part of it.

**Validation trick for both gates:** `pull_request` CI runs with PCOV **off**. To exercise the nightly path pre-merge, use `gh workflow run ci.yml --ref <branch>` and read the "Run Behat" step duration. The PCOV-off baseline is ~6 min/shard; the #348 and #351 measurements were both ~45 min/shard.

## Gate 1 — measure dump volume before rollout

Hit-line records are far smaller than today's object graphs, but the per-request touched-file count for this app is unknown, and guessing it is exactly what went wrong in #348.

**Verification:** run one feature with collection enabled; report per-request record bytes, total bytes per shard, and merge wall-clock. The starting encoding is a gzipped per-PID append file of `hits>0` line lists; Gate 1 either confirms it or retunes the three variables (per-PID append vs per-request file, gzip vs plain, line-lists vs hit-maps) against a measured number rather than an estimated one.

**Acceptance threshold:** a coverage-enabled shard must stay close enough to its ~6 min baseline that no scenario approaches the 40s `Spin` limit. Shard wall-clock is the headline number, but per-request latency is the binding constraint.

## Error handling / best-effort (never fail a job)

Unchanged in spirit from 07-22, and it all still applies:

- The subscriber wraps everything in `try/catch (\Throwable)` and returns silently. A coverage fault must never change a scenario outcome.
- `merge-behat-coverage.php` always `exit 0`. A merge failure must never fail the nightly.
- Zero dumps found is a **loud warning**, not an error — it is the signature of PCOV not actually being active in the fpm SAPI.
- New: a merged result with zero covered lines is also a loud warning, so a silently empty Clover cannot be uploaded as if it were real.

## Testing / validation

- **Unit:** `CoverageCollectorTest` gets a PCOV-free path (the collector no longer needs a `Driver`, so the raw reduce/encode logic is directly testable). `CoverageMergerTest` gains cases for the raw union, the uncovered-files denominator, and the empty-input warning. `BehatCoverageSubscriberTest` is unchanged.
- **Integration:** Gate 1's single-feature run doubles as the end-to-end check — dumps written, merge produces non-zero covered lines, Clover parses.
- **CI:** one `workflow_dispatch` run before the nightly schedule is trusted.

## Non-goals

- Branch/path coverage. PCOV is line-only; the existing PHPUnit Clover is line-level too, so nothing is lost.
- Per-scenario attribution. The union is per shard; nothing needs to know which scenario covered which line.
- Changing the JS half. Playwright E2E JS coverage (#339, #343, flag `e2e-playwright`) is done and untouched.
- Enabling coverage on PRs. Nightly + `carryforward` is the agreed cadence.

## Risks / open items

- **PCOV's own floor may still be too slow.** If Gate 1 shows the request path is still materially above baseline after all userland work is removed, the remaining lever is pinning the fpm pool to one worker and flushing per scenario — a larger change, deliberately deferred rather than designed in now.
- **`\pcov\waiting()` volume.** `waiting()` returns every file PCOV has seen since the last `clear()`. With `pcov.directory` scoping to `src/` this should be bounded, but it is part of what Gate 1 measures.
- **Static-analysis cache warmth.** The merge writes `cacheStaticAnalysis` into `var/`; the first nightly pays a cold cache. Acceptable — it is once per shard, offline.
- **Merge memory.** The union is one associative array over `src/`. Expected to be modest, but the merge already runs with `memory_limit=-1` in the container and should keep doing so.
