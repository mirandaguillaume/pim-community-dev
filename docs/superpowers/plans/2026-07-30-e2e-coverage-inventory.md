# E2E coverage inventory Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Produce a committed, per-test inventory of which `src/**` PHP and which front-end JS each E2E test exercises — for both the Behat and Playwright suites — so the Behat suite can be migrated to Playwright without silently losing coverage.

**Architecture:** Two collectors, one inventory builder. PHP moves from a Symfony subscriber to an `auto_prepend_file` shim so it works in **any** `APP_ENV` (which is what gives Playwright PHP coverage). Each test's identity is carried in a marker file that the shim stamps onto every dump record. JS comes from raw V8 entries: Playwright already produces them per test, and Behat gets them over Selenium's `se:cdp`. A builder joins both sides into two JSON views.

**Tech Stack:** PHP 8.3/8.4, ext-pcov, vendored `phpunit/php-code-coverage` 10.1.16, Behat 3.29, Playwright, Node 22 (built-in `WebSocket`), `monocart-coverage-reports` ^2.12.12, GitHub Actions.

## Global Constraints

- Work in `~/claude-worktrees/pim-community-dev/behat-cov-raw-collect`, branch `c1/e2e-coverage-inventory`. Do **not** work in `/home/gumiranda/pim-community-dev`.
- PHP runs in Docker ONLY: `docker-compose run --rm php php …`. The host PHP is the wrong version.
- **No new composer or npm dependencies.** Node 22 has a global `WebSocket`; use it rather than adding `ws`.
- **Never write a bare `\pcov\foo()` call.** ext-pcov is runtime-only, absent from dev checkouts. Go through the existing guarded `CoverageCollector::pcov()` helper.
- PHP coverage code stays under `tests/legacy/features/Behat/Coverage/`, namespace `Pim\Behat\Coverage` (autoload-dev). Tests there are auto-discovered by `phpunit.xml.dist:122`; do **not** edit `phpunit.xml.dist`.
- **Best-effort everywhere.** The shim runs before the framework on every request — it must never affect a response. Every entry point wraps its body in `try/catch (\Throwable)`; CLIs always `exit 0`.
- **Never run Jest** or any JS test suite locally.
- Marker file path is exactly `var/tests/behat-coverage/.current-test`.
- Dump files keep `<pid>.dump` naming; the test id goes **inside** each record.
- Run PHP tests with: `APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter <TestClassName>`
- Expect `OK, but there were issues! … PHPUnit Deprecations: 1` on every PHPUnit run — a pre-existing named-argument deprecation from `CsvWriterIntegration.php:1017`, emitted at suite-discovery. Not yours.

## Scope note

This plan implements **two subsystems** (server-side PHP, browser-side JS) that share only the inventory builder. That was raised as a decomposition candidate during brainstorming and the decision was to do both together. Tasks are ordered so each is independently testable, and Tasks 1–4 deliver a working PHP-only inventory before any JS work starts — so the plan can be stopped halfway and still produce value.

---

## File Structure

| File | Responsibility | Action |
| --- | --- | --- |
| `tests/legacy/features/Behat/Coverage/TestMarker.php` | Read/write the current-test marker. Pure, no PCOV. | Create |
| `tests/legacy/features/Behat/Coverage/RawCoverageRecorder.php` | Records gain a test id; decode returns `{test, hits}`. | Modify |
| `tests/legacy/features/Behat/Coverage/CoverageCollector.php` | Stamp each record with the marker's test id. | Modify |
| `docker/coverage-prepend.php` | `auto_prepend_file` shim: start PCOV, dump on shutdown. Env-agnostic. | Create |
| `docker/php-coverage.d/pcov-on.ini` | Also install the shim. | Modify |
| `tests/legacy/features/Behat/Coverage/CoverageMerger.php` | Add per-test grouping alongside the existing whole-suite union. | Modify |
| `tests/legacy/features/Behat/Coverage/build-php-inventory.php` | CLI: per-test hit lines → JSON. | Create |
| `tests/legacy/features/Behat/Context/CoverageMarkerContext.php` | `@BeforeScenario` writes the marker. | Create |
| `config/services/behat/services.yml`, `behat.yml` | Register that context. | Modify |
| `config/services/behat/coverage.yml`, `BehatCoverageSubscriber.php` (+ its test) | Superseded by the shim. | Delete (Task 3 only) |
| `tests/front/e2e/fixtures/coverage-fixture.ts` | Also write the marker, so Playwright gets PHP attribution. | Modify |
| `tests/front/e2e/coverage/behat-cdp-coverage.js` | Drive `Profiler` over `se:cdp`; write per-scenario V8 dumps. | Create |
| `tests/front/e2e/coverage/build-inventory.js` | Join per-test PHP + JS → the two JSON views. | Create |
| `.github/workflows/coverage-inventory.yml` | `workflow_dispatch` only; `if: always()` throughout. | Create |

---

### Task 1: Test-id stamping in the record format

**Files:**
- Create: `tests/legacy/features/Behat/Coverage/TestMarker.php`
- Create: `tests/legacy/features/Behat/Coverage/TestMarkerTest.php`
- Modify: `tests/legacy/features/Behat/Coverage/RawCoverageRecorder.php`
- Modify: `tests/legacy/features/Behat/Coverage/RawCoverageRecorderTest.php`

**Interfaces:**
- Consumes: nothing.
- Produces:
  - `TestMarker::write(string $dir, string $testId): void` — writes `<dir>/.current-test`.
  - `TestMarker::read(string $dir): string` — the id, or `''` when absent/unreadable.
  - `RawCoverageRecorder::encode(array $hits, string $testId): string` — **signature changed**, testId added.
  - `RawCoverageRecorder::decodeAll(string $blob): array` — now `list<array{test: string, hits: array<string, array<int,int>>}>`.
  - `RawCoverageRecorder::unionByTest(array $accumulator, string $testId, array $hits): array` — `array<string testId, array<string file, array<int line, int 1>>>`.
  - `RawCoverageRecorder::reduce()` and `::union()` keep their current signatures.

- [ ] **Step 1: Write the failing tests**

Create `tests/legacy/features/Behat/Coverage/TestMarkerTest.php`:

```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;

final class TestMarkerTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/marker-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        @unlink($this->dir . '/.current-test');
        @rmdir($this->dir);
    }

    public function test_it_round_trips_a_test_id(): void
    {
        TestMarker::write($this->dir, 'features/pim/foo.feature:23');

        self::assertSame('features/pim/foo.feature:23', TestMarker::read($this->dir));
    }

    public function test_reading_an_absent_marker_yields_an_empty_string(): void
    {
        // The shim runs on EVERY request, including before any scenario has started and on
        // requests from a suite that never writes a marker. That must be quiet, not fatal.
        self::assertSame('', TestMarker::read($this->dir));
    }

    public function test_write_creates_the_directory_and_overwrites_a_previous_id(): void
    {
        TestMarker::write($this->dir, 'first:1');
        TestMarker::write($this->dir, 'second:2');

        self::assertSame('second:2', TestMarker::read($this->dir));
    }

    public function test_it_trims_trailing_whitespace(): void
    {
        // Guards against a marker written by a shell `echo` in some future caller.
        file_put_contents($this->markerPath(), "third:3\n");

        self::assertSame('third:3', TestMarker::read($this->dir));
    }

    private function markerPath(): string
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0o775, true);
        }

        return $this->dir . '/.current-test';
    }
}
```

Then replace the three existing `encode`/`decodeAll` tests in `RawCoverageRecorderTest.php` with these, leaving the `reduce` and `union` tests untouched:

