# Behat E2E PHP Coverage Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Measure which `src/**` PHP code the legacy Behat suite exercises (via remote php-fpm PCOV coverage), and upload it to Codecov under an `e2e-behat` flag, nightly-only.

**Architecture:** A Symfony kernel-request subscriber (registered only in `APP_ENV=behat`) starts a PCOV-backed `CodeCoverage` per HTTP request and dumps a serialized per-request `.cov` on shutdown. Each nightly `test-behat` shard merges its own dumps into a Clover report and uploads it to Codecov with flag `e2e-behat`; Codecov unions the 10 shards server-side by flag (exactly like the 4-shard Playwright `e2e-playwright`). PCOV is baked into the image default-OFF and flipped ON only for the nightly via `PHP_INI_SCAN_DIR`.

**Tech Stack:** PHP 8.4, Symfony HttpKernel, PCOV (Debian `php8.4-pcov` from deb.sury.org), `phpunit/php-code-coverage` 10.1.16 (already vendored), Docker (httpd+php-fpm via supervisord), GitHub Actions, Codecov.

> **Refinement vs the spec (deliberate, more faithful to "mirror Playwright #339/#343"):** the spec described a two-mode collapse-then-central-merge in `coverage-summary`. Because `coverage-summary` runs on a bare runner with no PHP/Docker, and Playwright instead uploads **per-shard** coverage and lets Codecov merge by flag, this plan does the same: **one merge per shard inside `test-behat`** (where PHP is available) → per-shard Codecov upload under `e2e-behat`. (Format is **Clover**, not lcov: php-code-coverage 10.1.16 ships `Report\Clover` but has **no** lcov writer, and this repo already uploads PHPUnit clover to Codecov — Codecov merges by flag regardless of format.) This removes the two-mode script, the cross-shard merge, and all `coverage-summary` changes. The dump directory is also flat (`var/tests/behat-coverage/`, no `<shard>/` segment) because each shard is an isolated CI job/container/checkout.

## Global Constraints

