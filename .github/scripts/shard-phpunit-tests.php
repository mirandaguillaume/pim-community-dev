#!/usr/bin/env php
<?php
/**
 * PHPUnit test sharding script
 *
 * Splits PHPUnit test files into shards for parallel execution.
 * Uses a hash-based distribution to ensure deterministic and balanced shards.
 *
 * Usage: php shard-phpunit-tests.php <shard-number> <total-shards> [test-directory]
 *
 * Example: php shard-phpunit-tests.php 1 4 tests/back
 *          Returns a list of test files for shard 1 out of 4
 */

if ($argc < 3) {
    fwrite(STDERR, "Usage: {$argv[0]} <shard-number> <total-shards> [test-directory]\n");
    exit(1);
}

$shardNumber = (int) $argv[1];
$totalShards = (int) $argv[2];
$testDirectory = $argv[3] ?? 'tests/back';

if ($shardNumber < 1 || $shardNumber > $totalShards) {
    fwrite(STDERR, "Error: shard-number must be between 1 and total-shards\n");
    exit(1);
}

if ($totalShards < 1) {
    fwrite(STDERR, "Error: total-shards must be at least 1\n");
    exit(1);
}

// Find all test files
$testFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($testDirectory, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && preg_match('/Test\.php$/', $file->getFilename())) {
        $testFiles[] = $file->getPathname();
    }
}

// Sort for deterministic ordering
sort($testFiles);

// Distribute files to shards using hash-based assignment
// This ensures even distribution and determinism
$shardFiles = [];
foreach ($testFiles as $file) {
    // Use crc32 for fast, deterministic hashing
    $hash = crc32($file);
    $assignedShard = ($hash % $totalShards) + 1;

    if ($assignedShard === $shardNumber) {
        $shardFiles[] = $file;
    }
}

// Output the files for this shard (one per line)
foreach ($shardFiles as $file) {
    echo $file . "\n";
}

// Output stats to stderr
$totalFiles = count($testFiles);
$shardFileCount = count($shardFiles);
fwrite(STDERR, "Shard {$shardNumber}/{$totalShards}: {$shardFileCount} files out of {$totalFiles} total\n");