```php
    public function test_encoded_records_round_trip_with_their_test_ids(): void
    {
        $blob = RawCoverageRecorder::encode(['/srv/pim/src/A.php' => [3 => 1]], 'foo.feature:1')
            . RawCoverageRecorder::encode(['/srv/pim/src/B.php' => [9 => 1]], 'bar.feature:7');

        self::assertSame(
            [
                ['test' => 'foo.feature:1', 'hits' => ['/srv/pim/src/A.php' => [3 => 1]]],
                ['test' => 'bar.feature:7', 'hits' => ['/srv/pim/src/B.php' => [9 => 1]]],
            ],
            RawCoverageRecorder::decodeAll($blob),
        );
    }

    public function test_decode_all_ignores_a_truncated_trailing_record(): void
    {
        $good = RawCoverageRecorder::encode(['/srv/pim/src/A.php' => [3 => 1]], 't:1');
        $truncated = substr(RawCoverageRecorder::encode(['/srv/pim/src/B.php' => [9 => 1]], 't:2'), 0, 6);

        self::assertSame(
            [['test' => 't:1', 'hits' => ['/srv/pim/src/A.php' => [3 => 1]]]],
            RawCoverageRecorder::decodeAll($good . $truncated),
        );
    }

    public function test_decode_all_returns_nothing_for_an_empty_blob(): void
    {
        self::assertSame([], RawCoverageRecorder::decodeAll(''));
    }

    public function test_union_by_test_keeps_each_test_separate(): void
    {
        $acc = RawCoverageRecorder::unionByTest([], 'a:1', ['/srv/pim/src/A.php' => [3 => 1]]);
        $acc = RawCoverageRecorder::unionByTest($acc, 'a:1', ['/srv/pim/src/A.php' => [5 => 1]]);
        $acc = RawCoverageRecorder::unionByTest($acc, 'b:2', ['/srv/pim/src/A.php' => [9 => 1]]);

        self::assertSame(
            [
                'a:1' => ['/srv/pim/src/A.php' => [3 => 1, 5 => 1]],
                'b:2' => ['/srv/pim/src/A.php' => [9 => 1]],
            ],
            $acc,
        );
    }
```

- [ ] **Step 2: Run the tests to verify they fail**

```bash
cd ~/claude-worktrees/pim-community-dev/behat-cov-raw-collect
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter 'TestMarkerTest|RawCoverageRecorderTest'
```

Expected: FAIL — `Class "Pim\Behat\Coverage\TestMarker" not found`, and `encode()` given 2 arguments where 1 is declared.

- [ ] **Step 3: Create `TestMarker`**

```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

/**
 * The current-test marker: how a test runner tells the server-side collector which test caused a
 * request.
 *
 * A file rather than a cookie, deliberately. A cookie only attributes requests the browser makes,
 * and setting one through Selenium before the first navigation raises `invalid cookie domain`. A
 * file also works for the Playwright suite, whose PHP requests come from a different browser stack.
 *
 * Safe as a single global file because scenarios run SEQUENTIALLY within a shard and each shard is
 * its own container and workspace, so there is never more than one writer.
 */
final class TestMarker
{
    private const FILENAME = '.current-test';

    public static function write(string $dir, string $testId): void
    {
        if (!\is_dir($dir)) {
            @\mkdir($dir, 0o775, true);
        }

        @\file_put_contents($dir . '/' . self::FILENAME, $testId);
    }

    /**
     * Returns '' when there is no marker. The shim calls this on every request, including before any
     * scenario has begun, so absence is normal and must be quiet.
     */
    public static function read(string $dir): string
    {
        $raw = @\file_get_contents($dir . '/' . self::FILENAME);

        return $raw === false ? '' : \trim($raw);
    }
}
```

- [ ] **Step 4: Change the record format**

In `RawCoverageRecorder.php`, replace `encode()` and `decodeAll()` and add `unionByTest()`. Leave `reduce()` and `union()` exactly as they are.

```php
    /**
     * A record is `pack('N', len) . gzencode(serialize(['test' => $testId, 'hits' => $hits]))`.
     *
     * The test id travels INSIDE the record rather than in the filename: one file per fpm worker
     * instead of one per (test x worker), which would be thousands per shard, and the append-only
     * no-locking property is preserved.
     *
     * @param array<string, array<int, int>> $hits
     */
    public static function encode(array $hits, string $testId): string
    {
        $payload = \gzencode(\serialize(['test' => $testId, 'hits' => $hits]), 1);

        return \pack('N', \strlen($payload)) . $payload;
    }

    /**
     * @return list<array{test: string, hits: array<string, array<int, int>>}>
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
                break; // truncated tail — an fpm worker killed mid-write
            }

            $payload = @\gzdecode(\substr($blob, $offset, $length));
            $offset += $length;

            if ($payload === false) {
                continue;
            }

            $record = @\unserialize($payload, ['allowed_classes' => false]);

            if (\is_array($record) && \is_array($record['hits'] ?? null)) {
                /** @var array{test: string, hits: array<string, array<int, int>>} $record */
                $records[] = ['test' => (string) ($record['test'] ?? ''), 'hits' => $record['hits']];
            }
        }

        return $records;
    }

    /**
     * Fold one record's hits into a per-test accumulator.
     *
     * @param array<string, array<string, array<int, int>>> $accumulator
     * @param array<string, array<int, int>>                $hits
     *
     * @return array<string, array<string, array<int, int>>>
     */
    public static function unionByTest(array $accumulator, string $testId, array $hits): array
    {
        $accumulator[$testId] = self::union($accumulator[$testId] ?? [], $hits);

        return $accumulator;
    }
```

- [ ] **Step 5: Run the tests to verify they pass**

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter 'TestMarkerTest|RawCoverageRecorderTest'
```

Expected: the two target classes PASS.

`CoverageCollectorTest`, `CoverageMergerTest` and `MergeCliTest` will now fail, because they call the one-argument `encode()`. **Fix them in this task** — every commit must leave the suite green, so the branch stays bisectable and no reviewer has to distinguish an intended red from a real one.

The fix is mechanical: give every existing `RawCoverageRecorder::encode([...])` call a second argument `'t:1'`. Do **not** add per-test assertions here — Tasks 2 and 3 replace those placeholders with real ones as they make each class test-aware.

```bash
grep -rn 'RawCoverageRecorder::encode(' tests/legacy/features/Behat/Coverage/*Test.php
```

Then confirm the whole Coverage suite is green before committing:

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . \
  --filter 'TestMarkerTest|RawCoverageRecorderTest|CoverageCollectorTest|CoverageMergerTest|MergeCliTest|BehatCoverageSubscriberTest'
```

- [ ] **Step 6: Commit**

```bash
git add tests/legacy/features/Behat/Coverage/TestMarker.php \
        tests/legacy/features/Behat/Coverage/TestMarkerTest.php \
        tests/legacy/features/Behat/Coverage/RawCoverageRecorder.php \
        tests/legacy/features/Behat/Coverage/RawCoverageRecorderTest.php
git commit -m "feat(coverage): carry a test id in every coverage record

Adds TestMarker (the file a test runner uses to tell the server-side collector which
test caused a request) and threads a test id through encode/decodeAll. The id lives
inside the record, not the filename: one file per fpm worker rather than one per
(test x worker), preserving the append-only no-locking property.

A file rather than a cookie because a cookie only attributes browser requests and
setting one via Selenium pre-navigation raises invalid cookie domain. Safe as a single
global file because scenarios run sequentially per shard and each shard is its own
workspace."
```

---

### Task 2: Stamp the collector, and install it via auto_prepend_file

**Files:**
- Modify: `tests/legacy/features/Behat/Coverage/CoverageCollector.php`
- Modify: `tests/legacy/features/Behat/Coverage/CoverageCollectorTest.php`
- Create: `docker/coverage-prepend.php`
- Modify: `docker/php-coverage.d/pcov-on.ini`

**Interfaces:**
- Consumes: `TestMarker::read()`, `RawCoverageRecorder::encode(array, string)` from Task 1.
- Produces: `CoverageCollector::stopAndDump(string $dir)` unchanged in signature, but now reads the marker from `$dir` and stamps records. `docker/coverage-prepend.php` is the env-agnostic entry point.

- [ ] **Step 1: Write the failing test**

Replace the first test in `CoverageCollectorTest.php` and add one; keep the other tests as they are.

```php
    public function test_it_stamps_the_record_with_the_current_test_id(): void
    {
        TestMarker::write($this->dir, 'features/pim/foo.feature:23');
        $collector = new CoverageCollector(static fn (): array => [
            '/srv/pim/src/A.php' => [3 => 1, 4 => -1],
        ]);

        $collector->start();
        $collector->stopAndDump($this->dir);

        self::assertSame(
            [['test' => 'features/pim/foo.feature:23', 'hits' => ['/srv/pim/src/A.php' => [3 => 1]]]],
            RawCoverageRecorder::decodeAll((string) file_get_contents($this->dir . '/' . getmypid() . '.dump')),
        );
    }

    public function test_it_still_records_when_no_marker_has_been_written(): void
    {
        // The shim runs on every request, including ones no test caused (warm-up, health checks).
        // Those must still be captured, attributed to the empty id, not silently dropped.
        $collector = new CoverageCollector(static fn (): array => ['/srv/pim/src/A.php' => [3 => 1]]);

        $collector->stopAndDump($this->dir);

        $records = RawCoverageRecorder::decodeAll(
            (string) file_get_contents($this->dir . '/' . getmypid() . '.dump')
        );
        self::assertSame('', $records[0]['test']);
    }
```

Add `@unlink($this->dir . '/.current-test');` as the first line of that class's `tearDown()`.

- [ ] **Step 2: Run it to verify it fails**

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter CoverageCollectorTest
```

Expected: FAIL — `encode()` is called with 1 argument where 2 are declared.

- [ ] **Step 3: Stamp the collector**

In `CoverageCollector::stopAndDump()`, replace the `file_put_contents` call:

```php
        @\file_put_contents(
            $dir . '/' . \getmypid() . '.dump',
            RawCoverageRecorder::encode($hits, TestMarker::read($dir)),
            \FILE_APPEND,
        );
```

- [ ] **Step 4: Create the shim**

Create `docker/coverage-prepend.php`:

```php
<?php

declare(strict_types=1);

/**
 * auto_prepend_file shim: starts PCOV line coverage for this request and dumps it on shutdown.
 *
 * Installed by docker/php-coverage.d/pcov-on.ini, which is only on the include path when
 * PHP_INI_SCAN_DIR points at that directory — i.e. on a coverage run and never otherwise.
 *
 * WHY NOT A SYMFONY SUBSCRIBER (which is what this replaces): Kernel.php:51-52 loads services from
 * config/services/<APP_ENV>/, so a subscriber registered under config/services/behat/ exists ONLY
 * when APP_ENV=behat. The Playwright suite runs APP_ENV=prod and therefore got no PHP coverage at
 * all. Registering it under config/services/prod/ instead would break real production builds: the
 * Coverage classes are autoload-dev and Symfony validates service classes at container COMPILE
 * time, so a --no-dev install would fail to build. A prepend file sidesteps the container entirely
 * and serves every APP_ENV with one mechanism.
 *
 * Runs BEFORE the framework, so it precedes all error handling: every path is wrapped and silent.
 */

