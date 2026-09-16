# Behat coverage raw-collect rework — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove all `php-code-coverage` work from the per-HTTP-request path so the nightly `e2e-behat` Codecov flag can be switched back on without the ~5.1x shard slowdown that got it disabled on 2026-07-28.

**Architecture:** *The request records, the merge computes.* Each request calls PCOV directly and appends a compact hit-line record to a per-PID file. All `CodeCoverage` construction, the `src/**` `Filter`, static analysis and Clover rendering move to the once-per-shard merge, where they run exactly once instead of thousands of times.

**Tech Stack:** PHP 8.3/8.4, Symfony event subscriber, ext-pcov (runtime only, never installed in dev), vendored `phpunit/php-code-coverage` **10.1.16**, PHPUnit 10.5, GitHub Actions, Codecov.

## Global Constraints

- Work in the worktree `~/claude-worktrees/pim-community-dev/behat-cov-raw-collect`, branch `c1/behat-coverage-raw-collect`. Do **not** work in `/home/gumiranda/pim-community-dev` (31 commits behind; root-owned Docker artifacts block checkout).
- **No new composer dependencies.** Reuse the vendored `phpunit/php-code-coverage` 10.1.16 only.
- **PCOV is never installed in dev or in the unit-test job.** Every class must be unit-testable with `ext-pcov` absent. Reach `pcov\*` functions only through the guarded helper defined in Task 2 — never write a bare `\pcov\collect()` call, it fails static analysis and fatals locally.
- All code lives under `tests/legacy/features/Behat/Coverage/`, namespace `Pim\Behat\Coverage` (autoload-dev — never loads in prod).
- Tests in that directory are already wired into the `PHPUnit_Unit_Test` testsuite via `phpunit.xml.dist:122`. New `*Test.php` files there are picked up automatically; no config change needed.
- **Best-effort everywhere.** A coverage fault must never change a scenario outcome and must never fail a CI job: `try/catch (\Throwable)` in the subscriber, `exit 0` in the merge CLI.
- The merge-time `Filter` must mirror `phpunit.xml.dist`'s `<source>` excludes exactly: suffixes `Test.php`, `Integration.php`, `EndToEnd.php`.
- Coverage stays **nightly-only**: `schedule || workflow_dispatch`. `codecov.yml` already carries `carryforward: true` on `e2e-behat`; do not change it.
- Run tests with: `APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter <TestClassName>`
- Benchmarks for this job: PCOV-off baseline **7.6 min/shard**, broken state **38.4 min/shard**, both on the **`nightly`** suite. PRs run a different (smaller) suite *and* PCOV off — never benchmark a PR against a dispatch.

## Prerequisite: install dependencies in the worktree

The worktree was created by `git worktree add` and has **no `vendor/`** — only a `node_modules` symlink. Every test command below loads `vendor/autoload.php`, so this must run first, once:

```bash
cd ~/claude-worktrees/pim-community-dev/behat-cov-raw-collect
docker-compose run --rm --user "$(id -u):$(id -g)" -e COMPOSER_HOME=/tmp \
  php composer install --no-interaction --no-progress
test -f vendor/phpunit/php-code-coverage/src/CodeCoverage.php && echo "vendor OK"
```

Expected: `vendor OK`. The `--user` and `COMPOSER_HOME` flags avoid the root-owned-artifact problem that already blocks `git checkout` in the main clone.

---

## File Structure

| File | Responsibility | Action |
| --- | --- | --- |
| `RawCoverageRecorder.php` | Pure data: reduce a raw PCOV map to hit-lines, encode/decode length-prefixed records, union records. Zero I/O, zero PCOV — fully unit-testable. | Create |
| `CoverageCollector.php` | PCOV lifecycle + append one encoded record per request to `<dir>/<pid>.dump`. | Rewrite |
| `CoverageCollectorInterface.php` | `start()` / `stopAndDump(string $dir)` contract. | Unchanged |
| `BehatCoverageSubscriber.php` | `kernel.request` gate + `register_shutdown_function`. | Unchanged |
| `CoverageMerger.php` | Union `*.dump` records, build the `src/**` Filter, one `append()` with static-analysis caching, render Clover. | Rewrite |
| `FakeCoverageDriver.php` | Driver-free `Driver`; promoted from test fixture to the production merge path. | Unchanged |
| `merge-behat-coverage.php` | CLI over `CoverageMerger`; always `exit 0`; loud warnings on empty input **and** on a zero-line merge. | Rewrite |
| `RawCoverageRecorderTest.php` | Unit tests for the pure layer. | Create |
| `CoverageCollectorTest.php` | Assert a decodable `.dump` record is appended, with PCOV absent. | Rewrite |
| `CoverageMergerTest.php` | Union, both halves of the denominator (untouched files + unhit lines of touched files), Clover smoke. | Rewrite |
| `.github/workflows/ci.yml` | Re-enable `PHP_INI_SCAN_DIR` nightly; add the Gate 1 dump-size diagnostic. | Modify |

**Record format:** each request appends `pack('N', strlen($gz)) . $gz` where `$gz = gzencode(serialize($hits))` and `$hits` is `array<string $file, array<int $line, int 1>>`. Every fpm worker writes its own `<pid>.dump`, so records never interleave between processes, and requests within one worker are sequential. That shape is *already* what `RawCodeCoverageData::fromXdebugWithoutPathCoverage()` consumes, so the merge needs no conversion step.

---

### Task 1: RawCoverageRecorder — the pure layer

**Files:**
- Create: `tests/legacy/features/Behat/Coverage/RawCoverageRecorder.php`
- Test: `tests/legacy/features/Behat/Coverage/RawCoverageRecorderTest.php`

**Interfaces:**
- Consumes: nothing.
- Produces:
  - `RawCoverageRecorder::reduce(array $rawPcov): array` — `array<string, array<int,int>>` in, same shape out, keeping only lines with a hit count `> 0` and normalising every value to `1`.
  - `RawCoverageRecorder::encode(array $hits): string` — one length-prefixed gzipped record.
  - `RawCoverageRecorder::decodeAll(string $blob): array` — `list<array<string, array<int,int>>>`, tolerant of a truncated trailing record.
  - `RawCoverageRecorder::union(array $accumulator, array $record): array` — merged `array<string, array<int,int>>`.

- [ ] **Step 1: Write the failing test**

Create `tests/legacy/features/Behat/Coverage/RawCoverageRecorderTest.php`:

