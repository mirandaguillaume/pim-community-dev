<?php

declare(strict_types=1);

// Thin CLI over CoverageMerger. Best-effort: ALWAYS exit 0 so it can never fail the nightly Behat
// job. Run inside the httpd container, where the vendor tree and the bind-mounted sources live.

require dirname(__DIR__, 5) . '/vendor/autoload.php';

use Pim\Behat\Coverage\CoverageMerger;

$options = getopt('', ['in:', 'clover:', 'src::', 'cache::']);
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
    $coveredLines = 0;
    foreach ($coverage->getData()->lineCoverage() as $lines) {
        foreach ($lines as $tests) {
            if ($tests !== []) {
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
