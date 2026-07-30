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