```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;

final class RawCoverageRecorderTest extends TestCase
{
    public function test_reduce_keeps_only_executed_lines(): void
    {
        // php-code-coverage Driver markers: 1 = LINE_EXECUTED, -1 = LINE_NOT_EXECUTED,
        // -2 = LINE_NOT_EXECUTABLE.
        $raw = [
            '/srv/pim/src/A.php' => [3 => 1, 4 => -1, 5 => 1, 6 => -2],
            '/srv/pim/src/B.php' => [10 => -1],
        ];

        // Line 4 was executable but never run and 6 is not executable: neither is kept, because
        // CoverageMerger::backfillExecutableLines() re-adds each touched file's executable-line
        // skeleton at merge time. Dropping the -1 markers here is safe ONLY because of that
        // backfill — without it a touched file's denominator would collapse to its own hit set and
        // every partially-covered file would report 100%. B.php has no executed line at all, so the
        // file disappears entirely and the Filter rescues it via addUncoveredFilesFromFilter().
        self::assertSame(
            ['/srv/pim/src/A.php' => [3 => 1, 5 => 1]],
            RawCoverageRecorder::reduce($raw),
        );
    }

    public function test_reduce_normalises_a_hit_count_above_one_to_exactly_one(): void
    {
        // Not cosmetic. ProcessedCodeCoverageData::markCodeAsExecutedByTestCase compares with
        // `$v === Driver::LINE_EXECUTED` — strict identity against int 1 — so a line reported as
        // executed 7 times would be silently dropped from the report if passed through as-is.
        self::assertSame(
            ['/srv/pim/src/A.php' => [3 => 1]],
            RawCoverageRecorder::reduce(['/srv/pim/src/A.php' => [3 => 7]]),
        );
    }

    public function test_reduce_returns_an_empty_array_when_nothing_was_executed(): void
    {
        self::assertSame([], RawCoverageRecorder::reduce(['/srv/pim/src/A.php' => [3 => -1]]));
    }

    public function test_encoded_records_round_trip_when_appended_to_one_blob(): void
    {
        $first = ['/srv/pim/src/A.php' => [3 => 1]];
        $second = ['/srv/pim/src/B.php' => [9 => 1, 11 => 1]];

        $blob = RawCoverageRecorder::encode($first) . RawCoverageRecorder::encode($second);

        self::assertSame([$first, $second], RawCoverageRecorder::decodeAll($blob));
    }

    public function test_decode_all_ignores_a_truncated_trailing_record(): void
    {
        // An fpm worker killed mid-write leaves a partial record; the merge must not lose
        // the complete records that precede it.
        $good = RawCoverageRecorder::encode(['/srv/pim/src/A.php' => [3 => 1]]);
        $truncated = substr(RawCoverageRecorder::encode(['/srv/pim/src/B.php' => [9 => 1]]), 0, 6);

        self::assertSame(
            [['/srv/pim/src/A.php' => [3 => 1]]],
            RawCoverageRecorder::decodeAll($good . $truncated),
        );
    }

    public function test_decode_all_returns_nothing_for_an_empty_blob(): void
    {
        self::assertSame([], RawCoverageRecorder::decodeAll(''));
    }

    public function test_union_merges_lines_across_files_and_records(): void
    {
        $accumulator = RawCoverageRecorder::union([], ['/srv/pim/src/A.php' => [3 => 1]]);
        $accumulator = RawCoverageRecorder::union($accumulator, ['/srv/pim/src/A.php' => [5 => 1]]);
        $accumulator = RawCoverageRecorder::union($accumulator, ['/srv/pim/src/B.php' => [9 => 1]]);

        self::assertSame(
            [
                '/srv/pim/src/A.php' => [3 => 1, 5 => 1],
                '/srv/pim/src/B.php' => [9 => 1],
            ],
            $accumulator,
        );
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

```bash
cd ~/claude-worktrees/pim-community-dev/behat-cov-raw-collect
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter RawCoverageRecorderTest
```

Expected: FAIL — `Class "Pim\Behat\Coverage\RawCoverageRecorder" not found`.

- [ ] **Step 3: Write the implementation**

Create `tests/legacy/features/Behat/Coverage/RawCoverageRecorder.php`:

```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

/**
 * The pure, I/O-free and PCOV-free half of the Behat coverage collector.
 *
 * Everything here runs inside an HTTP request that a Behat scenario is waiting on, so it is
 * deliberately dumb: array reduction plus one gzip. No php-code-coverage object is built, no source
 * file is parsed, no filter is applied. All of that happens once, offline, in CoverageMerger --
 * doing it per request is what cost ~5.1x and got the nightly flag disabled on 2026-07-28.
 *
 * The reduced shape -- array<string $file, array<int $line, int 1>> -- is chosen to be exactly what
 * RawCodeCoverageData::fromXdebugWithoutPathCoverage() consumes, so the merge needs no conversion.
 */
final class RawCoverageRecorder
{
    /**
     * Records are length-prefixed so many of them can be appended to a single per-worker file and
     * read back unambiguously: 4-byte big-endian length, then that many bytes of payload.
     */
    private const LENGTH_BYTES = 4;

    /**
     * Drop everything PCOV reports that is not an actual execution, and normalise hit counts to 1.
     *
     * PCOV reports Driver::LINE_NOT_EXECUTED (-1) for an executable line that was not run and
     * LINE_NOT_EXECUTABLE (-2) otherwise. Dropping both is what makes the per-request record small.
     *
     * Dropping -1 here is safe ONLY because CoverageMerger::backfillExecutableLines() puts the
     * executable-line skeleton back for every file that survives the filter. Without that backfill a
     * partially-covered file would reach the report with a denominator equal to its own hit set and
     * render at exactly 100%: CodeCoverage's addUncoveredFilesFromFilter() rescues only files with
     * ZERO hits, so it does not cover this case. The Filter alone is not a sufficient denominator --
     * it accounts for untouched files, not for the unhit lines of touched ones.
     *
     * Normalising to exactly 1 is required, not cosmetic:
     * ProcessedCodeCoverageData::markCodeAsExecutedByTestCase compares with
     * `$v === Driver::LINE_EXECUTED`, a strict identity against int 1, so any other positive count
     * would be silently discarded from the report.
     *
     * @param array<string, array<int, int>> $rawPcov
     *
     * @return array<string, array<int, int>>
     */
    public static function reduce(array $rawPcov): array
    {
        $hits = [];

        foreach ($rawPcov as $file => $lines) {
            $executed = [];

            foreach ($lines as $line => $count) {
                if ($count > 0) {
                    $executed[$line] = 1;
                }
            }

            if ($executed !== []) {
                $hits[$file] = $executed;
            }
        }

        return $hits;
    }

    /**
     * @param array<string, array<int, int>> $hits
     */
    public static function encode(array $hits): string
    {
        $payload = \gzencode(\serialize($hits), 1);

        return \pack('N', \strlen($payload)) . $payload;
    }

    /**
     * Decode every complete record in a blob, ignoring a truncated tail.
     *
     * A truncated tail is expected, not exceptional: php-fpm can kill a worker mid-write, and the
     * merge must still keep every complete record written before it.
     *
     * @return list<array<string, array<int, int>>>
     */
    public static function decodeAll(string $blob): array
    {
        $records = [];
        $offset = 0;
        $total = \strlen($blob);

        while ($offset + self::LENGTH_BYTES <= $total) {
            /** @var array{1: int}|false $header */
            $header = \unpack('N', \substr($blob, $offset, self::LENGTH_BYTES));

            if ($header === false) {
                break;
            }

            $length = $header[1];
            $offset += self::LENGTH_BYTES;

            if ($length <= 0 || $offset + $length > $total) {
                break; // truncated tail
            }

            $payload = @\gzdecode(\substr($blob, $offset, $length));
            $offset += $length;

            if ($payload === false) {
                continue;
            }

            $record = @\unserialize($payload, ['allowed_classes' => false]);

            if (\is_array($record)) {
                /** @var array<string, array<int, int>> $record */
                $records[] = $record;
            }
        }

        return $records;
    }