- **Nightly-only:** every coverage CI step is gated `if: ${{ github.event_name == 'schedule' || github.event_name == 'workflow_dispatch' }}`. The `detect-changes` force-override (ci.yml:154-156) already cascades to `test-behat` on those events — no new gating.
- **Best-effort, never fail a job:** subscriber wrapped in `try/catch`; the merge CLI always `exit(0)`; CI steps use `continue-on-error: true`; Codecov `fail_ci_if_error: false`.
- **Zero PR cost:** PCOV is baked `pcov.enabled=0` (inert). On PR runs nothing is enabled → no dumps → nothing uploaded.
- **No new composer dependency:** use the vendored `phpunit/php-code-coverage` 10.1.16 API directly (`SebastianBergmann\CodeCoverage\*`). Do **not** add `phpcov`.
- **PCOV, line-only:** `pcov.directory=/srv/pim/src`. The coverage `Filter` mirrors `phpunit.xml.dist` `<source>`: include `src`, exclude suffixes `Test.php`, `Integration.php`, `EndToEnd.php`.
- **Code home:** namespace `Pim\Behat\Coverage\` → `tests/legacy/features/Behat/Coverage/` (autoload-dev, never loaded in prod). Subscriber service registered **only** in `config/services/behat/`.
- **Single toggle:** `pcov.enabled` (INI_SYSTEM). The subscriber gates on `extension_loaded('pcov') && (int) ini_get('pcov.enabled') === 1` — the same signal php-code-coverage uses to select the PCOV driver.
- **Codecov:** flag `e2e-behat`, `paths: [src/]`, `carryforward: true` (mirror `e2e-playwright`), `files: coverage-behat/clover.xml` (Clover, not lcov — see Tech Stack note).
- **Tests:** the PHP unit tests are driver-free and run in CI via the `test-phpunit-unit` job (`--testsuite PHPUnit_Unit_Test`). They are **not** run locally in this worktree (no `vendor/` here). The nightly is the only proof of real PCOV collection. No new Behat scenarios (byte-identical Behat contract).

---

## File Structure

- `tests/legacy/features/Behat/Coverage/CoverageCollectorInterface.php` — the `start()`/`stopAndDump()` contract (so the subscriber is testable with a spy).
- `tests/legacy/features/Behat/Coverage/CoverageCollector.php` — wraps a `CodeCoverage`; production `create()` factory + injectable ctor.
- `tests/legacy/features/Behat/Coverage/BehatCoverageSubscriber.php` — kernel.request → start + register shutdown dump; gated.
- `tests/legacy/features/Behat/Coverage/CoverageMerger.php` — merge `*.cov` in a dir → Clover.
- `tests/legacy/features/Behat/Coverage/merge-behat-coverage.php` — thin CLI wrapper over `CoverageMerger`.
- `tests/legacy/features/Behat/Coverage/FakeCoverageDriver.php` — test helper: a driver-free `Driver` for building `.cov` fixtures.
- `tests/legacy/features/Behat/Coverage/CoverageMergerTest.php`, `CoverageCollectorTest.php`, `BehatCoverageSubscriberTest.php` — unit tests.
- `phpunit.xml.dist` — add the Coverage dir to the `PHPUnit_Unit_Test` testsuite.
- `config/services/behat/coverage.yml` — register the subscriber (behat env only).
- `Dockerfile` — install `php${PHP_VERSION}-pcov` (dev stage) + COPY the pcov ini.
- `docker/build/pcov.ini` — `pcov.enabled=0` + `pcov.directory=/srv/pim/src` (default-off).
- `docker/php-coverage.d/pcov-on.ini` — `pcov.enabled=1` (nightly override).
- `docker-compose.yml` — add `PHP_INI_SCAN_DIR` passthrough on `httpd`.
- `.github/workflows/ci.yml` — nightly `PHP_INI_SCAN_DIR` on stack-up + per-shard merge + Codecov upload.
- `codecov.yml` — add the `e2e-behat` flag.

---

### Task 1: `CoverageMerger` + test helpers + testsuite wiring

**Files:**
- Create: `tests/legacy/features/Behat/Coverage/CoverageCollectorInterface.php`
- Create: `tests/legacy/features/Behat/Coverage/CoverageMerger.php`
- Create: `tests/legacy/features/Behat/Coverage/FakeCoverageDriver.php`
- Create: `tests/legacy/features/Behat/Coverage/CoverageMergerTest.php`
- Modify: `phpunit.xml.dist` (add the Coverage dir to `PHPUnit_Unit_Test`, ~line 110-117)

**Interfaces:**
- Produces (consumed by Tasks 2, 3, 4):
  - `interface CoverageCollectorInterface { public function start(): void; public function stopAndDump(string $dir): void; }`
  - `final class CoverageMerger { public function mergeDir(string $dir): ?CodeCoverage; public function writeClover(CodeCoverage $c, string $path): void; }` (Clover only — php-code-coverage 10.1.16 has no lcov writer)
  - `final class FakeCoverageDriver extends Driver` — a no-real-driver `Driver` used only by tests.

- [ ] **Step 1: Write the `CoverageCollectorInterface`**

Create `tests/legacy/features/Behat/Coverage/CoverageCollectorInterface.php`:
```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

/**
 * Collects PHP line coverage for a single HTTP request and dumps it to disk.
 * Extracted so the subscriber can be unit-tested with a spy (the real
 * collector needs a live PCOV driver, only present in the nightly).
 */
interface CoverageCollectorInterface
{
    public function start(): void;

    public function stopAndDump(string $dir): void;
}
```

- [ ] **Step 2: Write the `FakeCoverageDriver` test helper**

Create `tests/legacy/features/Behat/Coverage/FakeCoverageDriver.php`. `Driver` (php-code-coverage 10.1.16) has exactly three abstract methods: `nameAndVersion(): string`, `start(): void`, `stop(): RawCodeCoverageData`.
```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

/**
 * A driver-free {@see Driver} so tests can build real CodeCoverage objects
 * without PCOV/Xdebug loaded. start()/stop() are inert; synthetic coverage is
 * injected via CodeCoverage::append() in the tests, not via this driver.
 */
final class FakeCoverageDriver extends Driver
{
    public function nameAndVersion(): string
    {
        return 'FakeCoverageDriver 1.0';
    }

    public function start(): void
    {
    }

    public function stop(): RawCodeCoverageData
    {
        return RawCodeCoverageData::fromXdebugWithoutPathCoverage([]);
    }
}
```

- [ ] **Step 3: Write the failing test for `CoverageMerger`**

Create `tests/legacy/features/Behat/Coverage/CoverageMergerTest.php`. The test writes a tiny fixture source file with known executable lines, builds two `.cov` files that each cover a different line (via `CodeCoverage::append()` + `RawCodeCoverageData::fromXdebugWithoutPathCoverage`), merges them, and asserts (via `getData()->lineCoverage()`) that BOTH lines are hit (proving union) plus a Clover smoke test, plus the empty-dir returns null.
```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\PHP as PhpReport;

