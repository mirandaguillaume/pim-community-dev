#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Migration script: Remove kernel.event_subscriber tags from YAML and add autoconfigure: true
 *
 * Usage: php bin/migrate-yaml-subscribers.php [--dry-run]
 */

$dryRun = in_array('--dry-run', $argv, true);
$rootDir = dirname(__DIR__);

$yamlFiles = findYamlFiles($rootDir);
echo sprintf("Found %d YAML files with kernel.event_subscriber\n\n", count($yamlFiles));

$stats = ['modified' => 0, 'services_updated' => 0];

foreach ($yamlFiles as $file) {
    $relativePath = str_replace($rootDir . '/', '', $file);
    $content = file_get_contents($file);
    $originalContent = $content;

    echo "Processing: $relativePath\n";

    $content = processYamlFile($content, $stats);

    if ($content !== $originalContent) {
        $stats['modified']++;
        if (!$dryRun) {
            file_put_contents($file, $content);
        }
        echo "  MODIFIED\n";
    } else {
        echo "  NO CHANGES\n";
    }
}

echo "\n=== YAML Migration Summary ===\n";
echo sprintf("Files modified: %d\n", $stats['modified']);
echo sprintf("Service definitions updated: %d\n", $stats['services_updated']);

if ($dryRun) {
    echo "\n(DRY RUN - no files were modified)\n";
}

function findYamlFiles(string $rootDir): array
{
    $files = [];
    $dirs = [$rootDir . '/src', $rootDir . '/components'];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $fileInfo) {
            $ext = $fileInfo->getExtension();
            if ($ext !== 'yml' && $ext !== 'yaml') {
                continue;
            }
            $path = $fileInfo->getRealPath();
            if (str_contains($path, '/vendor/') || str_contains($path, '/test/') || str_contains($path, '/Test/')) {
                continue;
            }
            $content = file_get_contents($path);
            if (str_contains($content, 'kernel.event_subscriber')) {
                $files[] = $path;
            }
        }
    }

    sort($files);
    return $files;
}

function processYamlFile(string $content, array &$stats): string
{
    $lines = explode("\n", $content);
    $result = [];
    $i = 0;
    $totalLines = count($lines);

    while ($i < $totalLines) {
        $line = $lines[$i];

        // Detect a tags: line
        if (preg_match('/^(\s+)tags:\s*$/', $line, $tagsMatch)) {
            $tagsIndent = $tagsMatch[1];
            $tagsLineIdx = $i;

            // Collect all tag entries
            $tagEntries = [];
            $j = $i + 1;
            while ($j < $totalLines) {
                $tagLine = $lines[$j];
                // Check if this is a tag entry (starts with "- {" or "- name:")
                if (preg_match('/^' . preg_quote($tagsIndent, '/') . '\s+- \s*\{/', $tagLine) ||
                    preg_match('/^' . preg_quote($tagsIndent, '/') . '\s+- \s*\{/', $tagLine)) {
                    $tagEntries[] = ['index' => $j, 'line' => $tagLine];
                    $j++;
                } elseif (preg_match('/^\s+- \{/', $tagLine) && count($tagEntries) > 0) {
                    // Continuation of tags at same or deeper indent
                    $tagEntries[] = ['index' => $j, 'line' => $tagLine];
                    $j++;
                } else {
                    break;
                }
            }

            // Check if any tag entry is kernel.event_subscriber
            $hasKernelSubscriber = false;
            $kernelSubscriberIndices = [];
            $otherTags = [];

            foreach ($tagEntries as $idx => $entry) {
                if (preg_match("/kernel\.event_subscriber/", $entry['line'])) {
                    $hasKernelSubscriber = true;
                    $kernelSubscriberIndices[] = $idx;
                } else {
                    $otherTags[] = $entry;
                }
            }

            if ($hasKernelSubscriber) {
                $stats['services_updated']++;

                // Find the service name line to add autoconfigure: true
                // Walk backward from tags: to find the service definition
                $serviceIndent = $tagsIndent;
                $autoconfigureInserted = false;

                if (count($otherTags) === 0) {
                    // All tags were kernel.event_subscriber - remove entire tags: block
                    // Replace with autoconfigure: true at the same indent as tags:
                    $result[] = $tagsIndent . 'autoconfigure: true';
                    $autoconfigureInserted = true;
                    // Skip the tags: line and all tag entries
                    $i = $j;
                    continue;
                } else {
                    // Keep tags: and other tags, remove kernel.event_subscriber entries
                    // But also add autoconfigure: true before tags:
                    $result[] = $tagsIndent . 'autoconfigure: true';
                    $result[] = $line; // tags: line
                    foreach ($otherTags as $entry) {
                        $result[] = $entry['line'];
                    }
                    $i = $j;
                    continue;
                }
            }
        }

        // Check for commented out kernel.event_subscriber (leave as is)
        // Just output the line normally
        $result[] = $line;
        $i++;
    }

    return implode("\n", $result);
}