    /**
     * @param array<string, array<int, int>> $accumulator
     * @param array<string, array<int, int>> $record
     *
     * @return array<string, array<int, int>>
     */
    public static function union(array $accumulator, array $record): array
    {
        foreach ($record as $file => $lines) {
            // `+` on int-keyed arrays keeps the left operand for duplicate keys. Every value is 1,
            // so which side wins is irrelevant and this is markedly faster than array_merge.
            $accumulator[$file] = isset($accumulator[$file]) ? $accumulator[$file] + $lines : $lines;
        }

        return $accumulator;
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter RawCoverageRecorderTest
```

Expected: PASS, 7 tests.

Note: PHPUnit prints `OK, but there were issues! … PHPUnit Deprecations: 1` on **every** run in this repo. It is a named-argument deprecation from `tests/back/Tool/Integration/Connector/Writer/File/CsvWriterIntegration.php:1017`, emitted while PHPUnit builds the test-suite tree — before `--filter` narrows anything — so it is unrelated to this plan and appears no matter which class you target. Do not chase it.

- [ ] **Step 5: Commit**

```bash
git add tests/legacy/features/Behat/Coverage/RawCoverageRecorder.php \
        tests/legacy/features/Behat/Coverage/RawCoverageRecorderTest.php
git commit -m "feat(behat-coverage): RawCoverageRecorder — pure hit-line reduce/encode/union

The I/O-free, PCOV-free half of the collector. Reduces a raw PCOV map to executed
lines only and encodes length-prefixed gzip records that append safely to one
per-worker file. Its output shape is already what
RawCodeCoverageData::fromXdebugWithoutPathCoverage() consumes, so the merge needs
no conversion. decodeAll tolerates a truncated tail because php-fpm can kill a
worker mid-write."
```

---

### Task 2: Rewrite CoverageCollector on top of PCOV directly

**Files:**
- Modify: `tests/legacy/features/Behat/Coverage/CoverageCollector.php` (full rewrite)
- Modify: `tests/legacy/features/Behat/Coverage/CoverageCollectorTest.php` (full rewrite)

**Interfaces:**
- Consumes: `RawCoverageRecorder::reduce()`, `RawCoverageRecorder::encode()` from Task 1.
- Produces:
  - `CoverageCollector::create(): self` — the production factory the subscriber calls. Unchanged signature.
  - `new CoverageCollector(?callable $collect)` — `$collect` returns `array<string, array<int,int>>`; injecting it is how the class is tested with PCOV absent.
  - Dump files named `<dir>/<pid>.dump`, readable by `RawCoverageRecorder::decodeAll()`.
  - Still implements `CoverageCollectorInterface`, so `BehatCoverageSubscriber` needs no change.

- [ ] **Step 1: Write the failing test**

Replace `tests/legacy/features/Behat/Coverage/CoverageCollectorTest.php` entirely:

```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;

final class CoverageCollectorTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/behatcoll-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->dir);
    }

    public function test_it_appends_a_decodable_record_named_after_the_worker_pid(): void
    {
        $collector = new CoverageCollector(static fn (): array => [
            '/srv/pim/src/A.php' => [3 => 1, 4 => 0],
        ]);

        $collector->start();          // inert without PCOV
        $collector->stopAndDump($this->dir); // creates the dir if missing

        $expected = $this->dir . '/' . getmypid() . '.dump';
        self::assertFileExists($expected);

        self::assertSame(
            [['/srv/pim/src/A.php' => [3 => 1]]],
            RawCoverageRecorder::decodeAll((string) file_get_contents($expected)),
        );
    }

    public function test_successive_requests_in_one_worker_append_to_the_same_file(): void
    {
        $maps = [
            ['/srv/pim/src/A.php' => [3 => 1]],
            ['/srv/pim/src/B.php' => [9 => 1]],
        ];
        $collector = new CoverageCollector(static function () use (&$maps): array {
            return array_shift($maps) ?? [];
        });

        $collector->stopAndDump($this->dir);
        $collector->stopAndDump($this->dir);

        self::assertCount(1, glob($this->dir . '/*.dump') ?: []);
        self::assertSame(
            [
                ['/srv/pim/src/A.php' => [3 => 1]],
                ['/srv/pim/src/B.php' => [9 => 1]],
            ],
            RawCoverageRecorder::decodeAll((string) file_get_contents($this->dir . '/' . getmypid() . '.dump')),
        );
    }

    public function test_it_writes_nothing_when_no_line_was_executed(): void
    {
        // A request that touches no src/ code must not leave an empty record for the merge to read.
        $collector = new CoverageCollector(static fn (): array => ['/srv/pim/src/A.php' => [3 => 0]]);

        $collector->stopAndDump($this->dir);

        self::assertSame([], glob($this->dir . '/*.dump') ?: []);
    }

    public function test_it_is_inert_when_pcov_is_not_collecting(): void
    {
        // Covers the real production factory and the real PCOV path, which must degrade quietly
        // rather than fatal or throw.
        //
        // Deliberately does NOT assert on extension_loaded('pcov'): the answer differs by
        // environment. The CI image installs php-pcov with pcov.enabled=0 (Dockerfile:80,
        // docker/build/pcov.ini), so the extension IS loaded there and merely disabled, while a
        // stale local image may not have it at all. Both cases must behave identically — nothing
        // was collected, so nothing is written — and asserting the environment instead of the
        // behaviour would make this test pass locally and fail in CI.
        $collector = CoverageCollector::create();
        $collector->start();
        $collector->stopAndDump($this->dir);

        self::assertSame([], glob($this->dir . '/*.dump') ?: []);
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter CoverageCollectorTest
```

Expected: FAIL — the current constructor requires a `CodeCoverage`, so `new CoverageCollector(static fn () => [...])` raises a `TypeError`.

- [ ] **Step 3: Write the implementation**

Replace `tests/legacy/features/Behat/Coverage/CoverageCollector.php` entirely:

```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

/**
 * Records PCOV line coverage for one HTTP request and appends it to a per-worker dump file.
 *
 * Deliberately holds NO php-code-coverage object. The previous implementation built a CodeCoverage
 * per request and called stop() on it, which runs append() -> applyExecutableLinesFilter() ->
 * ParsingFileAnalyser: a nikic/php-parser parse of every file the request touched, uncached because
 * cacheStaticAnalysis() was never called. Measured on the nightly suite that cost 38.4 min/shard
 * against a 7.6 min baseline. With the collector reduced to PCOV's own lifecycle, a shard ran
 * 7.5 min -- i.e. PCOV itself is free here and the whole overhead was that userland work
 * (run 30453503181 vs 30425913943, 2026-07-29).
 *
 * Every fpm worker writes its own <pid>.dump, so records never interleave between processes, and
 * requests within a worker are sequential -- appends need no locking.
 */
final class CoverageCollector implements CoverageCollectorInterface
{
    /**
     * Returns a raw PCOV map, array<string $file, array<int $line, int $hits>>.
     *
     * Injectable because ext-pcov is a runtime-only extension: it is absent from every dev checkout
     * and from the unit-test job, so the write path would otherwise be untestable.
     *
     * @var (callable(): array<string, array<int, int>>)|null
     */
    private $collect;

    /**
     * @param (callable(): array<string, array<int, int>>)|null $collect
     */
    public function __construct(?callable $collect = null)
    {
        $this->collect = $collect;
    }

    public static function create(): self
    {
        return new self();
    }

    public function start(): void
    {
        self::pcov('start');
    }

    public function stopAndDump(string $dir): void
    {
        $raw = $this->collect !== null ? ($this->collect)() : self::collectFromPcov();
        $hits = RawCoverageRecorder::reduce($raw);

        if ($hits === []) {
            return; // a request that executed no src/ line leaves no record to merge
        }

        if (!\is_dir($dir)) {
            @\mkdir($dir, 0o777, true);
        }

        @\file_put_contents(
            $dir . '/' . \getmypid() . '.dump',
            RawCoverageRecorder::encode($hits),
            \FILE_APPEND,
        );
    }

    /**
     * @return array<string, array<int, int>>
     */
    private static function collectFromPcov(): array
    {
        self::pcov('stop');

        /** @var list<string>|null $waiting */
        $waiting = self::pcov('waiting');

        if (!\is_array($waiting) || $waiting === []) {
            return [];
        }

        // No intersect against a src/** Filter here: pcov.directory=/srv/pim/src (docker/build/pcov.ini)
        // already scopes collection in C, and the previous userland array_intersect against a Filter
        // holding every path under src/ ran on every single request. Test-file exclusion is applied
        // once, at merge time, by CoverageMerger::sourceFilter().
        $inclusive = \defined('pcov\inclusive') ? \constant('pcov\inclusive') : 1;

        /** @var array<string, array<int, int>>|null $raw */
        $raw = self::pcov('collect', [$inclusive, $waiting]);

        self::pcov('clear');

        return \is_array($raw) ? $raw : [];
    }

    /**
     * Call a `pcov\*` function through a variable so neither PHPStan nor the IDE flags it as
     * undefined -- PCOV is a runtime-only extension, absent from dev checkouts and from the image on
     * non-coverage runs. function_exists() makes every call a no-op when PCOV is missing, which
     * matters because this class and the subscriber's gate can in principle disagree.
     *
     * @param list<mixed> $args
     */
    private static function pcov(string $function, array $args = []): mixed
    {
        $callable = '\pcov\\' . $function;

        return \function_exists($callable) ? $callable(...$args) : null;
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter CoverageCollectorTest
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter BehatCoverageSubscriberTest
```

Expected: both PASS. `BehatCoverageSubscriberTest` is untouched and must stay green — it proves the subscriber contract survived the rewrite.

- [ ] **Step 5: Commit**

```bash
git add tests/legacy/features/Behat/Coverage/CoverageCollector.php \
        tests/legacy/features/Behat/Coverage/CoverageCollectorTest.php
git commit -m "feat(behat-coverage): collector calls PCOV directly, no php-code-coverage per request

Drops CodeCoverage/Selector/Filter/Report\\PHP from the request path entirely and
appends a compact hit-line record to <dir>/<pid>.dump instead. Also drops the
per-request array_intersect against the src/** Filter -- pcov.directory already
scopes collection in C, and test-file exclusion moves to merge time.

The raw-collect callable is injectable so the write path is unit-testable with
ext-pcov absent, which it always is outside the nightly."
```

---

### Task 3: Rewrite CoverageMerger to union raw records

**Files:**
- Modify: `tests/legacy/features/Behat/Coverage/CoverageMerger.php` (full rewrite)
- Modify: `tests/legacy/features/Behat/Coverage/CoverageMergerTest.php` (full rewrite)

**Interfaces:**
- Consumes: `RawCoverageRecorder::decodeAll()`, `RawCoverageRecorder::union()` (Task 1); `<pid>.dump` files written by Task 2; the existing `FakeCoverageDriver`.
- Produces:
  - `CoverageMerger::unionDir(string $dir): array` — `array<string, array<int,int>>`, empty when there is nothing to merge.
  - `CoverageMerger::sourceFilter(string $srcDir): Filter`
  - `CoverageMerger::toCodeCoverage(array $union, Filter $filter, ?string $cacheDir): CodeCoverage`
  - `CoverageMerger::writeClover(CodeCoverage $coverage, string $path): void` — unchanged signature.

- [ ] **Step 1: Write the failing test**

Replace `tests/legacy/features/Behat/Coverage/CoverageMergerTest.php` entirely:

```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;

final class CoverageMergerTest extends TestCase
{
    private string $dir;
    private string $srcDir;
    private string $covered;
    private string $untouched;
    private string $excluded;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/behatcov-' . uniqid('', true);
        $this->srcDir = $this->dir . '/src';
        mkdir($this->srcDir, 0o777, true);

        $body = <<<'PHP'
        <?php
        function fixture_target($x)
        {
            $a = $x + 1;
            if ($a > 0) {
                return $a;
            }
            return 0;
        }
        PHP;

        // Executable statements sit on lines 4, 5, 6 and 8 -- verified against ParsingFileAnalyser,
        // which is what decides the denominator. Four statements in total.
        $this->covered = $this->srcDir . '/Covered.php';
        $this->untouched = $this->srcDir . '/Untouched.php';
        $this->excluded = $this->srcDir . '/ThingTest.php';

        file_put_contents($this->covered, $body);
        file_put_contents($this->untouched, str_replace('fixture_target', 'untouched_target', $body));
        file_put_contents($this->excluded, str_replace('fixture_target', 'excluded_target', $body));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->srcDir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->srcDir);
        @rmdir($this->dir);
    }

    public function test_it_unions_records_across_several_worker_dumps(): void
    {
        file_put_contents(
            $this->dir . '/111.dump',
            RawCoverageRecorder::encode([$this->covered => [4 => 1]]),
        );
        file_put_contents(
            $this->dir . '/222.dump',
            RawCoverageRecorder::encode([$this->covered => [6 => 1]])
            . RawCoverageRecorder::encode([$this->untouched => [4 => 1]]),
        );

        self::assertSame(
            [
                $this->covered => [4 => 1, 6 => 1],
                $this->untouched => [4 => 1],
            ],
            (new CoverageMerger())->unionDir($this->dir),
        );
    }

    public function test_union_dir_is_empty_when_there_are_no_dumps(): void
    {
        self::assertSame([], (new CoverageMerger())->unionDir($this->dir));
    }

    public function test_the_filter_includes_sources_and_excludes_test_suffixes(): void
    {
        $files = (new CoverageMerger())->sourceFilter($this->srcDir)->files();

        self::assertContains($this->covered, $files);
        self::assertContains($this->untouched, $files);
        self::assertNotContains($this->excluded, $files, 'ThingTest.php must not inflate the denominator');
    }

    public function test_untouched_sources_still_count_towards_the_denominator(): void
    {
        // The whole point of keeping a Filter at merge time: a file no request ever hit must appear
        // as 0%, not vanish. Without it the report degenerates towards a falsely high percentage --
        // the failure #343 fixed on the Playwright side.
        //
        // This covers UNTOUCHED files only, and asserts paths rather than counts. The unhit lines of
        // a touched file are a separate mechanism and a separate test:
        // test_a_partially_covered_file_keeps_its_unhit_executable_lines.
        $merger = new CoverageMerger();
        $filter = $merger->sourceFilter($this->srcDir);
        $coverage = $merger->toCodeCoverage([$this->covered => [4 => 1]], $filter, null);

        $clover = $this->dir . '/clover.xml';
        $merger->writeClover($coverage, $clover);

        $xml = (string) file_get_contents($clover);
        self::assertStringContainsString($this->covered, $xml);
        self::assertStringContainsString($this->untouched, $xml);
        self::assertStringNotContainsString($this->excluded, $xml);
    }

    public function test_a_partially_covered_file_keeps_its_unhit_executable_lines(): void
    {
        // Covered.php has executable lines 4, 5, 6 and 8. Hitting only line 4 must read 1/4, not 1/1.
        // Without the merge-side skeleton backfill the denominator collapses to the hit set and every
        // partially-covered file reports 100% — which, in an E2E run, is almost every touched file.
        $merger = new CoverageMerger();
        $coverage = $merger->toCodeCoverage(
            [$this->covered => [4 => 1]],
            $merger->sourceFilter($this->srcDir),
            null,
        );
        $merger->writeClover($coverage, $clover = $this->dir . '/clover.xml');

        $xml = new \SimpleXMLElement((string) file_get_contents($clover));
        $file = $xml->xpath(sprintf('//file[@name="%s"]', $this->covered))[0];
        self::assertSame('4', (string) $file->metrics['statements']);
        self::assertSame('1', (string) $file->metrics['coveredstatements']);
    }

    public function test_it_reports_the_union_as_covered_lines(): void
    {
        $merger = new CoverageMerger();
        $coverage = $merger->toCodeCoverage(
            [$this->covered => [4 => 1, 6 => 1]],
            $merger->sourceFilter($this->srcDir),
            null,
        );

        $lineCoverage = $coverage->getData()->lineCoverage();
        self::assertArrayHasKey($this->covered, $lineCoverage);
        self::assertNotEmpty($lineCoverage[$this->covered][4] ?? []);
        self::assertNotEmpty($lineCoverage[$this->covered][6] ?? []);
    }

    public function test_static_analysis_cache_directory_is_used_when_given(): void
    {
        $cache = $this->dir . '/sa-cache';
        $merger = new CoverageMerger();
        $coverage = $merger->toCodeCoverage(
            [$this->covered => [4 => 1]],
            $merger->sourceFilter($this->srcDir),
            $cache,
        );
        $merger->writeClover($coverage, $this->dir . '/clover.xml');

        self::assertTrue($coverage->cachesStaticAnalysis());
        self::assertDirectoryExists($cache);
        self::assertNotSame([], glob($cache . '/*') ?: [], 'the analyser should have written cache entries');

        // One entry per analysed file and no more: Covered.php (touched, so the backfill analyses it
        // and append() then re-reads it) and Untouched.php (added by addUncoveredFilesFromFilter()
        // during writeClover()). ThingTest.php is filtered out and never parsed.
        //
        // This is the guard on backfillExecutableLines()'s (true, false) flags. CachingFileAnalyser
        // keys its cache on both (CachingFileAnalyser.php:147-161), so if the backfill's analyser
        // ever stops matching CodeCoverage's own defaults, Covered.php lands under two different keys
        // and this count becomes 3 -- the silent double-parse the flags exist to prevent.
        self::assertCount(
            2,
            glob($cache . '/*') ?: [],
            'the backfill and append() must share cache entries, not write a set each',
        );

        foreach (glob($cache . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($cache);
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter CoverageMergerTest
```

Expected: FAIL — `Call to undefined method Pim\Behat\Coverage\CoverageMerger::unionDir()`.

- [ ] **Step 3: Write the implementation**

Replace `tests/legacy/features/Behat/Coverage/CoverageMerger.php` entirely:

```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Clover;
use SebastianBergmann\CodeCoverage\StaticAnalysis\CachingFileAnalyser;
use SebastianBergmann\CodeCoverage\StaticAnalysis\FileAnalyser;
use SebastianBergmann\CodeCoverage\StaticAnalysis\ParsingFileAnalyser;

/**
 * Turns the per-request hit-line dumps into one Clover report.
 *
 * This is where every expensive step lives, on purpose: building the src/** Filter, constructing a
 * CodeCoverage, and running php-code-coverage's static analysis to resolve executable lines. Each
 * happens ONCE per shard here instead of once per HTTP request in the collector, which is the whole
 * point of the rework.
 */
final class CoverageMerger
{
    /**
     * Union every complete record in every worker dump in a directory.
     *
     * @return array<string, array<int, int>>
     */
    public function unionDir(string $dir): array
    {
        $union = [];

        foreach (\glob(\rtrim($dir, '/') . '/*.dump') ?: [] as $file) {
            $blob = @\file_get_contents($file);

            if ($blob === false) {
                continue;
            }

            foreach (RawCoverageRecorder::decodeAll($blob) as $record) {
                $union = RawCoverageRecorder::union($union, $record);
            }
        }

        return $union;
    }

    /**
     * The coverage allowlist, which is also the denominator.
     *
     * Mirrors phpunit.xml.dist's <source> excludes so the e2e-behat flag measures the same body of
     * code as the backend flag. Excluding test classes matters because they run at ~100% and would
     * otherwise flatter the number.
     */
    public function sourceFilter(string $srcDir): Filter
    {
        $filter = new Filter();
        $filter->includeDirectory($srcDir);

        foreach ($filter->files() as $file) {
            if (\str_ends_with($file, 'Test.php')
                || \str_ends_with($file, 'Integration.php')
                || \str_ends_with($file, 'EndToEnd.php')
            ) {
                $filter->excludeFile($file);
            }
        }

        return $filter;
    }

    /**
     * @param array<string, array<int, int>> $union
     * @param string|null                    $cacheDir static-analysis cache; null disables caching
     */
    public function toCodeCoverage(array $union, Filter $filter, ?string $cacheDir): CodeCoverage
    {
        // FakeCoverageDriver rather than a real driver: nothing is ever started here, the raw data
        // is handed straight to append(). That also lets the merge run in a container without PCOV.
        $coverage = new CodeCoverage(new FakeCoverageDriver(), $filter);

        if ($cacheDir !== null) {
            if (!\is_dir($cacheDir)) {
                @\mkdir($cacheDir, 0o777, true);
            }

            // Without this, append() resolves executable lines through a bare ParsingFileAnalyser
            // (CodeCoverage.php:609) and re-parses every covered file on every run.
            $coverage->cacheStaticAnalysis($cacheDir);
        }

        $union = $this->backfillExecutableLines($union, $filter, $cacheDir);

        // The single append. Together with the backfill above it is the whole of the static analysis:
        // both share one CachingFileAnalyser cache, so each file is parsed at most once per shard.
        $coverage->append(RawCodeCoverageData::fromXdebugWithoutPathCoverage($union), 'behat');

        return $coverage;
    }

    /**
     * Restore the executable-line skeleton that the recorder dropped.
     *
     * RawCoverageRecorder::reduce() keeps hits only, so a file some request touched arrives here
     * carrying nothing but hit lines. append() would then build that file's denominator from those
     * keys alone and render it at exactly 100%. CodeCoverage's own rescue, addUncoveredFilesFromFilter()
     * (CodeCoverage.php:476), does not help: it diffs the Filter against the ALREADY-COVERED files, so
     * it only ever rescues files with zero hits. In an E2E run nearly every touched file is partially
     * covered, which is precisely the population that would be misreported.
     *
     * The alternative -- shipping the -1 markers from every request -- is equally correct but inflates
     * the dump volume and pushes parse work back into the request path, the two things this rework
     * exists to avoid. Doing it here costs one extra analyser pass per touched file, cached.
     *
     * @param array<string, array<int, int>> $union
     *
     * @return array<string, array<int, int>>
     */
    private function backfillExecutableLines(array $union, Filter $filter, ?string $cacheDir): array
    {
        $analyser = $this->fileAnalyser($cacheDir);

        foreach ($union as $file => $hits) {
            // isExcluded() is the same allowlist gate append() applies (CodeCoverage.php:422), so the
            // backfill tracks exactly the files that will survive into the report -- and because it
            // delegates to isFile(), it also keeps runtime-evaluated pseudo-files (Filter.php:92-100)
            // and files that have since vanished from disk out of the parser.
            if ($filter->isExcluded($file)) {
                continue;
            }

            $skeleton = RawCodeCoverageData::fromUncoveredFile($file, $analyser)->lineCoverage()[$file] ?? [];

            // `+` on int-keyed arrays keeps the LEFT operand for duplicate keys, so a hit (1) wins
            // over the skeleton's LINE_NOT_EXECUTED (-1). array_merge would renumber the integer keys
            // and destroy the line numbers outright.
            $union[$file] = $hits + $skeleton;
        }

        return $union;
    }

    /**
     * `true, false` are CodeCoverage's own defaults for useAnnotationsForIgnoringCode and
     * ignoreDeprecatedCode (CodeCoverage.php:53,57), and both constructors below must be given the
     * same pair for two different reasons:
     *
     * - on CachingFileAnalyser they are part of the cache KEY (CachingFileAnalyser.php:147-161).
     *   Diverging from what CodeCoverage::analyser() passes means every lookup misses, so each file
     *   is parsed once for the backfill and again for append() -- silently, at twice the cost.
     *   test_static_analysis_cache_directory_is_used_when_given pins the entry count for this.
     * - on the wrapped ParsingFileAnalyser they decide the analysis SEMANTICS (whether
     *   @codeCoverageIgnore annotations are honoured). They do not reach the cache key at all, so a
     *   mismatch between the two constructors would be worse than a cache miss: results computed
     *   under one set of flags would be stored under a key claiming the other.
     */
    private function fileAnalyser(?string $cacheDir): FileAnalyser
    {
        $analyser = new ParsingFileAnalyser(true, false);

        return $cacheDir !== null
            ? new CachingFileAnalyser($cacheDir, $analyser, true, false)
            : $analyser;
    }

    public function writeClover(CodeCoverage $coverage, string $path): void
    {
        (new Clover())->process($coverage, $path);
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter CoverageMergerTest
```

Expected: PASS, 7 tests.

- [ ] **Step 5: Commit**

```bash
git add tests/legacy/features/Behat/Coverage/CoverageMerger.php \
        tests/legacy/features/Behat/Coverage/CoverageMergerTest.php
git commit -m "feat(behat-coverage): merge unions raw records and runs static analysis once

unionDir() folds every worker dump into one hit-line map; sourceFilter() rebuilds
the src/** allowlist (mirroring phpunit.xml.dist's <source> excludes) at merge
time; toCodeCoverage() does the single append() with cacheStaticAnalysis() set,
which is what the per-request path never called.

The denominator is built from two mechanisms, not one. The Filter is kept
deliberately: \$includeUncoveredFiles defaults to true and getReport() calls
addUncoveredFilesFromFilter(), so files no request touched still count as 0%
instead of vanishing. That rescue only fires for files with ZERO hits, so
backfillExecutableLines() re-adds the executable-line skeleton of every touched
file — otherwise a partially-covered file would be measured against its own hit
set and render at exactly 100%."
```

---

### Task 4: Merge CLI with an empty-result tripwire

**Files:**
- Modify: `tests/legacy/features/Behat/Coverage/merge-behat-coverage.php` (full rewrite)
- Create: `tests/legacy/features/Behat/Coverage/MergeCliTest.php`

**Interfaces:**
- Consumes: everything from Task 3.
- Produces: CLI `php merge-behat-coverage.php --in <dir> --clover <path> [--src <dir>] [--cache <dir>]`. Always exits 0. Prints `[behat-coverage] WARNING:` to STDERR when there is nothing to upload.

- [ ] **Step 1: Write the failing test**

Create `tests/legacy/features/Behat/Coverage/MergeCliTest.php`:

```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;

final class MergeCliTest extends TestCase
{
    private string $dir;
    private string $srcDir;
    private string $covered;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/behatcli-' . uniqid('', true);
        $this->srcDir = $this->dir . '/src';
        mkdir($this->srcDir, 0o777, true);

        $this->covered = $this->srcDir . '/Covered.php';
        file_put_contents($this->covered, <<<'PHP'
        <?php
        function cli_target($x)
        {
            $a = $x + 1;
            if ($a > 0) {
                return $a;
            }
            return 0;
        }
        PHP);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->srcDir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->srcDir);
        @rmdir($this->dir);
    }

    public function test_it_writes_clover_and_exits_zero_for_real_dumps(): void
    {
        file_put_contents(
            $this->dir . '/111.dump',
            RawCoverageRecorder::encode([$this->covered => [4 => 1]]),
        );
        $clover = $this->dir . '/report/clover.xml';

        [$exit, $stderr] = $this->runCli($clover);

        self::assertSame(0, $exit);
        self::assertFileExists($clover);
        self::assertStringNotContainsString('WARNING', $stderr);
    }

    public function test_it_warns_and_still_exits_zero_when_there_are_no_dumps(): void
    {
        // The signature of PCOV not being active in the fpm SAPI. Must be loud but must never fail
        // the nightly job.
        [$exit, $stderr] = $this->runCli($this->dir . '/report/clover.xml');

        self::assertSame(0, $exit);
        self::assertStringContainsString('WARNING', $stderr);
        self::assertStringContainsString('0 records', $stderr);
    }

    public function test_it_warns_when_the_merge_covers_zero_lines(): void
    {
        // Dumps exist but every path in them falls outside the filter -- e.g. a source-path mismatch
        // between the container and the merge. Exit code alone would call that a success.
        file_put_contents(
            $this->dir . '/111.dump',
            RawCoverageRecorder::encode(['/somewhere/else/Nope.php' => [4 => 1]]),
        );

        [$exit, $stderr] = $this->runCli($this->dir . '/report/clover.xml');

        self::assertSame(0, $exit);
        self::assertStringContainsString('WARNING', $stderr);
        self::assertStringContainsString('0 covered lines', $stderr);
    }

    public function test_a_dump_naming_only_non_executable_lines_trips_the_warning(): void
    {
        // A real failure mode worth locking in: the dump names ONLY line 1 (`<?php`) of a file that
        // DOES clear the --src filter. That is what stale line numbers or a wrong file revision look
        // like — the paths match, so the earlier out-of-filter test does not cover it, yet nothing
        // real was covered and the report would be empty.
        //
        // Note this test does NOT discriminate the is_array() guard in the counting loop: measured
        // against this pipeline, append() strips non-executable lines via applyExecutableLinesFilter()
        // before they are ever initialised, so no `null` state reaches the loop and both the guarded
        // and unguarded forms return 0 here. The guard is defensive; this test covers the warning.
        file_put_contents(
            $this->dir . '/111.dump',
            RawCoverageRecorder::encode([$this->covered => [1 => 1]]),
        );

        [$exit, $stderr] = $this->runCli($this->dir . '/report/clover.xml');

        self::assertSame(0, $exit);
        self::assertStringContainsString('WARNING', $stderr);
        self::assertStringContainsString('0 covered lines', $stderr);
    }

    /** @return array{0: int, 1: string} */
    private function runCli(string $clover): array
    {
        $script = __DIR__ . '/merge-behat-coverage.php';
        // Space-separated on purpose: this is exactly how ci.yml invokes the script. The test
        // previously used the `=`-attached form, so it exercised a command line production never
        // runs — and could not have caught `--cache` silently failing to bind under `getopt`'s
        // optional-argument (`::`) form. Keep these two shapes identical.
        $cmd = sprintf(
            '%s %s --in %s --clover %s --src %s 2>&1',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($script),
            escapeshellarg($this->dir),
            escapeshellarg($clover),
            escapeshellarg($this->srcDir),
        );

        exec($cmd, $output, $exit);

        return [$exit, implode("\n", $output)];
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter MergeCliTest
```

Expected: FAIL — the current CLI has no `--src` option and calls the removed `mergeDir()`.

- [ ] **Step 3: Write the implementation**

Replace `tests/legacy/features/Behat/Coverage/merge-behat-coverage.php` entirely:

```php
<?php

declare(strict_types=1);

// Thin CLI over CoverageMerger. Best-effort: ALWAYS exit 0 so it can never fail the nightly Behat
// job. Run inside the httpd container, where the vendor tree and the bind-mounted sources live.

require dirname(__DIR__, 5) . '/vendor/autoload.php';

use Pim\Behat\Coverage\CoverageMerger;

// All four use the single-colon (required-argument) form. The options themselves stay optional to
// supply — an omitted flag simply leaves its key absent — but a flag that IS supplied must carry a
// value. The double-colon form was a trap: PHP's getopt binds `::` options ONLY when the value is
// `=`-attached, so `--cache var/x` silently left the key unset, `$cacheDir` fell to null, and
// cacheStaticAnalysis() was never called. With `:`, both `--cache var/x` and `--cache=var/x` bind.
$options = getopt('', ['in:', 'clover:', 'src:', 'cache:']);
$inDir = $options['in'] ?? null;
$clover = $options['clover'] ?? null;
$srcDir = $options['src'] ?? '/srv/pim/src';
$cacheDir = $options['cache'] ?? null;

if (!is_string($inDir) || !is_string($clover)) {
    fwrite(STDERR, "[behat-coverage] usage: --in <dir> --clover <path> [--src <dir>] [--cache <dir>]\n");
    exit(0);
}

try {
    $merger = new CoverageMerger();
    $union = $merger->unionDir($inDir);

    $dumpedLines = array_sum(array_map('count', $union));

    if ($union === []) {
        fwrite(STDERR, sprintf(
            "[behat-coverage] WARNING: 0 records in %s — PCOV is most likely not active in the fpm "
            . "SAPI; nothing to upload\n",
            $inDir,
        ));
        exit(0);
    }

    $coverage = $merger->toCodeCoverage(
        $union,
        $merger->sourceFilter(is_string($srcDir) ? $srcDir : '/srv/pim/src'),
        is_string($cacheDir) ? $cacheDir : null,
    );

    // A non-empty union that survives the filter as zero covered lines means the dumped paths do not
    // match the filter's paths. Exit status alone would report that as success and Codecov would
    // ingest an empty report, so assert it explicitly and say so loudly.
    // php-code-coverage stores three distinct states per line
    // (ProcessedCodeCoverageData.php:69 — `$v === Driver::LINE_NOT_EXECUTABLE ? null : []`):
    //   null    → the line is not executable (blank, brace, `use`, declaration)
    //   []      → executable but never hit
    //   [ids…]  → covered
    // The is_array() check is DEFENSIVE, not currently load-bearing. `null !== []` is true in
    // PHP, so a bare `!== []` would count non-executable lines as covered — but measured against
    // this pipeline (probe, 2026-07-29) `null` never reaches here: append() runs
    // applyExecutableLinesFilter() before initializeUnseenData(), so non-executable lines are
    // already stripped and every surviving line is `[]` or a hit list. The guard costs nothing
    // and keeps the count correct if raw data ever arrives unfiltered.
    $coveredLines = 0;
    foreach ($coverage->getData()->lineCoverage() as $lines) {
        foreach ($lines as $tests) {
            if (is_array($tests) && $tests !== []) {
                $coveredLines++;
            }
        }
    }

    if ($coveredLines === 0) {
        fwrite(STDERR, sprintf(
            "[behat-coverage] WARNING: merged %d dumped lines across %d files but 0 covered lines "
            . "survived the %s filter — check that dumped paths match the filter's paths\n",
            $dumpedLines,
            count($union),
            is_string($srcDir) ? $srcDir : '/srv/pim/src',
        ));
    }

    if (!is_dir(dirname($clover))) {
        @mkdir(dirname($clover), 0o777, true);
    }

    $merger->writeClover($coverage, $clover);

    fwrite(STDOUT, sprintf(
        "[behat-coverage] wrote %s (%d files, %d covered lines)\n",
        $clover,
        count($union),
        $coveredLines,
    ));
} catch (\Throwable $e) {
    fwrite(STDERR, "[behat-coverage] merge failed (ignored): {$e->getMessage()}\n");
}

exit(0);
```

- [ ] **Step 4: Run the test to verify it passes**

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter MergeCliTest
```

Expected: PASS, 3 tests.

- [ ] **Step 5: Run the whole Coverage suite and commit**

```bash
# --filter is a regex over test NAMES, so list the classes explicitly rather than
# trying to match the namespace.
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . \
  --filter 'RawCoverageRecorderTest|CoverageCollectorTest|CoverageMergerTest|MergeCliTest|BehatCoverageSubscriberTest'
```

Expected: PASS — all five classes green, including the untouched `BehatCoverageSubscriberTest`.

```bash
git add tests/legacy/features/Behat/Coverage/merge-behat-coverage.php \
        tests/legacy/features/Behat/Coverage/MergeCliTest.php
git commit -m "feat(behat-coverage): merge CLI gains --src/--cache and a zero-line tripwire

Still always exits 0 so it cannot fail the nightly. Adds a second loud warning
for the case exit status cannot catch: dumps merged fine but no covered line
survived the filter, which is what a container/merge source-path mismatch looks
like. Reports file and covered-line counts on success so the CI log carries the
numbers Gate 1 needs."
```

---

### Task 5: Re-enable nightly coverage and instrument Gate 1

**Files:**
- Modify: `.github/workflows/ci.yml` — the `test-behat` job's `Setup test database` step (`env:` block, ~line 1240) and the `Merge Behat PHP coverage` step (~line 1533).

**Interfaces:**
- Consumes: the CLI from Task 4.
- Produces: `var/tests/behat-coverage-report/clover.xml`, uploaded under Codecov flag `e2e-behat` by the already-present upload step (no change needed there).

- [ ] **Step 1: Re-enable the ini toggle**

In `.github/workflows/ci.yml`, replace the `Setup test database` `env:` block (the comment block plus `PHP_INI_SCAN_DIR: ''`) with:

```yaml
      - name: Setup test database
        env:
          # Behat E2E PHP coverage (PCOV), nightly-only. An empty value is safe: the httpd service
          # defaults it to ':' (scan the compiled-in conf.d); an EMPTY scan dir would disable all
          # conf.d and 500 the app. The leading ':' means "each SAPI's own default, then this dir".
          #
          # Re-enabled 2026-07-29 after the raw-collect rework. The 2026-07-28 disable was caused by
          # per-request php-code-coverage work (uncached static analysis + full-graph serialize),
          # NOT by PCOV: measured 38.4 min/shard broken vs a 7.6 min PCOV-off baseline, and 7.5 min
          # with PCOV on but the collector reduced to a no-op. See
          # docs/superpowers/specs/2026-07-29-behat-coverage-raw-collect-design.md
          PHP_INI_SCAN_DIR: ${{ (github.event_name == 'schedule' || github.event_name == 'workflow_dispatch') && ':/srv/pim/docker/php-coverage.d' || '' }}
```

- [ ] **Step 2: Add the Gate 1 dump-size diagnostic and pass the new CLI options**

Replace the `Merge Behat PHP coverage (shard ...)` step with these two steps, keeping the existing Codecov upload step that follows untouched:

```yaml
      # Gate 1 instrumentation. Dump volume was the one number this design deliberately did not
      # guess (guessing the overhead is what sank #348), so the shard reports it every run.
      - name: Behat PHP coverage dump volume (shard ${{ matrix.shard }})
        if: ${{ github.event_name == 'schedule' || github.event_name == 'workflow_dispatch' }}
        continue-on-error: true
        run: |
          echo "dump files: $(find var/tests/behat-coverage -name '*.dump' 2>/dev/null | wc -l)"
          echo "total bytes: $(du -sb var/tests/behat-coverage 2>/dev/null | cut -f1)"
          echo "largest per-worker dumps:"
          du -b var/tests/behat-coverage/*.dump 2>/dev/null | sort -rn | head -5 || true

      - name: Merge Behat PHP coverage (shard ${{ matrix.shard }})
        if: ${{ github.event_name == 'schedule' || github.event_name == 'workflow_dispatch' }}
        continue-on-error: true
        run: |
          START=$(date +%s)
          docker-compose exec -u www-data -T httpd \
            php tests/legacy/features/Behat/Coverage/merge-behat-coverage.php \
            --in var/tests/behat-coverage \
            --clover var/tests/behat-coverage-report/clover.xml \
            --src /srv/pim/src \
            --cache var/cache/behat-coverage-sa
          echo "merge wall-clock: $(( $(date +%s) - START ))s"
```

- [ ] **Step 3: Validate the workflow parses**

```bash
cd ~/claude-worktrees/pim-community-dev/behat-cov-raw-collect
python3 -c "import yaml; yaml.safe_load(open('.github/workflows/ci.yml')); print('ci.yml OK')"
```

Expected: `ci.yml OK`.

- [ ] **Step 4: Commit and push**

```bash
git add .github/workflows/ci.yml
git commit -m "ci(behat-coverage): re-enable the nightly e2e-behat flag on the raw-collect path

Restores PHP_INI_SCAN_DIR for schedule/workflow_dispatch, passes --src and --cache
to the merge CLI so static analysis is cached between runs, and adds a dump-volume
diagnostic plus merge wall-clock so Gate 1 has real numbers instead of estimates."
git push origin c1/behat-coverage-raw-collect
```

- [ ] **Step 5: Run Gate 1 and read the numbers**

```bash
gh workflow run ci.yml --ref c1/behat-coverage-raw-collect
# then, once test-behat shards conclude (note: conclusion is "" not null while queued):
gh run view <RUN_ID> --json jobs \
  -q '.jobs[]|select(.name|test("test-behat"))|select(.conclusion|length>0)
      |"\(.name)\t\(.conclusion)\t\((((.completedAt|fromdate)-(.startedAt|fromdate))/60)|floor)min"'
```

**Acceptance:** shards land close enough to the **7.6 min** baseline that no scenario approaches the 40s `Spin` limit, and shards are green. Read the dump-volume and merge wall-clock lines from the job log.

**If shards are green but slow,** retune the encoding using the reported numbers — the three variables are per-PID append vs per-request file, gzip vs plain, and line-lists vs hit-maps. Do not retune before seeing the numbers.

**If shards are green and fast,** confirm the `e2e-behat` flag appears on Codecov with a realistic sub-100% over `src/`, then open the PR. Include the spec and this plan in that PR — a docs-only PR deadlocks on this repo (`paths-ignore` vs a required check).

---

## Self-Review

**Spec coverage.** Every section of `2026-07-29-behat-coverage-raw-collect-design.md` maps to a task: the request-side rewrite → Tasks 1–2; the Filter's move to merge time plus `cacheStaticAnalysis` and the uncovered-files denominator → Task 3; the zero-line tripwire and always-`exit 0` → Task 4; nightly-only re-enablement and Gate 1 instrumentation → Task 5. Gate 0a is already resolved and recorded in the spec. Gate 0b is marked optional and off the critical path there, so it is deliberately not a task.

**Unchanged by design:** `BehatCoverageSubscriber`, `CoverageCollectorInterface`, `FakeCoverageDriver`, `SpyCollector`, `docker/build/pcov.ini`, `docker/php-coverage.d/pcov-on.ini`, `docker-compose.yml`, `codecov.yml`. `BehatCoverageSubscriberTest` runs unmodified in Task 2 Step 4 as the regression check that the subscriber contract held.

**Fixed during review:** the worktree has no `vendor/` (added the composer prerequisite); the `reduce()` fixtures used `0` for "not executed" when the real markers are `-1`/`-2`, and normalising positive hit counts to exactly `1` turned out to be a correctness requirement rather than tidiness — `ProcessedCodeCoverageData::markCodeAsExecutedByTestCase` compares with `===` against `Driver::LINE_EXECUTED`, so a count of `7` would be silently dropped (added a dedicated test); the CLI's line counter was named `$records`; and Task 4's namespace `--filter` would not have matched, since `--filter` is a regex over test names.

**Type consistency.** `array<string, array<int,int>>` is the single currency between `reduce` → `encode` → `decodeAll` → `union` → `unionDir` → `toCodeCoverage` → `fromXdebugWithoutPathCoverage`, which is the shape that factory documents. `stopAndDump(string $dir)` keeps its interface signature; `writeClover(CodeCoverage, string)` keeps its existing one. `mergeDir()` is gone, replaced by `unionDir()`, and Task 4's CLI is the only caller — it is rewritten in the same task.