final class CoverageMergerTest extends TestCase
{
    private string $dir;
    private string $fixtureSrc;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/behatcov-' . uniqid('', true);
        mkdir($this->dir, 0o777, true);

        // A fixture source file with executable statements on lines 4 and 6.
        $this->fixtureSrc = $this->dir . '/Fixture.php';
        file_put_contents($this->fixtureSrc, <<<'PHP'
        <?php
        function fixture_target($x)
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
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->dir);
    }

    public function test_it_merges_cov_files_and_reports_the_union_of_covered_lines(): void
    {
        // dump A: line 4 executed
        $this->dumpCov($this->dir . '/a.cov', [4 => 1]);
        // dump B: line 6 executed
        $this->dumpCov($this->dir . '/b.cov', [6 => 1]);

        $merger = new CoverageMerger();
        $merged = $merger->mergeDir($this->dir);
        self::assertInstanceOf(CodeCoverage::class, $merged);

        // Assert the union directly on the merged data (no report-format parsing):
        // dump A covered line 4, dump B covered line 6 → both hit after merge.
        $lineCoverage = $merged->getData()->lineCoverage();
        self::assertArrayHasKey($this->fixtureSrc, $lineCoverage);
        self::assertNotEmpty($lineCoverage[$this->fixtureSrc][4] ?? [], 'line 4 covered by dump A');
        self::assertNotEmpty($lineCoverage[$this->fixtureSrc][6] ?? [], 'line 6 covered by dump B');

        // Smoke-test the Clover writer (the format Codecov ingests).
        $cloverPath = $this->dir . '/clover.xml';
        $merger->writeClover($merged, $cloverPath);
        self::assertFileExists($cloverPath);
        self::assertStringContainsString($this->fixtureSrc, file_get_contents($cloverPath));
    }

    public function test_it_returns_null_when_the_directory_has_no_cov_files(): void
    {
        self::assertNull((new CoverageMerger())->mergeDir($this->dir));
    }

    /** @param array<int,int> $executedLines line => xdebug count (1 = executed) */
    private function dumpCov(string $path, array $executedLines): void
    {
        $filter = new Filter();
        $filter->includeFile($this->fixtureSrc);

        $coverage = new CodeCoverage(new FakeCoverageDriver(), $filter);
        $coverage->append(
            RawCodeCoverageData::fromXdebugWithoutPathCoverage([$this->fixtureSrc => $executedLines]),
            'fixture',
        );

        (new PhpReport())->process($coverage, $path);
    }
}
```

- [ ] **Step 4: Add the Coverage dir to the `PHPUnit_Unit_Test` testsuite**

In `phpunit.xml.dist`, inside `<testsuite name="PHPUnit_Unit_Test">` (starts ~line 110), add one line alongside the existing `<directory suffix="Test.php">…</directory>` entries:
```xml
            <directory suffix="Test.php">tests/legacy/features/Behat/Coverage</directory>
```

- [ ] **Step 5: Write `CoverageMerger`**

Create `tests/legacy/features/Behat/Coverage/CoverageMerger.php`:
```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover;

/**
 * Merges the per-request serialized CodeCoverage dumps (*.cov, written by
 * {@see CoverageCollector} via Report\PHP) in a directory into a single object
 * and renders Clover (php-code-coverage 10.1.16 ships no lcov writer; Codecov
 * ingests Clover natively). Incremental (load → merge → free) to bound memory.
 */
final class CoverageMerger
{
    public function mergeDir(string $dir): ?CodeCoverage
    {
        $merged = null;

        foreach (glob(rtrim($dir, '/') . '/*.cov') ?: [] as $file) {
            // Report\PHP dumps `<?php return \unserialize(...);` → include returns the object.
            $coverage = @include $file;
            if (!$coverage instanceof CodeCoverage) {
                continue;
            }

            if ($merged === null) {
                $merged = $coverage;
            } else {
                $merged->merge($coverage);
            }

            unset($coverage);
        }

        return $merged;
    }