(static function (): void {
    try {
        if (!\extension_loaded('pcov') || (int) \ini_get('pcov.enabled') !== 1) {
            return;
        }

        $root = \dirname(__DIR__);
        $autoload = $root . '/vendor/autoload.php';

        if (!\is_file($autoload)) {
            return;
        }

        require_once $autoload;

        if (!\class_exists(\Pim\Behat\Coverage\CoverageCollector::class)) {
            return; // a --no-dev install: the Coverage classes are autoload-dev
        }

        $dir = $root . '/var/tests/behat-coverage';
        $collector = \Pim\Behat\Coverage\CoverageCollector::create();
        $collector->start();

        \register_shutdown_function(static function () use ($collector, $dir): void {
            try {
                $collector->stopAndDump($dir);
            } catch (\Throwable) {
                // a coverage dump must never affect the response
            }
        });
    } catch (\Throwable) {
        // never break a request
    }
})();
```

- [ ] **Step 5: Install it from the ini**

Replace the whole of `docker/php-coverage.d/pcov-on.ini` with:

```ini
pcov.enabled=1
; Installs the collector for EVERY SAPI and every APP_ENV, which is what gives the Playwright
; suite (APP_ENV=prod) PHP coverage. auto_prepend_file is PHP_INI_PERDIR, so a conf.d file may
; set it. Only on the include path when PHP_INI_SCAN_DIR names this directory.
auto_prepend_file=/srv/pim/docker/coverage-prepend.php
```

- [ ] **Step 6: Run the tests and lint the shim**

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter CoverageCollectorTest
docker-compose run --rm php php -l docker/coverage-prepend.php
```

Expected: PASS, and `No syntax errors detected`.

- [ ] **Step 7: Smoke-check that the shim cannot break a request**

The shim runs outside the framework, so verify it is inert before trusting it. With PCOV absent locally the guard should return immediately and the script still run clean:

```bash
docker-compose run --rm php php -d auto_prepend_file=/srv/pim/docker/coverage-prepend.php \
  -r 'echo "request survived the shim\n";'
```

Expected: `request survived the shim` and nothing else. Any warning or fatal here is a blocker — report it rather than proceeding.

- [ ] **Step 8: Commit**

```bash
git add tests/legacy/features/Behat/Coverage/CoverageCollector.php \
        tests/legacy/features/Behat/Coverage/CoverageCollectorTest.php \
        docker/coverage-prepend.php docker/php-coverage.d/pcov-on.ini
git commit -m "feat(coverage): install the collector via auto_prepend_file, stamped per test

Kernel.php:51-52 loads services from config/services/<APP_ENV>/, so the existing
subscriber exists only under APP_ENV=behat -- which is the sole reason the Playwright
suite (APP_ENV=prod) has no PHP coverage. Registering it under config/services/prod/
would break real --no-dev builds, since the Coverage classes are autoload-dev and
Symfony validates service classes at container compile time.

A prepend file installed by pcov-on.ini sidesteps the container and serves every
APP_ENV with one mechanism. It runs before the framework, so every path is wrapped and
silent. Records now carry the marker's test id."
```

---

### Task 3: Group the merge by test, and retire the subscriber

**Files:**
- Modify: `tests/legacy/features/Behat/Coverage/CoverageMerger.php`
- Modify: `tests/legacy/features/Behat/Coverage/CoverageMergerTest.php`
- Modify: `tests/legacy/features/Behat/Coverage/MergeCliTest.php`
- Delete: `config/services/behat/coverage.yml`, `tests/legacy/features/Behat/Coverage/BehatCoverageSubscriber.php`, `tests/legacy/features/Behat/Coverage/BehatCoverageSubscriberTest.php`

**Interfaces:**
- Consumes: `RawCoverageRecorder::decodeAll()`, `::unionByTest()` from Task 1.
- Produces: `CoverageMerger::unionDirByTest(string $dir): array<string, array<string, array<int,int>>>`. `unionDir()` keeps its signature and now delegates.

- [ ] **Step 1: Write the failing test**

Add to `CoverageMergerTest.php`, and update every existing `RawCoverageRecorder::encode([...])` call in that file and in `MergeCliTest.php` to pass a second argument (use `'t:1'`):

```php
    public function test_it_groups_the_union_by_test_id(): void
    {
        file_put_contents(
            $this->dir . '/111.dump',
            RawCoverageRecorder::encode([$this->covered => [4 => 1]], 'a.feature:1')
            . RawCoverageRecorder::encode([$this->covered => [5 => 1]], 'b.feature:2'),
        );
        file_put_contents(
            $this->dir . '/222.dump',
            RawCoverageRecorder::encode([$this->covered => [6 => 1]], 'a.feature:1'),
        );

        self::assertSame(
            [
                'a.feature:1' => [$this->covered => [4 => 1, 6 => 1]],
                'b.feature:2' => [$this->covered => [5 => 1]],
            ],
            (new CoverageMerger())->unionDirByTest($this->dir),
        );
    }

    public function test_union_dir_still_returns_the_whole_suite_union(): void
    {
        // The Clover path (merge-behat-coverage.php) must keep working unchanged.
        file_put_contents(
            $this->dir . '/111.dump',
            RawCoverageRecorder::encode([$this->covered => [4 => 1]], 'a.feature:1')
            . RawCoverageRecorder::encode([$this->covered => [6 => 1]], 'b.feature:2'),
        );

        self::assertSame(
            [$this->covered => [4 => 1, 6 => 1]],
            (new CoverageMerger())->unionDir($this->dir),
        );
    }
```

