<?php

declare(strict_types=1);

// Thin CLI over CoverageMerger. Best-effort: always exit 0 so it can never fail
// the nightly Behat job. Run inside the httpd container where PCOV + vendor exist.

require dirname(__DIR__, 5) . '/vendor/autoload.php';

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