    public function writeClover(CodeCoverage $coverage, string $path): void
    {
        (new Clover())->process($coverage, $path);
    }
}
```

- [ ] **Step 6: Verify the test passes (CI)**

The unit test runs in CI in the `test-phpunit-unit` job (`--testsuite PHPUnit_Unit_Test`). It is **not** run locally (this worktree has no `vendor/`). Expected in CI: `CoverageMergerTest` passes (2 tests). Verify by review that the merge/union logic and the `include`-based load match `Report\PHP`'s output format.

- [ ] **Step 7: Commit**
```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/behat-e2e-coverage
git add tests/legacy/features/Behat/Coverage/CoverageCollectorInterface.php \
        tests/legacy/features/Behat/Coverage/FakeCoverageDriver.php \
        tests/legacy/features/Behat/Coverage/CoverageMerger.php \
        tests/legacy/features/Behat/Coverage/CoverageMergerTest.php \
        phpunit.xml.dist
git commit -m "feat(behat-coverage): CoverageMerger + driver-free test fixtures"
```

---

### Task 2: `CoverageCollector`

**Files:**
- Create: `tests/legacy/features/Behat/Coverage/CoverageCollector.php`
- Create: `tests/legacy/features/Behat/Coverage/CoverageCollectorTest.php`

**Interfaces:**
- Consumes: `CoverageCollectorInterface`, `FakeCoverageDriver` (Task 1).
- Produces (consumed by Task 3): `final class CoverageCollector implements CoverageCollectorInterface` with `public static function create(): self` (production factory using the PCOV driver) and `public function __construct(CodeCoverage $coverage)` (injectable for tests).

- [ ] **Step 1: Write the failing test**

Create `tests/legacy/features/Behat/Coverage/CoverageCollectorTest.php`. It injects a `CodeCoverage` built on `FakeCoverageDriver`, calls `start()`/`stopAndDump()`, and asserts a uniquely-named `.cov` is written and reloads to a `CodeCoverage`.
```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter;

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

    public function test_stop_and_dump_writes_a_uniquely_named_loadable_cov_file(): void
    {
        $collector = new CoverageCollector(new CodeCoverage(new FakeCoverageDriver(), new Filter()));

        $collector->start();
        $collector->stopAndDump($this->dir); // creates the dir if missing

        $files = glob($this->dir . '/*.cov') ?: [];
        self::assertCount(1, $files);
        self::assertMatchesRegularExpression('/\/\d+-[0-9a-f.]+\.cov$/', $files[0]);

        $loaded = include $files[0];
        self::assertInstanceOf(CodeCoverage::class, $loaded);
    }
}
```

- [ ] **Step 2: Write `CoverageCollector`**

Create `tests/legacy/features/Behat/Coverage/CoverageCollector.php`. The `create()` factory mirrors `phpunit.xml.dist`'s `<source>` filter exactly (`Filter::excludeDirectory($dir, $suffix)` matches PHPUnit's suffix excludes).
```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\PHP as PhpReport;

/**
 * Wraps a CodeCoverage for one HTTP request and serializes it to a unique
 * per-request .cov (Report\PHP format) so many fpm workers never collide.
 */
final class CoverageCollector implements CoverageCollectorInterface
{
    public function __construct(private readonly CodeCoverage $coverage)
    {
    }

    /**
     * Production factory: line coverage over src/**, using whatever driver is
     * active (PCOV in the nightly). Only call this when a driver is available.
     */
    public static function create(): self
    {
        $filter = new Filter();
        $filter->includeDirectory('/srv/pim/src');
        $filter->excludeDirectory('/srv/pim/src', 'Test.php');
        $filter->excludeDirectory('/srv/pim/src', 'Integration.php');
        $filter->excludeDirectory('/srv/pim/src', 'EndToEnd.php');

        return new self(new CodeCoverage((new Selector())->forLineCoverage($filter), $filter));
    }

    public function start(): void
    {
        $this->coverage->start('behat');
    }

    public function stopAndDump(string $dir): void
    {
        $this->coverage->stop();

        if (!is_dir($dir)) {
            @mkdir($dir, 0o777, true);
        }

        $file = $dir . '/' . getmypid() . '-' . uniqid('', true) . '.cov';
        (new PhpReport())->process($this->coverage, $file);
    }
}
```

- [ ] **Step 3: Verify the test passes (CI)**

Runs in CI (`test-phpunit-unit`). Expected: `CoverageCollectorTest` passes (1 test). `create()` is exercised only by the nightly (needs a live driver) — verify by review that the filter mirrors `phpunit.xml.dist`.

- [ ] **Step 4: Commit**
```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/behat-e2e-coverage
git add tests/legacy/features/Behat/Coverage/CoverageCollector.php \
        tests/legacy/features/Behat/Coverage/CoverageCollectorTest.php