- [ ] **Step 2: Run it to verify it fails**

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter CoverageMergerTest
```

Expected: FAIL — `Call to undefined method … ::unionDirByTest()`.

- [ ] **Step 3: Implement per-test grouping**

Replace `CoverageMerger::unionDir()` with these two methods, leaving every other method untouched:

```php
    /**
     * Union every complete record in every worker dump, grouped by the test that caused it.
     *
     * @return array<string, array<string, array<int, int>>>
     */
    public function unionDirByTest(string $dir): array
    {
        $byTest = [];

        foreach (\glob(\rtrim($dir, '/') . '/*.dump') ?: [] as $file) {
            $blob = @\file_get_contents($file);

            if ($blob === false) {
                continue;
            }

            foreach (RawCoverageRecorder::decodeAll($blob) as $record) {
                $byTest = RawCoverageRecorder::unionByTest($byTest, $record['test'], $record['hits']);
            }
        }

        return $byTest;
    }

    /**
     * The whole-suite union, as the Clover path needs it.
     *
     * @return array<string, array<int, int>>
     */
    public function unionDir(string $dir): array
    {
        $union = [];

        foreach ($this->unionDirByTest($dir) as $hits) {
            $union = RawCoverageRecorder::union($union, $hits);
        }

        return $union;
    }
```

- [ ] **Step 4: Retire the subscriber**

Task 2's shim now does this job in every `APP_ENV`. Remove the old path:

```bash
git rm config/services/behat/coverage.yml \
       tests/legacy/features/Behat/Coverage/BehatCoverageSubscriber.php \
       tests/legacy/features/Behat/Coverage/BehatCoverageSubscriberTest.php
```

- [ ] **Step 5: Run the whole Coverage suite**

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . \
  --filter 'TestMarkerTest|RawCoverageRecorderTest|CoverageCollectorTest|CoverageMergerTest|MergeCliTest'
```

Expected: PASS. `BehatCoverageSubscriberTest` is gone, so it must not appear.

- [ ] **Step 6: Commit**

```bash
git add -A tests/legacy/features/Behat/Coverage config/services/behat
git commit -m "feat(coverage): group the merge by test id; retire the subscriber

unionDirByTest() groups records by the test that caused them; unionDir() keeps its
signature and delegates, so the Clover path is unchanged.

Deletes BehatCoverageSubscriber and its APP_ENV=behat service registration, now that
the auto_prepend shim covers every APP_ENV. Order matters: the shim landed and was
smoke-checked in the previous commit before this removes the only other working path."
```

---

### Task 4: Behat writes the marker, and a PHP inventory CLI

**Files:**
- Create: `tests/legacy/features/Behat/Context/CoverageMarkerContext.php`
- Modify: `config/services/behat/services.yml`, `behat.yml`
- Create: `tests/legacy/features/Behat/Coverage/build-php-inventory.php`
- Create: `tests/legacy/features/Behat/Coverage/BuildPhpInventoryCliTest.php`

**Interfaces:**
- Consumes: `TestMarker::write()` (Task 1), `CoverageMerger::unionDirByTest()` (Task 3).
- Produces: CLI `php build-php-inventory.php --in <dumpdir> --src <srcdir> --out <json>` writing `{"<testId>": {"<file>": [lines]}}`. Always exits 0.

- [ ] **Step 1: Write the failing CLI test**

Create `tests/legacy/features/Behat/Coverage/BuildPhpInventoryCliTest.php`:

```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;

final class BuildPhpInventoryCliTest extends TestCase
{
    private string $dir;
    private string $srcDir;
    private string $covered;
    private string $excluded;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/phpinv-' . uniqid('', true);
        $this->srcDir = $this->dir . '/src';
        mkdir($this->srcDir, 0o775, true);
        $this->covered = $this->srcDir . '/Covered.php';
        $this->excluded = $this->srcDir . '/ThingTest.php';
        file_put_contents($this->covered, "<?php\nfunction c() { return 1; }\n");
        file_put_contents($this->excluded, "<?php\nfunction e() { return 1; }\n");
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

    public function test_it_writes_one_entry_per_test_excluding_test_files_and_foreign_paths(): void
    {
        file_put_contents(
            $this->dir . '/111.dump',
            RawCoverageRecorder::encode([$this->covered => [2 => 1]], 'a.feature:1')
            . RawCoverageRecorder::encode([$this->excluded => [2 => 1]], 'a.feature:1')
            . RawCoverageRecorder::encode(['/somewhere/else/Nope.php' => [2 => 1]], 'a.feature:1')
            . RawCoverageRecorder::encode([$this->covered => [2 => 1]], 'b.feature:9'),
        );
        $out = $this->dir . '/inv.json';

        [$exit] = $this->runCli($out);

        self::assertSame(0, $exit);
        $inv = json_decode((string) file_get_contents($out), true);

        // Paths are repo-relative, test files and out-of-tree paths dropped.
        self::assertSame(['a.feature:1', 'b.feature:9'], array_keys($inv));
        self::assertSame(['src/Covered.php' => [2]], $inv['a.feature:1']);
        self::assertSame(['src/Covered.php' => [2]], $inv['b.feature:9']);
    }

    public function test_it_warns_and_exits_zero_when_there_are_no_dumps(): void
    {
        [$exit, $stderr] = $this->runCli($this->dir . '/inv.json');

        self::assertSame(0, $exit);
        self::assertStringContainsString('WARNING', $stderr);
    }

    /** @return array{0: int, 1: string} */
    private function runCli(string $out): array
    {
        exec(sprintf(
            '%s %s --in %s --src %s --out %s 2>&1',
            escapeshellarg(PHP_BINARY),
            escapeshellarg(__DIR__ . '/build-php-inventory.php'),
            escapeshellarg($this->dir),
            escapeshellarg($this->srcDir),
            escapeshellarg($out),
        ), $output, $exit);

        return [$exit, implode("\n", $output)];
    }
}
```

- [ ] **Step 2: Run it to verify it fails**

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter BuildPhpInventoryCliTest
```

Expected: FAIL — the script does not exist, so exit is non-zero.

- [ ] **Step 3: Write the CLI**

Create `tests/legacy/features/Behat/Coverage/build-php-inventory.php`:

```php
<?php

declare(strict_types=1);

// Per-test PHP inventory: which src/ lines each E2E test executed.
//
// Deliberately does NOT build a CodeCoverage object. An inventory needs hit lines, not a
// percentage, so it needs no denominator, no Filter and no static analysis -- which is what makes
// this cheap enough to run per test. The Clover path (merge-behat-coverage.php) still does all that
// for the whole-suite report; the two are independent on purpose.
//
// Best-effort: always exit 0 so it can never fail the job.

require dirname(__DIR__, 5) . '/vendor/autoload.php';

use Pim\Behat\Coverage\CoverageMerger;

$options = getopt('', ['in:', 'src:', 'out:']);
$inDir = $options['in'] ?? null;
$srcDir = $options['src'] ?? null;
$out = $options['out'] ?? null;

if (!is_string($inDir) || !is_string($srcDir) || !is_string($out)) {
    fwrite(STDERR, "[php-inventory] usage: --in <dumpdir> --src <srcdir> --out <json>\n");
    exit(0);
}

// Single-colon (required-argument) getopt: `--in <value>` and `--in=<value>` both bind. The
// double-colon form binds ONLY when =-attached, which silently dropped a flag once already.

