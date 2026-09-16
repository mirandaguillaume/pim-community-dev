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

/**
 * Line numbers strictly inside a function body, from PHP's own tokenizer.
 *
 * Used to tell "this scenario executed the file" from "the container instantiated the service".
 * A constructor signature line executes on instantiation and PCOV records it, so presence in the
 * dump proves nothing on its own -- see the comment at the call site for the measurements.
 *
 * Results are memoised: a shard covers ~2,500 files across ~50 scenarios, so without the cache the
 * same file would be tokenised dozens of times.
 */
function bodyLines(string $file): array
{
    static $cache = [];

    if (isset($cache[$file])) {
        return $cache[$file];
    }

    $src = @file_get_contents($file);
    if (false === $src) {
        return $cache[$file] = [];
    }

    $tokens = token_get_all($src);
    $count = count($tokens);
    $lines = [];

    for ($i = 0; $i < $count; $i++) {
        if (!is_array($tokens[$i]) || T_FUNCTION !== $tokens[$i][0]) {
            continue;
        }

        // Walk past the signature to the body's opening brace. Depth tracking keeps parentheses in
        // parameter lists and return types from being mistaken for the end of the signature; a `;`
        // at depth 0 means an abstract or interface method, which has no body at all.
        $depth = 0;
        $open = null;
        for ($j = $i; $j < $count; $j++) {
            $text = is_array($tokens[$j]) ? $tokens[$j][1] : $tokens[$j];
            if ('(' === $text) {
                $depth++;
            } elseif (')' === $text) {
                $depth--;
            } elseif (';' === $text && 0 === $depth) {
                break;
            } elseif ('{' === $text && 0 === $depth) {
                $open = $j;
                break;
            }
        }

        if (null === $open) {
            continue;
        }

        $braces = 0;
        for ($k = $open; $k < $count; $k++) {
            $text = is_array($tokens[$k]) ? $tokens[$k][1] : $tokens[$k];
            if ('{' === $text) {
                $braces++;
            } elseif ('}' === $text) {
                $braces--;
                if (0 === $braces) {
                    break;
                }
            }
            // Strictly after the opening brace, so the signature line is never counted even when
            // the brace sits on it.
            if ($k > $open && is_array($tokens[$k])) {
                $lines[$tokens[$k][2]] = true;
            }
        }

        $i = $k;
    }

    return $cache[$file] = $lines;
}

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
            "[php-inventory] WARNING: 0 records in %s — PCOV was most likely not active in the SAPI "
            . "serving the app (Apache, via the httpd service), or no marker was ever written. Note "
            . "the collector is inert under CLI by design, so a suite whose scenarios never issue an "
            . "HTTP request produces nothing here\n",
            $inDir,
        ));
        exit(0);
    }

    $inventory = [];
    $keptFiles = 0;
    $unattributed = 0;
    $droppedGroups = 0;
    $instantiatedOnly = 0;

    foreach ($byTest as $testId => $hits) {
        if ($testId === '') {
            // Requests with no marker: warm-up, health checks, anything before the first scenario.
            // Keeping them would list "" as a covering scenario for every file they touched --
            // join()/invert() do not special-case an empty id, so it would survive straight into
            // files.json and make those files read as already covered by a test, never flagged for
            // migration. CoverageMerger::unionDir() (the Clover path) still wants these records, so
            // only this per-test view drops them, not the recorder.
            $unattributed = count($hits);
            $droppedGroups = 1;
            continue;
        }

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

            $numbers = array_keys($lines);
            sort($numbers);

            // The whole point of this filter: PCOV marks a file as covered the moment the DI
            // container instantiates the service, because the constructor signature line executes.
            // No method body ever runs. Measured on one shard of run 31107185339: the median
            // (scenario, file) pair had THREE covered lines, 39% had exactly one, and 48% of files
            // never exceeded three across every scenario. Sampled, those lines are
            // `public function __construct(`, a closing brace, and a blank line.
            //
            // That is what made the inventory unusable: a median of 1,940 "covered" files per
            // scenario is the container booting, not the scenario working, and it is why the
            // single-scenario set was unstable between runs -- which services get instantiated
            // depends on which pages were visited, not on what was exercised.
            //
            // So require at least one covered line strictly inside a function body, using PHP's own
            // tokenizer rather than a line-count threshold (a threshold would be arbitrary, and a
            // small genuinely-exercised method would fail it). On that same shard this drops 60% of
            // pairs, 2,550 files to 1,489, and leaves e.g. AclPrivilegeRepository with 201 of its
            // 212 lines -- exercised code survives, instantiation does not.
            $body = bodyLines($file);
            if ([] === $body) {
                continue; // interface, enum or abstract-only: nothing executable to attribute
            }
            $executed = array_values(array_filter($numbers, static fn (int $n): bool => isset($body[$n])));
            if ([] === $executed) {
                $instantiatedOnly++;
                continue;
            }

            $relative = substr($file, strlen($repoRoot) + 1);
            $entry[$relative] = $executed;
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
            count($byTest) - $droppedGroups,
            $srcReal,
        ));
    }

    if (!is_dir(dirname($out))) {
        @mkdir(dirname($out), 0o775, true);
    }

    file_put_contents($out, json_encode($inventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

    fwrite(STDOUT, sprintf(
        // The two counts are NOT comparable: %d file entries is post-filter (under src/, minus the
        // test suffixes), whereas the dropped count is the raw size of the unattributed group and so
        // still includes vendor/ and everything else the warm-up requests touched.
        "[php-inventory] wrote %s (%d tests, %d file entries kept; %d dropped as instantiated-only; "
        . "dropped an unattributed group of %d unfiltered file entries)\n",
        $out,
        count($inventory),
        $keptFiles,
        $instantiatedOnly,
        $unattributed,
    ));
} catch (\Throwable $e) {
    fwrite(STDERR, "[php-inventory] failed (ignored): {$e->getMessage()}\n");
}

exit(0);