git commit -m "feat(behat-coverage): per-request CoverageCollector"
```

---

### Task 3: `BehatCoverageSubscriber` + behat service registration

**Files:**
- Create: `tests/legacy/features/Behat/Coverage/BehatCoverageSubscriber.php`
- Create: `tests/legacy/features/Behat/Coverage/BehatCoverageSubscriberTest.php`
- Create: `config/services/behat/coverage.yml`

**Interfaces:**
- Consumes: `CoverageCollectorInterface`, `CoverageCollector::create()` (Task 2).
- Produces: a `kernel.event_subscriber` service, active only when PCOV is enabled.

- [ ] **Step 1: Write the failing test**

Create `tests/legacy/features/Behat/Coverage/BehatCoverageSubscriberTest.php`. Uses a spy collector to assert the gate: started on a main request when enabled, never started when disabled or on a sub-request.
```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class BehatCoverageSubscriberTest extends TestCase
{
    public function test_it_subscribes_to_the_kernel_request_event(): void
    {
        self::assertArrayHasKey(KernelEvents::REQUEST, BehatCoverageSubscriber::getSubscribedEvents());
    }

    public function test_it_starts_collection_on_a_main_request_when_enabled(): void
    {
        $collector = new SpyCollector();
        $subscriber = new BehatCoverageSubscriber(true, '/tmp/whatever', $collector);

        $subscriber->onRequest($this->mainRequestEvent());

        self::assertSame(1, $collector->startCalls);
    }

    public function test_it_does_nothing_when_disabled(): void
    {
        $collector = new SpyCollector();
        $subscriber = new BehatCoverageSubscriber(false, '/tmp/whatever', $collector);

        $subscriber->onRequest($this->mainRequestEvent());

        self::assertSame(0, $collector->startCalls);
    }

    public function test_it_ignores_sub_requests(): void
    {
        $collector = new SpyCollector();
        $subscriber = new BehatCoverageSubscriber(true, '/tmp/whatever', $collector);

        $subscriber->onRequest($this->subRequestEvent());

        self::assertSame(0, $collector->startCalls);
    }

    private function mainRequestEvent(): RequestEvent
    {
        return new RequestEvent($this->kernel(), new Request(), HttpKernelInterface::MAIN_REQUEST);
    }

    private function subRequestEvent(): RequestEvent
    {
        return new RequestEvent($this->kernel(), new Request(), HttpKernelInterface::SUB_REQUEST);
    }

    private function kernel(): HttpKernelInterface
    {
        return new class implements HttpKernelInterface {
            public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): \Symfony\Component\HttpFoundation\Response
            {
                return new \Symfony\Component\HttpFoundation\Response();
            }
        };
    }
}

final class SpyCollector implements CoverageCollectorInterface
{
    public int $startCalls = 0;

    public function start(): void
    {
        $this->startCalls++;
    }

    public function stopAndDump(string $dir): void
    {
    }
}
```

- [ ] **Step 2: Write `BehatCoverageSubscriber`**

Create `tests/legacy/features/Behat/Coverage/BehatCoverageSubscriber.php`. The production wiring uses `fromEnvironment()` (reads `ini_get('pcov.enabled')`); the ctor stays injectable for the test.
```php
<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Starts PCOV line coverage on each main request and dumps it on shutdown.
 * Registered only in APP_ENV=behat; a no-op unless PCOV is enabled (nightly),
 * so normal behat runs pay nothing. Best-effort — never breaks a scenario.
 */