try {
    $srcReal = realpath($srcDir) ?: $srcDir;
    $repoRoot = dirname($srcReal);
    $byTest = (new CoverageMerger())->unionDirByTest($inDir);

    if ($byTest === []) {
        fwrite(STDERR, sprintf(
            "[php-inventory] WARNING: 0 records in %s — PCOV was most likely not active in the fpm "
            . "SAPI, or no marker was ever written\n",
            $inDir,
        ));
        exit(0);
    }

    $inventory = [];
    $keptFiles = 0;

    foreach ($byTest as $testId => $hits) {
        $entry = [];

        foreach ($hits as $file => $lines) {
            if (!str_starts_with($file, $srcReal . '/')) {
                continue; // outside the tree under analysis
            }
            if (str_ends_with($file, 'Test.php')
                || str_ends_with($file, 'Integration.php')
                || str_ends_with($file, 'EndToEnd.php')
            ) {
                continue; // mirrors phpunit.xml.dist's <source> excludes
            }

            $relative = substr($file, strlen($repoRoot) + 1);
            $numbers = array_keys($lines);
            sort($numbers);
            $entry[$relative] = $numbers;
            $keptFiles++;
        }

        ksort($entry);
        $inventory[$testId] = $entry;
    }

    ksort($inventory);

    if ($keptFiles === 0) {
        fwrite(STDERR, sprintf(
            "[php-inventory] WARNING: %d tests recorded but no file survived the %s filter — check "
            . "that dumped paths match the source tree\n",
            count($byTest),
            $srcReal,
        ));
    }

    if (!is_dir(dirname($out))) {
        @mkdir(dirname($out), 0o775, true);
    }

    file_put_contents($out, json_encode($inventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

    fwrite(STDOUT, sprintf(
        "[php-inventory] wrote %s (%d tests, %d file entries)\n",
        $out,
        count($inventory),
        $keptFiles,
    ));
} catch (\Throwable $e) {
    fwrite(STDERR, "[php-inventory] failed (ignored): {$e->getMessage()}\n");
}

exit(0);
```

- [ ] **Step 4: Create the Behat marker context**

Create `tests/legacy/features/Behat/Context/CoverageMarkerContext.php`:

```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Pim\Behat\Coverage\TestMarker;

/**
 * Tells the server-side coverage collector which scenario is running.
 *
 * Inert unless PCOV is collecting, so a normal behat run pays one ini_get() per scenario. The id is
 * `<repo-relative feature path>:<scenario line>`, which is also how behat itself addresses a
 * scenario — so the inventory can be joined back to the suite without a lookup table.
 */
final class CoverageMarkerContext implements Context
{
    public function __construct(private readonly string $dumpDir)
    {
    }

    /** @BeforeScenario */
    public function recordCurrentScenario(BeforeScenarioScope $scope): void
    {
        if (!\extension_loaded('pcov') || (int) \ini_get('pcov.enabled') !== 1) {
            return;
        }

        $file = $scope->getFeature()->getFile() ?? 'unknown.feature';
        $line = $scope->getScenario()->getLine();

        TestMarker::write($this->dumpDir, \sprintf('%s:%d', $this->relative($file), $line));
    }

    private function relative(string $path): string
    {
        // 5, not 4: __DIR__ is tests/legacy/features/Behat/Context, so four levels lands on
        // /srv/pim/tests and would strip the `tests/` segment from every scenario id --
        // breaking the join with behat.yml, whose suite paths are `tests/legacy/features`.
        $root = \dirname(__DIR__, 5) . '/';

        return \str_starts_with($path, $root) ? \substr($path, \strlen($root)) : $path;
    }
}
```

Register it in `config/services/behat/services.yml`, immediately after the `Pim\Behat\Context\HookContext` block:

```yaml
    Pim\Behat\Context\CoverageMarkerContext:
        - '%kernel.project_dir%/var/tests/behat-coverage'
```

And add it to the shared `contexts` list in `behat.yml`, immediately after the `Pim\Behat\Context\HookContext` line:

```yaml
    -   Pim\Behat\Context\CoverageMarkerContext
```

- [ ] **Step 5: Run the tests**

```bash
APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter BuildPhpInventoryCliTest
docker-compose run --rm php php -l tests/legacy/features/Behat/Context/CoverageMarkerContext.php
python3 -c "import yaml; yaml.safe_load(open('behat.yml')); yaml.safe_load(open('config/services/behat/services.yml')); print('yaml OK')"
```

Expected: tests PASS, `No syntax errors detected`, `yaml OK`.

- [ ] **Step 6: Commit**

```bash
git add tests/legacy/features/Behat/Context/CoverageMarkerContext.php \
        tests/legacy/features/Behat/Coverage/build-php-inventory.php \
        tests/legacy/features/Behat/Coverage/BuildPhpInventoryCliTest.php \
        config/services/behat/services.yml behat.yml
git commit -m "feat(coverage): Behat writes the scenario marker; add a per-test PHP inventory CLI

CoverageMarkerContext writes <feature>:<line> before each scenario -- the same way behat
addresses a scenario, so the inventory joins back to the suite without a lookup table.
Inert unless PCOV is collecting.

The inventory CLI deliberately builds no CodeCoverage object: an inventory needs hit
lines, not a percentage, so no denominator, Filter or static analysis is required. That
is what makes per-test output cheap. The Clover path stays independent."
```

---

### Task 5: Playwright writes the marker too

**Files:**
- Modify: `tests/front/e2e/fixtures/coverage-fixture.ts`

**Interfaces:**
- Consumes: the marker convention from Task 1 (`var/tests/behat-coverage/.current-test`, plain text).
- Produces: nothing new — Playwright's PHP requests become attributable to `testInfo.titlePath`.

- [ ] **Step 1: Write the marker from the fixture**

In `tests/front/e2e/fixtures/coverage-fixture.ts`, add below the existing `OUT` constant:

```ts
/**
 * Where the PHP collector looks for the current-test id. Writing it here is what gives the
 * Playwright suite PHP coverage: the auto_prepend shim runs for every APP_ENV, so the only missing
 * piece was telling it which test caused a request.
 */
const MARKER_DIR = path.resolve(__dirname, '../../../..', 'var/tests/behat-coverage');
```

Then, inside the `if (COVERAGE)` block that runs **before** `await use(page)`, after the `startJSCoverage` try/catch:

```ts
      try {
        fs.mkdirSync(MARKER_DIR, {recursive: true});
        // titlePath() is stable and human-readable, and matches how Playwright reports a test —
        // so the inventory keys line up with what `npx playwright test` prints.
        fs.writeFileSync(path.join(MARKER_DIR, '.current-test'), testInfo.titlePath.join(' > '));
      } catch (e) {
        console.warn(`[coverage] marker write failed: ${(e as Error).message}`);
      }
```

- [ ] **Step 2: Verify it type-checks and the file parses**

```bash
docker-compose run --rm -T --user "$(id -u):$(id -g)" node \
  node_modules/.bin/tsc --noEmit --esModuleInterop --skipLibCheck \
  tests/front/e2e/fixtures/coverage-fixture.ts 2>&1 | tail -5
```

Expected: no errors referencing `coverage-fixture.ts`. Pre-existing errors from other files may appear; ignore those. **Do not run the Playwright or Jest suites.**

- [ ] **Step 3: Commit**

```bash
git add tests/front/e2e/fixtures/coverage-fixture.ts
git commit -m "feat(coverage): Playwright writes the current-test marker

With the collector installed by auto_prepend_file rather than an APP_ENV=behat service,
the only thing standing between the Playwright suite and PHP coverage was telling the
collector which test caused a request. The fixture now writes the same marker the Behat
context does, keyed on titlePath() so inventory keys match what playwright reports."
```

---

### Task 6: Behat JS coverage over Selenium CDP

**Files:**
- Create: `tests/front/e2e/coverage/behat-cdp-coverage.js`
- Create: `tests/front/e2e/coverage/behat-cdp-coverage.check.js`

**Interfaces:**
- Consumes: nothing from earlier tasks (independent of the PHP side).
- Produces: a CLI `node behat-cdp-coverage.js <selenium-base-url> <out-dir>` exposing `startCoverage(sessionId)` / `takeCoverage(sessionId, testId)`; writes `<out-dir>/<sanitised testId>.json` containing the raw V8 entry array that `e2e-coverage-report.js` already consumes.

- [ ] **Step 1: Write the failing check**

There is no browser in the unit environment, so this gets a node-runnable check of the pure parts, following the existing `e2e-coverage-report.check.js` pattern. Create `tests/front/e2e/coverage/behat-cdp-coverage.check.js`:

```js
/**
 * Node-runnable checks for the pure parts of behat-cdp-coverage (no browser needed).
 * Run: node tests/front/e2e/coverage/behat-cdp-coverage.check.js
 */
const assert = require('assert');
const {sanitise, toV8Entries} = require('./behat-cdp-coverage');

// A scenario id becomes a safe filename.
assert.strictEqual(
  sanitise('tests/legacy/features/pim/foo.feature:23'),
  'tests-legacy-features-pim-foo-feature-23'
);

// Profiler.takePreciseCoverage returns {result: [{scriptId, url, functions}]}. monocart wants that
// array with `source` attached, and entries without a usable url dropped.
const cdp = {
  result: [
    {scriptId: '1', url: 'http://httpd/dist/main.min.js', functions: [{functionName: 'f', ranges: []}]},
    {scriptId: '2', url: '', functions: []},
    {scriptId: '3', url: 'chrome-extension://x/y.js', functions: []},
  ],
};
const sources = {'1': 'console.log(1)'};
const entries = toV8Entries(cdp, sources);

assert.strictEqual(entries.length, 1, 'only the real http script survives');
assert.strictEqual(entries[0].url, 'http://httpd/dist/main.min.js');
assert.strictEqual(entries[0].source, 'console.log(1)');
assert.ok(Array.isArray(entries[0].functions));

console.log('behat-cdp-coverage checks passed');
```

- [ ] **Step 2: Run it to verify it fails**

```bash
docker-compose run --rm -T node node tests/front/e2e/coverage/behat-cdp-coverage.check.js
```

Expected: FAIL — `Cannot find module './behat-cdp-coverage'`.

- [ ] **Step 3: Write the helper**

Create `tests/front/e2e/coverage/behat-cdp-coverage.js`:

```js
/**
 * Per-scenario JS coverage for the Behat suite, over Selenium's CDP endpoint.
 *
 * Behat drives Pim\Behat\Extension\WebdriverClassicExtension — the CLASSIC WebDriver protocol,
 * which has no CDP of its own. But Selenium exposes one per session: GET /session/{id} returns
 * capabilities including `se:cdp`, a websocket URL. Verified on this stack
 * (selenium/standalone-chrome:4.27.0, se:cdpVersion 131.0.6778.204).
 *
 * That is why this exists instead of an instrumented bundle: driving Profiler over CDP yields the
 * same raw V8 entries Playwright's page.coverage produces, so the existing monocart pipeline
 * (e2e-coverage-report.js) consumes them unchanged — no second build artifact, no SWC/Babel
 * instrumentation, and no window.__coverage__ flushing across Backbone full page loads.
 *
 * Node 22 has a global WebSocket, so this needs no dependency.
 *
 * Best-effort throughout: any failure warns and resolves, never throws into a scenario.
 */
const fs = require('fs');
const path = require('path');

/** A scenario id such as `path/to/foo.feature:23` becomes a safe filename. */
function sanitise(testId) {
  return String(testId).replace(/[^0-9a-z]+/gi, '-').replace(/^-+|-+$/g, '');
}

/**
 * Reshape Profiler.takePreciseCoverage output into the array monocart expects, attaching each
 * script's source. Entries without an http(s) url are dropped: extension and internal scripts
 * cannot be mapped back to repository sources and would only add noise.
 */
function toV8Entries(cdpResult, sourcesByScriptId) {
  const result = (cdpResult && cdpResult.result) || [];

  return result
    .filter(e => typeof e.url === 'string' && /^https?:\/\//.test(e.url))
    .map(e => ({
      scriptId: e.scriptId,
      url: e.url,
      functions: e.functions || [],
      source: sourcesByScriptId[e.scriptId] || '',
    }));
}

/** Minimal CDP client over the session's se:cdp websocket. */
class CdpClient {
  constructor(url) {
    this.url = url;
    this.nextId = 1;
    this.pending = new Map();
    this.ws = null;
  }

  connect() {
    return new Promise((resolve, reject) => {
      this.ws = new WebSocket(this.url);
      this.ws.onopen = () => resolve();
      this.ws.onerror = e => reject(new Error(`cdp connect failed: ${e.message || 'unknown'}`));
      this.ws.onmessage = ev => {
        let msg;
        try {
          msg = JSON.parse(ev.data);
        } catch {
          return;
        }
        const p = this.pending.get(msg.id);
        if (p) {
          this.pending.delete(msg.id);
          msg.error ? p.reject(new Error(msg.error.message)) : p.resolve(msg.result);
        }
      };
    });
  }

  send(method, params = {}) {
    const id = this.nextId++;
    return new Promise((resolve, reject) => {
      this.pending.set(id, {resolve, reject});
      this.ws.send(JSON.stringify({id, method, params}));
      setTimeout(() => {
        if (this.pending.delete(id)) reject(new Error(`cdp timeout: ${method}`));
      }, 30000);
    });
  }

  close() {
    try {
      this.ws && this.ws.close();
    } catch {
      /* ignore */
    }
  }
}

/** Read the session's se:cdp websocket URL from Selenium. */
async function cdpUrl(seleniumBase, sessionId) {
  const res = await fetch(`${seleniumBase}/session/${sessionId}`);
  const body = await res.json();
  const caps = (body.value && body.value.capabilities) || body.value || {};
  const url = caps['se:cdp'];
  if (!url) throw new Error('se:cdp absent from session capabilities');
  return url;
}

async function startCoverage(seleniumBase, sessionId) {
  const client = new CdpClient(await cdpUrl(seleniumBase, sessionId));
  await client.connect();
  await client.send('Profiler.enable');
  await client.send('Profiler.startPreciseCoverage', {callCount: false, detailed: true});
  return client;
}

async function takeCoverage(client, testId, outDir) {
  const cdpResult = await client.send('Profiler.takePreciseCoverage');
  const sources = {};

  for (const entry of cdpResult.result || []) {
    try {
      const {scriptSource} = await client.send('Debugger.getScriptSource', {scriptId: entry.scriptId});
      sources[entry.scriptId] = scriptSource;
    } catch {
      // a script may already be gone after a navigation; its entry is still useful without source
    }
  }

  const entries = toV8Entries(cdpResult, sources);
  if (!entries.length) return 0;

  fs.mkdirSync(outDir, {recursive: true});
  fs.writeFileSync(path.join(outDir, `${sanitise(testId)}.json`), JSON.stringify(entries));

  return entries.length;
}

module.exports = {sanitise, toV8Entries, CdpClient, cdpUrl, startCoverage, takeCoverage};
```

- [ ] **Step 4: Run the check to verify it passes**

```bash
docker-compose run --rm -T node node tests/front/e2e/coverage/behat-cdp-coverage.check.js
```

Expected: `behat-cdp-coverage checks passed`.

- [ ] **Step 5: Commit**

```bash
git add tests/front/e2e/coverage/behat-cdp-coverage.js \
        tests/front/e2e/coverage/behat-cdp-coverage.check.js
git commit -m "feat(coverage): per-scenario Behat JS coverage over Selenium CDP

Behat's WebdriverClassicExtension speaks the classic WebDriver protocol and has no CDP,
but Selenium exposes one per session: GET /session/{id} returns capabilities including
se:cdp. Verified on this stack (selenium/standalone-chrome:4.27.0, cdpVersion
131.0.6778.204).

Driving Profiler over that socket yields the same raw V8 entries page.coverage gives
Playwright, so the existing monocart pipeline consumes them unchanged -- no instrumented
bundle, no second build artifact, no window.__coverage__ flushing across Backbone page
loads. Node 22's global WebSocket means no new dependency."
```

---

### Task 7: The inventory builder

**Files:**
- Create: `tests/front/e2e/coverage/build-inventory.js`
- Create: `tests/front/e2e/coverage/build-inventory.check.js`

**Interfaces:**
- Consumes: the PHP inventory JSON from Task 4 (`{testId: {file: [lines]}}`) and per-test V8 dumps from Task 6 / the Playwright fixture.
- Produces: `docs/coverage-inventory/scenarios.json` and `files.json`.

- [ ] **Step 1: Write the failing check**

Create `tests/front/e2e/coverage/build-inventory.check.js`:

```js
/**
 * Node-runnable checks for the inventory join.
 * Run: node tests/front/e2e/coverage/build-inventory.check.js
 */
const assert = require('assert');
const {join, invert} = require('./build-inventory');

const php = {
  'a.feature:1': {'src/A.php': [3, 5]},
  'b.feature:2': {'src/A.php': [3]},
};
const js = {
  'a.feature:1': {'src/front/x.ts': [10]},
};

const scenarios = join(php, js);

// Every test from either side appears, with both keys always present.
assert.deepStrictEqual(Object.keys(scenarios), ['a.feature:1', 'b.feature:2']);
assert.deepStrictEqual(scenarios['a.feature:1'].php, {'src/A.php': [3, 5]});
assert.deepStrictEqual(scenarios['a.feature:1'].js, {'src/front/x.ts': [10]});
assert.deepStrictEqual(scenarios['b.feature:2'].js, {}, 'a test with no JS still gets an empty map');

// The inverse view: which tests cover a file. This is the one that answers the migration
// question -- when a file's last Behat scenario is gone, its coverage has moved.
const files = invert(scenarios);
assert.deepStrictEqual(files['src/A.php'], ['a.feature:1', 'b.feature:2']);
assert.deepStrictEqual(files['src/front/x.ts'], ['a.feature:1']);

console.log('build-inventory checks passed');
```

- [ ] **Step 2: Run it to verify it fails**

```bash
docker-compose run --rm -T node node tests/front/e2e/coverage/build-inventory.check.js
```

Expected: FAIL — `Cannot find module './build-inventory'`.

- [ ] **Step 3: Write the builder**

Create `tests/front/e2e/coverage/build-inventory.js`:

```js
/**
 * Joins per-test PHP and JS coverage into the migration inventory.
 *
 * Two views are written. `scenarios.json` answers "what does this test exercise", which is what you
 * need to reproduce a Behat scenario in Playwright. `files.json` is the inverse and answers the
 * actual migration question: when a file's last remaining Behat scenario has moved, that file's
 * coverage is safe to consider migrated.
 *
 * JS side: each per-test V8 dump goes through monocart individually, which unpacks the original
 * sources from the rspack source maps (devtool:'source-map'). One monocart pass per test is slower
 * than one pass overall, but this job runs on demand, so clarity wins over speed.
 *
 * Best-effort: any single test's failure warns and is skipped; the process still exits 0.
 */
const fs = require('fs');
const path = require('path');

const REPO_ROOT = path.resolve(__dirname, '../../../..');
const OUT_DIR = path.join(REPO_ROOT, 'docs/coverage-inventory');

/**
 * @param {Record<string, Record<string, number[]>>} php
 * @param {Record<string, Record<string, number[]>>} js
 */
function join(php, js) {
  const out = {};
  for (const test of [...Object.keys(php), ...Object.keys(js)].sort()) {
    if (out[test]) continue;
    out[test] = {php: php[test] || {}, js: js[test] || {}};
  }
  return out;
}

/** scenario -> code becomes code -> scenarios. */
function invert(scenarios) {
  const files = {};
  for (const [test, sides] of Object.entries(scenarios)) {
    for (const map of [sides.php, sides.js]) {
      for (const file of Object.keys(map)) {
        (files[file] ||= []).push(test);
      }
    }
  }
  for (const file of Object.keys(files)) {
    files[file] = [...new Set(files[file])].sort();
  }
  return Object.fromEntries(Object.entries(files).sort(([a], [b]) => a.localeCompare(b)));
}

/** Run one per-test V8 dump through monocart and return {file: [lines]} for covered lines. */
async function jsCoverageForDump(dumpFile) {
  const MCR = require('monocart-coverage-reports');
  const mcr = MCR({
    outputDir: path.join(REPO_ROOT, 'var/tmp/mcr-inventory'),
    baseDir: REPO_ROOT,
    logging: 'error',
    reports: ['none'],
    entryFilter: {'**/node_modules/**': false, '**/*': true},
    sourceFilter: {'**/node_modules/**': false, '**/src/**': true, '**/public/bundles/**': true},
  });

  await mcr.add(JSON.parse(fs.readFileSync(dumpFile, 'utf8')));
  const results = await mcr.generate();
  const out = {};

  for (const file of results.files || []) {
    const covered = Object.entries((file.data && file.data.lines) || {})
      .filter(([, hits]) => hits > 0)
      .map(([line]) => Number(line))
      .sort((a, b) => a - b);

    if (covered.length) out[file.sourcePath] = covered;
  }

  return out;
}

async function main() {
  const phpFile = process.argv[2];
  const v8Dir = process.argv[3];

  const php = fs.existsSync(phpFile) ? JSON.parse(fs.readFileSync(phpFile, 'utf8')) : {};
  if (!Object.keys(php).length) console.warn(`[inventory] WARNING: no PHP entries from ${phpFile}`);

  const js = {};
  const dumps = fs.existsSync(v8Dir)
    ? fs.readdirSync(v8Dir).filter(f => f.endsWith('.json'))
    : [];
  if (!dumps.length) console.warn(`[inventory] WARNING: no JS dumps under ${v8Dir}`);

  for (const name of dumps) {
    const testId = name.replace(/\.json$/, '');
    try {
      js[testId] = await jsCoverageForDump(path.join(v8Dir, name));
    } catch (e) {
      console.warn(`[inventory] skip JS ${name}: ${e.message}`);
    }
  }

  const scenarios = join(php, js);
  const files = invert(scenarios);

  fs.mkdirSync(OUT_DIR, {recursive: true});
  fs.writeFileSync(path.join(OUT_DIR, 'scenarios.json'), JSON.stringify(scenarios, null, 2) + '\n');
  fs.writeFileSync(path.join(OUT_DIR, 'files.json'), JSON.stringify(files, null, 2) + '\n');

  console.log(
    `[inventory] wrote ${Object.keys(scenarios).length} tests, ${Object.keys(files).length} files`
  );
}

if (require.main === module) {
  main().catch(e => console.warn(`[inventory] fatal (ignored): ${e.message}`));
}

module.exports = {join, invert, jsCoverageForDump, OUT_DIR};
```

**Note on the JS test-id mismatch:** PHP keys are `<feature>:<line>` while JS dump filenames are the *sanitised* form. Task 6's `sanitise()` is the only transform, so the join is on sanitised ids for JS and raw ids for PHP — meaning a scenario appears under two keys. Fix this in Step 4 rather than leaving it.

- [ ] **Step 4: Normalise the PHP keys so both sides join**

Add to `build-inventory.js`, and apply it to the PHP map before joining:

```js
/** Match Task 6's filename transform so PHP and JS keys line up. */
function sanitise(testId) {
  return String(testId).replace(/[^0-9a-z]+/gi, '-').replace(/^-+|-+$/g, '');
}

/** Re-key a {testId: …} map by sanitised id, preserving the original for display. */
function normaliseKeys(map) {
  const out = {};
  for (const [testId, value] of Object.entries(map)) {
    out[sanitise(testId)] = value;
  }
  return out;
}
```

Then in `main()`, replace `const scenarios = join(php, js);` with:

```js
  const scenarios = join(normaliseKeys(php), js);
```

and add `sanitise, normaliseKeys` to the `module.exports` list. Add this assertion to the check file, then re-run it:

```js
const {normaliseKeys} = require('./build-inventory');
assert.deepStrictEqual(
  Object.keys(normaliseKeys({'tests/legacy/features/foo.feature:23': {}})),
  ['tests-legacy-features-foo-feature-23'],
  'PHP keys are sanitised so they join with JS dump filenames'
);
```

- [ ] **Step 5: Run the check to verify it passes**

```bash
docker-compose run --rm -T node node tests/front/e2e/coverage/build-inventory.check.js
```

Expected: `build-inventory checks passed`.

- [ ] **Step 6: Commit**

```bash
git add tests/front/e2e/coverage/build-inventory.js \
        tests/front/e2e/coverage/build-inventory.check.js
git commit -m "feat(coverage): join per-test PHP and JS into the migration inventory

Writes two views. scenarios.json answers 'what does this test exercise', which is what
you need to reproduce a Behat scenario in Playwright. files.json is the inverse and
answers the migration question directly: when a file's last remaining Behat scenario
has moved, that file's coverage is safe to consider migrated.

Each per-test V8 dump goes through monocart individually so the original sources are
unpacked from the rspack source maps. One pass per test is slower than one overall, but
this job runs on demand. PHP keys are sanitised to match the JS dump filenames, so both
sides join on one id."
```

---

### Task 8: The on-demand workflow

**Files:**
- Create: `.github/workflows/coverage-inventory.yml`

**Interfaces:**
- Consumes: everything from Tasks 1–7.
- Produces: a `workflow_dispatch` workflow that commits `docs/coverage-inventory/*.json`.

- [ ] **Step 1: Write the workflow**

Create `.github/workflows/coverage-inventory.yml`:

```yaml
# Per-scenario PHP + JS coverage inventory for the E2E suites.
#
# workflow_dispatch ONLY, deliberately. This is a migration inventory, not a metric, so it has no
# nightly constraint: the ~2.8x per-request PCOV overhead is irrelevant and shard failures are fine
# (a scenario that fails still exercised code up to its failure point). That is why every coverage
# step below carries `if: always()` -- without it a red shard skips the merge and yields nothing.
#
# Runs the `nightly` Behat suite because it includes @critical and @optional: the inventory must
# cover everything that needs migrating, not just what PRs run.
name: Coverage inventory

on:
  workflow_dispatch:

permissions:
  contents: write

defaults:
  run:
    shell: bash

jobs:
  behat-inventory:
    runs-on: ${{ vars.RUNNER_LABEL || 'ubuntu-latest' }}
    timeout-minutes: 120
    strategy:
      fail-fast: false
      max-parallel: 4
      matrix:
        shard: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
    steps:
      - uses: actions/checkout@v4

      - uses: ./.github/actions/setup-pim-job
        with:
          github-token: ${{ secrets.GITHUB_TOKEN }}

      - uses: ./.github/actions/install-castor

      - name: Cleanup stale containers
        run: docker-compose down --remove-orphans 2>/dev/null || true

      - name: Bring up the stack with coverage enabled
        env:
          # Leading ':' is load-bearing: it means "each SAPI's own compiled-in conf.d, THEN this
          # directory". An empty value would disable all conf.d, stripping every extension and
          # 500ing the app. pcov-on.ini both enables PCOV and installs the auto_prepend collector.
          PHP_INI_SCAN_DIR: ':/srv/pim/docker/php-coverage.d'
        run: |
          DB_NAME=$(grep '^APP_DATABASE_NAME=' .env.behat 2>/dev/null | cut -d= -f2 || echo "akeneo_pim_test")
          APP_ENV=behat castor docker:up 'httpd mysql elasticsearch object-storage selenium pubsub-emulator'
          docker/wait_docker_up.sh
          .github/scripts/setup-test-db.sh "${DB_NAME}" seed/db-seed.sql
          docker-compose run --rm php php bin/console akeneo:elasticsearch:reset-indexes --env=test

      - name: Assert the collector is actually installed
        run: |
          docker-compose exec -u www-data -T httpd php -r '
            printf("pcov=%d enabled=%s prepend=%s\n",
              extension_loaded("pcov"), ini_get("pcov.enabled"), ini_get("auto_prepend_file"));'
          echo "expected: pcov=1 enabled=1 prepend=/srv/pim/docker/coverage-prepend.php"

      - name: Run the nightly Behat suite
        continue-on-error: true
        run: |
          export BEHAT_SPLIT="${{ matrix.shard }}/10"
          SUITE=nightly castor test:end-to-end-legacy

      - name: Build the per-test PHP inventory
        if: always()
        continue-on-error: true
        run: |
          docker-compose exec -u www-data -T httpd \
            php tests/legacy/features/Behat/Coverage/build-php-inventory.php \
            --in var/tests/behat-coverage \
            --src /srv/pim/src \
            --out var/tests/inventory-php-${{ matrix.shard }}.json

      - name: Upload shard inventory
        if: always()
        uses: actions/upload-artifact@v4
        with:
          name: inventory-shard-${{ matrix.shard }}
          path: |
            var/tests/inventory-php-${{ matrix.shard }}.json
            coverage-v8/
          retention-days: 7
          if-no-files-found: warn

  commit-inventory:
    runs-on: ${{ vars.RUNNER_LABEL || 'ubuntu-latest' }}
    needs: [behat-inventory]
    if: always()
    timeout-minutes: 20
    steps:
      - uses: actions/checkout@v4

      - uses: ./.github/actions/setup-pim-job
        with:
          github-token: ${{ secrets.GITHUB_TOKEN }}

      - name: Download every shard inventory
        uses: actions/download-artifact@v4
        with:
          pattern: inventory-shard-*
          path: shards

      - name: Merge shard PHP inventories
        run: |
          docker-compose run --rm -T node node -e '
            const fs = require("fs"), path = require("path");
            const out = {};
            const walk = d => fs.readdirSync(d, {withFileTypes: true}).flatMap(e =>
              e.isDirectory() ? walk(path.join(d, e.name)) : [path.join(d, e.name)]);
            for (const f of walk("shards").filter(f => /inventory-php-\d+\.json$/.test(f))) {
              for (const [test, files] of Object.entries(JSON.parse(fs.readFileSync(f, "utf8")))) {
                out[test] = Object.assign(out[test] || {}, files);
              }
            }
            fs.writeFileSync("inventory-php-all.json", JSON.stringify(out));
            console.log(`merged ${Object.keys(out).length} tests`);
          '

      - name: Build the inventory
        run: |
          mkdir -p coverage-v8
          find shards -type d -name 'coverage-v8' -exec cp -a {}/. coverage-v8/ \; 2>/dev/null || true
          docker-compose run --rm -T node \
            node tests/front/e2e/coverage/build-inventory.js inventory-php-all.json coverage-v8

      - name: Commit the inventory
        run: |
          if git diff --quiet -- docs/coverage-inventory/; then
            echo "inventory unchanged — nothing to commit"
            exit 0
          fi
          git config user.name "$(git log -1 --format='%an')"
          git config user.email "$(git log -1 --format='%ae')"
          git add docs/coverage-inventory/
          git commit -m "chore(coverage): refresh the E2E coverage inventory"
          git push
```

- [ ] **Step 2: Validate the workflow parses**

```bash
python3 -c "import yaml; yaml.safe_load(open('.github/workflows/coverage-inventory.yml')); print('ci.yml OK')"
```

Expected: `ci.yml OK`.

- [ ] **Step 3: Commit**

```bash
git add .github/workflows/coverage-inventory.yml
git commit -m "ci(coverage): on-demand per-scenario coverage inventory workflow

workflow_dispatch only, deliberately: an inventory is not a metric, so it has no
nightly constraint. The ~2.8x per-request PCOV overhead is irrelevant here and shard
failures are fine, which is why every coverage step carries if: always() -- without it
a red shard skips the merge and yields nothing, as observed on the earlier Gate 1 run.

Runs the nightly suite because it includes @critical and @optional: the inventory must
cover everything that needs migrating. Asserts pcov and auto_prepend_file are actually
in effect before the suite runs, so a silently inert collector cannot masquerade as a
clean result."
```

- [ ] **Step 4: Dispatch it and read the result**

```bash
gh workflow run coverage-inventory.yml --ref c1/e2e-coverage-inventory
```

**Acceptance:** the assert step prints `pcov=1 enabled=1 prepend=/srv/pim/docker/coverage-prepend.php`; `docs/coverage-inventory/scenarios.json` is non-empty with at least one scenario having **both** `php` and `js` populated; per-scenario PHP file counts are far below the 2,516 files Gate 1 measured suite-wide.

**If `js` is empty for every scenario**, the CDP route did not capture anything — most likely because `Profiler.takePreciseCoverage` was reset by a Backbone full page load. That is the risk the spec names: fall back to taking coverage per navigation rather than per scenario, and report before changing anything else.

---

## Self-Review

**Spec coverage.** Every spec section maps to a task: the `auto_prepend_file` move and subscriber retirement → Tasks 2–3; per-test attribution via marker → Tasks 1, 4, 5; Behat JS over `se:cdp` → Task 6; Playwright JS → already shipped, consumed in Task 7; the two JSON views → Task 7; `workflow_dispatch` + `if: always()` + `nightly` suite + committed JSON → Task 8. Error handling is folded into each component. The spec's risk about deleting the subscriber only after the shim is verified is enforced by task ordering (Task 2 smoke-checks, Task 3 deletes).

**Fixed during review:** the PHP/JS test-id key mismatch — PHP emits `<feature>:<line>`, JS dumps are sanitised filenames, so the join would have silently produced two entries per scenario. Task 7 Step 4 normalises both sides onto the sanitised id. Also corrected `getopt` to the single-colon form in Task 4, since the double-colon form binds only with `=`-attached values and silently dropped a flag in the previous plan.

**Type consistency.** `array<string, array<int, int>>` is the currency between `reduce` → `encode` → `decodeAll['hits']` → `unionByTest` → `unionDirByTest`. `TestMarker::read/write(string $dir, …)` takes the dump directory, matching what `stopAndDump()` is given. The JS `{testId: {file: [lines]}}` shape is produced by Task 4's CLI and consumed by Task 7's `join()`.

**Known gap, deliberate:** no automated test proves the shim actually collects under a live PCOV, because no dev image has the extension. Task 8's assert step covers it in CI instead — which is the same reason Gate 0a existed in the earlier work.
