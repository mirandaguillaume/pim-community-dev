<?php

declare(strict_types=1);

/**
 * Finds all PHPUnit test files for the given testsuite(s) by parsing phpunit.xml.dist directly.
 *
 * Usage:
 *   php find_phpunit.php -c <config-path> --testsuite <suite1,suite2,...>
 *
 * The config-path can be a directory (will look for phpunit.xml.dist or phpunit.xml inside)
 * or a direct path to a phpunit XML file.
 *
 * Outputs one absolute file path per line.
 */

$configPath = '';
$testSuiteNames = '';

$args = array_slice($argv, 1);
for ($i = 0, $count = count($args); $i < $count; $i++) {
    if ($args[$i] === '-c' && isset($args[$i + 1])) {
        $configPath = $args[++$i];
    }
    if ($args[$i] === '--testsuite' && isset($args[$i + 1])) {
        $testSuiteNames = $args[++$i];
    }
}

if ($configPath === '' || $testSuiteNames === '') {
    fwrite(STDERR, "Usage: php find_phpunit.php -c <config-path> --testsuite <name1,name2,...>\n");
    exit(1);
}

// Resolve config file path
$configFile = $configPath;
if (is_dir($configPath)) {
    if (file_exists($configPath . '/phpunit.xml.dist')) {
        $configFile = $configPath . '/phpunit.xml.dist';
    } elseif (file_exists($configPath . '/phpunit.xml')) {
        $configFile = $configPath . '/phpunit.xml';
    } else {
        fwrite(STDERR, "No phpunit.xml.dist or phpunit.xml found in: $configPath\n");
        exit(1);
    }
}

if (!file_exists($configFile)) {
    fwrite(STDERR, "Config file not found: $configFile\n");
    exit(1);
}

$configDir = dirname(realpath($configFile));
$requestedSuites = array_map('trim', explode(',', $testSuiteNames));

$xml = simplexml_load_file($configFile);
if ($xml === false) {
    fwrite(STDERR, "Failed to parse XML: $configFile\n");
    exit(1);
}

$testFiles = [];
$excludePaths = [];

foreach ($xml->testsuites->testsuite as $suite) {
    $suiteName = (string) $suite['name'];
    if (!in_array($suiteName, $requestedSuites, true)) {
        continue;
    }

    // Collect exclude patterns
    foreach ($suite->exclude as $exclude) {
        $excludeDir = (string) $exclude;
        $resolved = realpath($configDir . '/' . $excludeDir);
        if ($resolved !== false) {
            $excludePaths[] = $resolved;
        }
    }

    // Collect files from <directory> elements
    foreach ($suite->directory as $dir) {
        $suffix = (string) ($dir['suffix'] ?? 'Test.php');
        $dirPath = (string) $dir;
        $resolvedDir = $configDir . '/' . $dirPath;

        if (!is_dir($resolvedDir)) {
            continue;
        }

        $realDir = realpath($resolvedDir);
        if ($realDir === false) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($realDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if (!$file->isFile()) {
                continue;
            }
            $filePath = $file->getRealPath();
            if ($filePath === false) {
                continue;
            }
            if (!str_ends_with($filePath, $suffix)) {
                continue;
            }
            $testFiles[] = $filePath;
        }
    }

    // Collect files from <file> elements
    foreach ($suite->file as $file) {
        $filePath = (string) $file;
        $resolvedFile = realpath($configDir . '/' . $filePath);
        if ($resolvedFile !== false && is_file($resolvedFile)) {
            $testFiles[] = $resolvedFile;
        }
    }
}

// Filter out excluded paths
if (!empty($excludePaths)) {
    $testFiles = array_filter($testFiles, function (string $file) use ($excludePaths): bool {
        foreach ($excludePaths as $excludePath) {
            if (str_starts_with($file, $excludePath . '/')) {
                return false;
            }
        }
        return true;
    });
}

// Deduplicate and sort
$testFiles = array_unique($testFiles);
sort($testFiles);

foreach ($testFiles as $file) {
    echo $file . "\n";
}