final class BehatCoverageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly bool $enabled,
        private readonly string $dumpDir,
        private readonly ?CoverageCollectorInterface $collector = null,
    ) {
    }

    public static function fromEnvironment(string $dumpDir): self
    {
        $enabled = \extension_loaded('pcov') && (int) \ini_get('pcov.enabled') === 1;

        return new self($enabled, $dumpDir);
    }

    /**
     * @return array<string, array{0: string, 1: int}>
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onRequest', 1024]];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$this->enabled || !$event->isMainRequest()) {
            return;
        }

        try {
            $collector = $this->collector ?? CoverageCollector::create();
            $collector->start();

            $dir = $this->dumpDir;
            \register_shutdown_function(static function () use ($collector, $dir): void {
                try {
                    $collector->stopAndDump($dir);
                } catch (\Throwable) {
                    // best-effort: a coverage dump must never affect the request outcome
                }
            });
        } catch (\Throwable) {
            // best-effort: never break a scenario if a driver is unexpectedly missing
        }
    }
}
```

- [ ] **Step 3: Register the service (behat env only)**

Create `config/services/behat/coverage.yml` — glob-loaded only in `APP_ENV=behat` (Kernel:52). The file follows the explicit-definition style of `config/services/behat/services.yml` (no autoconfigure → explicit tag), and uses the `fromEnvironment` factory so the gate is computed at container build.
```yaml
services:
    _defaults:
        public: true

    Pim\Behat\Coverage\BehatCoverageSubscriber:
        factory: ['Pim\Behat\Coverage\BehatCoverageSubscriber', 'fromEnvironment']
        arguments:
            - '%kernel.project_dir%/var/tests/behat-coverage'
        tags:
            - { name: kernel.event_subscriber }
```

- [ ] **Step 4: Verify the test passes (CI)**

Runs in CI (`test-phpunit-unit`). Expected: `BehatCoverageSubscriberTest` passes (4 tests). The `fromEnvironment` factory + service wiring are exercised by the behat boot (nightly); verify by review that `config/services/behat/coverage.yml` matches the loader glob and the factory signature.

- [ ] **Step 5: Commit**
```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/behat-e2e-coverage
git add tests/legacy/features/Behat/Coverage/BehatCoverageSubscriber.php \
        tests/legacy/features/Behat/Coverage/BehatCoverageSubscriberTest.php \
        config/services/behat/coverage.yml
git commit -m "feat(behat-coverage): kernel-request subscriber + behat service wiring"
```

---

### Task 4: `merge-behat-coverage.php` CLI

**Files:**
- Create: `tests/legacy/features/Behat/Coverage/merge-behat-coverage.php`

**Interfaces:**
- Consumes: `CoverageMerger` (Task 1).
- Produces: a CLI invoked in CI as `php tests/legacy/features/Behat/Coverage/merge-behat-coverage.php --in <dir> --clover <path>`. Always exits 0; warns loudly on zero dumps (the anti-#328 tripwire).

- [ ] **Step 1: Write the CLI**

Create `tests/legacy/features/Behat/Coverage/merge-behat-coverage.php`. It is intentionally thin (the logic lives in the unit-tested `CoverageMerger`); no separate unit test — it is exercised by the nightly and reviewed here.
```php
<?php

declare(strict_types=1);

// Thin CLI over CoverageMerger. Best-effort: always exit 0 so it can never fail
// the nightly Behat job. Run inside the httpd container where PCOV + vendor exist.

require dirname(__DIR__, 4) . '/vendor/autoload.php';

use Pim\Behat\Coverage\CoverageMerger;

$options = getopt('', ['in:', 'clover:']);
$inDir = $options['in'] ?? null;
$clover = $options['clover'] ?? null;

if ($inDir === null || $clover === null) {
    fwrite(STDERR, "[behat-coverage] usage: --in <dir> --clover <path>\n");
    exit(0);
}

try {
    $merger = new CoverageMerger();
    $coverage = $merger->mergeDir($inDir);

    if ($coverage === null) {
        fwrite(STDERR, "[behat-coverage] WARNING: 0 .cov dumps in {$inDir} — PCOV likely not active in the fpm SAPI; nothing to upload\n");
        exit(0);
    }

    if (!is_dir(dirname($clover))) {
        @mkdir(dirname($clover), 0o777, true);
    }

    $merger->writeClover($coverage, $clover);

    fwrite(STDOUT, "[behat-coverage] wrote {$clover}\n");
} catch (\Throwable $e) {
    fwrite(STDERR, "[behat-coverage] merge failed (ignored): {$e->getMessage()}\n");
}

exit(0);
```

- [ ] **Step 2: Verify by review**

No unit test (thin CLI over the tested `CoverageMerger`). Verify: `require` path resolves to `/srv/pim/vendor/autoload.php` (`dirname(__DIR__, 4)` from `tests/legacy/features/Behat/Coverage/` → repo root), the tripwire warning fires on empty input, and it always `exit(0)`.

- [ ] **Step 3: Commit**
```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/behat-e2e-coverage
git add tests/legacy/features/Behat/Coverage/merge-behat-coverage.php
git commit -m "feat(behat-coverage): merge CLI (per-shard .cov → clover, best-effort)"
```

---

### Task 5: Docker image — PCOV default-off + nightly override

**Files:**
- Modify: `Dockerfile` (dev stage apt install ~line 72-84, and the COPY ini block ~line 86-89)
- Create: `docker/build/pcov.ini`
- Create: `docker/php-coverage.d/pcov-on.ini`
- Modify: `docker-compose.yml` (httpd `environment:` block, ~line 25-33)

**Interfaces:**
- Produces: PCOV present in the `ci`-target image, `pcov.enabled=0` by default; a committed `pcov-on.ini` and a `PHP_INI_SCAN_DIR` passthrough that the nightly uses to flip it on.

- [ ] **Step 1: Create the default-off PCOV ini**

Create `docker/build/pcov.ini`:
```ini
pcov.enabled=0
pcov.directory=/srv/pim/src
```

- [ ] **Step 2: Create the nightly override ini**

Create `docker/php-coverage.d/pcov-on.ini`:
```ini
pcov.enabled=1
```

- [ ] **Step 3: Install PCOV in the `dev` stage + copy the ini**

In `Dockerfile`, add `php${PHP_VERSION}-pcov` to the `dev` stage apt install list (the block that installs `php${PHP_VERSION}-xdebug`, ~line 72-84). Add the package line next to xdebug:
```
        php${PHP_VERSION}-pcov \
        php${PHP_VERSION}-xdebug \
```
Then, next to the existing xdebug ini COPYs (~line 86-89), add COPYs for the pcov ini into **both** cli and fpm conf.d (mirroring the xdebug pattern):
```
COPY docker/build/pcov.ini /etc/php/${PHP_VERSION}/cli/conf.d/99-akeneo-pcov.ini
COPY docker/build/pcov.ini /etc/php/${PHP_VERSION}/fpm/conf.d/99-akeneo-pcov.ini
```
Do **not** add pcov to the `ci`-stage removal list (unlike xdebug) — it must survive into `ci`, inert (`pcov.enabled=0`). The `php${PHP_VERSION}-pcov` deb package auto-enables `extension=pcov.so` via its own `20-pcov.ini`; our `99-akeneo-pcov.ini` (loaded after, alphabetically) sets the directives.

- [ ] **Step 4: Add the `PHP_INI_SCAN_DIR` passthrough on `httpd`**

In `docker-compose.yml`, in the `httpd` service `environment:` block (after the `XDEBUG_MODE` line, ~line 30), add:
```yaml
      PHP_INI_SCAN_DIR: '${PHP_INI_SCAN_DIR:-}'
```
Default empty → unchanged behaviour on every non-nightly run. The nightly job (Task 6) exports `PHP_INI_SCAN_DIR=:/srv/pim/docker/php-coverage.d` (leading colon = default conf.d scanned first, then this dir, whose `pcov.enabled=1` overrides the baked `0`).

- [ ] **Step 5: Verify by review + CI image build**

No unit test (infra). Verify by review: pcov installs in `dev` (inherited by `ci`), is **not** removed by the `ci` stage, `pcov.directory=/srv/pim/src` matches the coverage filter, and the compose passthrough defaults empty. The image build itself is validated by the CI `reusable-image` job on this PR. **Anti-#328 note:** whether `PHP_INI_SCAN_DIR` reaches the fpm master (via supervisord) is proven by the nightly's dump count (Task 6 tripwire), not by this build.

- [ ] **Step 6: Commit**
```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/behat-e2e-coverage
git add Dockerfile docker/build/pcov.ini docker/php-coverage.d/pcov-on.ini docker-compose.yml
git commit -m "build(behat-coverage): PCOV in the image (default-off) + nightly PHP_INI_SCAN_DIR passthrough"
```

---

### Task 6: CI wiring — nightly enablement, per-shard merge, Codecov upload

**Files:**
- Modify: `.github/workflows/ci.yml` (test-behat: the stack-up step ~1232-1239; a new merge+upload block before `Archive behat artifacts` ~1515)
- Modify: `codecov.yml` (add the `e2e-behat` flag)

**Interfaces:**
- Consumes: the merge CLI (Task 4), the compose passthrough (Task 5).
- Produces: the nightly `e2e-behat` Codecov flag.

- [ ] **Step 1: Enable PCOV on the nightly stack-up**

In `.github/workflows/ci.yml`, the `Setup test database` step (~1232) runs `APP_ENV=behat castor docker:up 'httpd …'` (line 1236). Add a step-level `env:` so the httpd container's fpm master sees `PHP_INI_SCAN_DIR` **only** on the nightly:
```yaml
      - name: Setup test database
        env:
          PHP_INI_SCAN_DIR: ${{ (github.event_name == 'schedule' || github.event_name == 'workflow_dispatch') && ':/srv/pim/docker/php-coverage.d' || '' }}
        run: |
```
(The rest of the step body is unchanged. On PR runs the value is empty → PCOV stays disabled.)

- [ ] **Step 2: Merge + upload per shard (before the archive step)**

In `.github/workflows/ci.yml`, immediately **before** `- name: Archive behat artifacts` (~line 1515), insert two nightly-gated, best-effort steps. The merge runs in the `httpd` container (PCOV + vendor present); `coverage-behat/clover.xml` lands on the runner via the `./:/srv/pim` bind-mount.
```yaml
      - name: Merge Behat PHP coverage (shard ${{ matrix.shard }})
        if: ${{ github.event_name == 'schedule' || github.event_name == 'workflow_dispatch' }}
        continue-on-error: true
        run: |
          docker-compose exec -u www-data -T httpd \
            php tests/legacy/features/Behat/Coverage/merge-behat-coverage.php \
            --in var/tests/behat-coverage \
            --clover coverage-behat/clover.xml

      - name: Upload Behat PHP coverage to Codecov (shard ${{ matrix.shard }})
        if: ${{ github.event_name == 'schedule' || github.event_name == 'workflow_dispatch' }}
        continue-on-error: true
        uses: codecov/codecov-action@v4
        with:
          files: coverage-behat/clover.xml
          flags: e2e-behat
          disable_search: true
          fail_ci_if_error: false
        env:
          CODECOV_TOKEN: ${{ secrets.CODECOV_TOKEN }}
```

- [ ] **Step 3: Declare the `e2e-behat` flag in `codecov.yml`**

In `codecov.yml`, add the flag under the existing `flags:` map (mirroring `e2e-playwright`, but `paths` is `src/` only — PHP coverage has no `public/bundles/`):
```yaml
  e2e-behat:
    carryforward: true
    paths:
      - src/
```

- [ ] **Step 4: Verify by review + YAML lint**

No unit test (CI config). Validate YAML: `python3 -c "import yaml,sys; yaml.safe_load(open('.github/workflows/ci.yml')); yaml.safe_load(open('codecov.yml')); print('ok')"` (run from the worktree). Verify by review: both steps are nightly-gated + `continue-on-error`, the merge runs in `httpd` (not `php` — the running stack is `httpd`), the clover path matches the upload `files:`, and the flag mirrors `e2e-playwright`.

- [ ] **Step 5: Commit**
```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/behat-e2e-coverage
git add .github/workflows/ci.yml codecov.yml
git commit -m "ci(behat-coverage): nightly PCOV enablement + per-shard e2e-behat Codecov upload"
```

---

## Self-Review

**1. Spec coverage.** Scope=remote fpm (subscriber+collector, Tasks 2-3 ✓); driver=PCOV default-off + nightly toggle (Task 5 ✓); single `pcov.enabled` gate (subscriber `fromEnvironment`, Task 3 ✓); no new composer dep (uses vendored php-code-coverage ✓); filter mirrors phpunit.xml.dist (Task 2 ✓); best-effort everywhere (Tasks 3,4,6 ✓); tests via CI (Tasks 1-3 ✓); Codecov `e2e-behat` (Task 6 ✓); byte-identical Behat (no scenarios ✓). **Deviation, flagged:** per-shard Codecov upload replaces the spec's central `coverage-summary` merge (documented in the header, more faithful to the Playwright mirror). The two-mode script → single mode; the `<shard>/` dir segment → flat dir (each shard is isolated).

**2. Placeholder scan.** No TBD/TODO; every code step shows complete code; every CI/infra step gives exact edits + anchors.

**3. Type consistency.** `CoverageCollectorInterface { start(): void; stopAndDump(string): void }` — implemented by `CoverageCollector` (Task 2), consumed by `BehatCoverageSubscriber` (Task 3) and the `SpyCollector` test double (Task 3). `CoverageMerger { mergeDir(): ?CodeCoverage; writeClover() }` — used by the CLI (Task 4) and its own test (Task 1). `FakeCoverageDriver extends Driver` (3 abstract methods) — used by Tasks 1 & 2 tests. `merge-behat-coverage.php` flags `--in/--clover` match the CI invocation (Task 6). `var/tests/behat-coverage` is the dump dir in both the service arg (Task 3) and the merge `--in` (Task 6). Consistent.

**Note on mutation testing:** the new PHP lives under `tests/` (not `src/`), so it is outside Infection's `src/`-scoped mutation shards → no MSI pressure. The unit tests above are for correctness, not mutation coverage.
