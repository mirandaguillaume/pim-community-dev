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
    //
    // php-code-coverage stores three distinct line states per file
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

    // Both messages below count DUMPED files -- count($union), the pre-filter total -- and say so.
    // That is deliberately not the number of files in the report: the filter drops some, and
    // addUncoveredFilesFromFilter() adds every untouched src/ file at getReport() time, so the two
    // never match. Naming it plainly beats printing a figure that looks like a report total.
    $dumpedFiles = count($union);

    if ($coveredLines === 0) {
        fwrite(STDERR, sprintf(
            "[behat-coverage] WARNING: merged %d dumped lines across %d dumped files but 0 covered "
            . "lines survived the %s filter — check that dumped paths match the filter's paths\n",
            $dumpedLines,
            $dumpedFiles,
            is_string($srcDir) ? $srcDir : '/srv/pim/src',
        ));
    }

    if (!is_dir(dirname($clover))) {
        @mkdir(dirname($clover), 0o777, true);
    }

    $merger->writeClover($coverage, $clover);

    fwrite(STDOUT, sprintf(
        "[behat-coverage] wrote %s (%d dumped files, %d covered lines)\n",
        $clover,
        $dumpedFiles,
        $coveredLines,
    ));
} catch (\Throwable $e) {
    fwrite(STDERR, "[behat-coverage] merge failed (ignored): {$e->getMessage()}\n");
}

exit(0);
