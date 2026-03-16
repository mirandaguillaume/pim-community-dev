#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Migration script: EventSubscriberInterface → #[AsEventListener]
 *
 * Converts all kernel EventSubscribers to use Symfony 6.4+ #[AsEventListener] attributes.
 *
 * Usage: php bin/migrate-event-subscribers.php [--dry-run]
 */

$dryRun = in_array('--dry-run', $argv, true);
$rootDir = dirname(__DIR__);

$stats = ['migrated' => 0, 'skipped' => 0, 'warnings' => []];

// Find all PHP files implementing EventSubscriberInterface
$files = findSubscriberFiles($rootDir);

echo sprintf("Found %d files implementing EventSubscriberInterface\n", count($files));

foreach ($files as $file) {
    $relativePath = str_replace($rootDir . '/', '', $file);
    $content = file_get_contents($file);

    // Skip test/spec/vendor files
    if (preg_match('#/(test|tests|Test|Tests|spec|Spec|vendor)/#', $file)) {
        echo "  SKIP (test/spec/vendor): $relativePath\n";
        $stats['skipped']++;
        continue;
    }

    // Skip form event subscribers (they use FormEvents, not kernel events)
    if (isFormEventSubscriber($content)) {
        echo "  SKIP (form subscriber): $relativePath\n";
        $stats['skipped']++;
        continue;
    }

    // Skip programmatically-registered UpdateJobExecutionStorageSummarySubscriber
    if (str_contains($content, 'UpdateJobExecutionStorageSummarySubscriber') && str_contains($content, 'class UpdateJobExecutionStorageSummarySubscriber')) {
        echo "  SKIP (programmatic): $relativePath\n";
        $stats['skipped']++;
        continue;
    }

    // Skip DispatchBufferedPimEventSubscriberInterface (it's an interface, not a class)
    if (str_contains($content, 'interface DispatchBufferedPimEventSubscriberInterface')) {
        echo "  SKIP (interface): $relativePath\n";
        $stats['skipped']++;
        continue;
    }

    echo "Processing: $relativePath\n";

    $result = migrateFile($content, $relativePath);

    if ($result === null) {
        echo "  WARNING: Could not parse getSubscribedEvents() - SKIPPING\n";
        $stats['warnings'][] = $relativePath;
        $stats['skipped']++;
        continue;
    }

    if (!$dryRun) {
        file_put_contents($file, $result);
    }

    $stats['migrated']++;
}

echo "\n=== Migration Summary ===\n";
echo sprintf("Migrated: %d files\n", $stats['migrated']);
echo sprintf("Skipped: %d files\n", $stats['skipped']);
if (!empty($stats['warnings'])) {
    echo "\nWarnings (files that need manual review):\n";
    foreach ($stats['warnings'] as $warning) {
        echo "  - $warning\n";
    }
}

if ($dryRun) {
    echo "\n(DRY RUN - no files were modified)\n";
}

// --- Functions ---

function findSubscriberFiles(string $rootDir): array
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
            if ($fileInfo->getExtension() !== 'php') {
                continue;
            }
            $path = $fileInfo->getRealPath();
            $content = file_get_contents($path);
            // Match "implements EventSubscriberInterface" or "implements DispatchBufferedPimEventSubscriberInterface"
            if (preg_match('/implements\s+.*EventSubscriberInterface/', $content)) {
                $files[] = $path;
            }
        }
    }

    sort($files);
    return $files;
}

function isFormEventSubscriber(string $content): bool
{
    // Check if getSubscribedEvents() references FormEvents
    if (preg_match('/getSubscribedEvents.*?return\s+\[(.+?)\];/s', $content, $match)) {
        $body = $match[1];
        if (preg_match('/FormEvents::/', $body)) {
            return true;
        }
    }
    return false;
}

function migrateFile(string $content, string $relativePath): ?string
{
    // 1. Parse getSubscribedEvents() to extract event mappings
    $eventMappings = parseGetSubscribedEvents($content);
    if ($eventMappings === null) {
        return null;
    }

    // 2. Build #[AsEventListener] attribute lines
    $attributes = buildAttributes($eventMappings);
    if (empty($attributes)) {
        echo "  WARNING: No event mappings found\n";
        return null;
    }

    // 3. Add AsEventListener use statement
    $content = addUseStatement($content);

    // 4. Remove EventSubscriberInterface use statement
    $content = removeEventSubscriberUseStatement($content);

    // 5. Add #[AsEventListener] attributes before the class declaration
    $content = addAttributesToClass($content, $attributes);

    // 6. Remove "implements EventSubscriberInterface" or "DispatchBufferedPimEventSubscriberInterface"
    $content = removeImplementsClause($content);

    // 7. Remove getSubscribedEvents() method (including PHPDoc)
    $content = removeGetSubscribedEventsMethod($content);

    return $content;
}

function parseGetSubscribedEvents(string $content): ?array
{
    // Extract the body of getSubscribedEvents()
    // Find the method and extract its return statement
    $pattern = '/public\s+static\s+function\s+getSubscribedEvents\s*\(\s*\)\s*:\s*array\s*\{(.*?)\n\s{4}\}/s';
    if (!preg_match($pattern, $content, $match)) {
        // Try without return type
        $pattern = '/public\s+static\s+function\s+getSubscribedEvents\s*\(\s*\)\s*\{(.*?)\n\s{4}\}/s';
        if (!preg_match($pattern, $content, $match)) {
            return null;
        }
    }

    $methodBody = $match[1];

    // Extract the return array content
    if (!preg_match('/return\s+\[(.+)\];/s', $methodBody, $returnMatch)) {
        return null;
    }

    $arrayContent = $returnMatch[1];

    return parseEventArray($arrayContent);
}

function parseEventArray(string $arrayContent): ?array
{
    $mappings = [];

    // Split by top-level commas (not inside nested brackets)
    $entries = splitArrayEntries($arrayContent);

    foreach ($entries as $entry) {
        $entry = trim($entry);
        if (empty($entry)) {
            continue;
        }

        // Parse: EventClass::class => 'methodName'
        // Parse: EventClass::class => ['methodName', priority]
        // Parse: EventClass::class => [['method1', priority], ['method2', priority]]
        // Parse: EventClass::class => [['method1']]
        if (!preg_match('/^(.+?)\s*=>\s*(.+)$/s', $entry, $parts)) {
            continue;
        }

        $eventName = trim($parts[1]);
        $value = trim($parts[2]);

        $listeners = parseListenerValue($eventName, $value);
        if ($listeners === null) {
            return null;
        }

        $mappings = array_merge($mappings, $listeners);
    }

    return $mappings;
}

function splitArrayEntries(string $content): array
{
    $entries = [];
    $current = '';
    $depth = 0;

    for ($i = 0; $i < strlen($content); $i++) {
        $char = $content[$i];
        if ($char === '[') {
            $depth++;
        } elseif ($char === ']') {
            $depth--;
        } elseif ($char === ',' && $depth === 0) {
            $entries[] = $current;
            $current = '';
            continue;
        }
        $current .= $char;
    }

    if (trim($current) !== '') {
        $entries[] = $current;
    }

    return $entries;
}

function parseListenerValue(string $eventName, string $value): ?array
{
    $mappings = [];

    // Pattern A: Simple string - 'methodName'
    if (preg_match("/^'([^']+)'$/", $value, $m)) {
        $mappings[] = ['event' => $eventName, 'method' => $m[1], 'priority' => null];
        return $mappings;
    }

    // Check if it's an array
    if (!preg_match('/^\[(.+)\]$/s', $value, $arrayMatch)) {
        return null;
    }

    $inner = trim($arrayMatch[1]);

    // Pattern C: Multiple listeners - [['method1', priority], ['method2', priority]]
    // or [['method1']]
    // Detect: starts with '['
    if (preg_match('/^\s*\[/', $inner)) {
        // It's nested arrays - Pattern C
        $subEntries = splitArrayEntries($inner);
        foreach ($subEntries as $sub) {
            $sub = trim($sub);
            if (empty($sub)) {
                continue;
            }
            // Each sub is like ['methodName'] or ['methodName', priority]
            if (preg_match('/^\[(.+)\]$/s', $sub, $subMatch)) {
                $subInner = trim($subMatch[1]);
                $parsed = parseSimpleListenerArray($subInner);
                if ($parsed === null) {
                    return null;
                }
                $mappings[] = ['event' => $eventName, 'method' => $parsed['method'], 'priority' => $parsed['priority']];
            } else {
                return null;
            }
        }
        return $mappings;
    }

    // Pattern B: Single listener with priority - ['methodName', priority]
    $parsed = parseSimpleListenerArray($inner);
    if ($parsed === null) {
        return null;
    }
    $mappings[] = ['event' => $eventName, 'method' => $parsed['method'], 'priority' => $parsed['priority']];
    return $mappings;
}

function parseSimpleListenerArray(string $inner): ?array
{
    // 'methodName' or 'methodName', priority
    $parts = array_map('trim', explode(',', $inner, 2));

    if (!preg_match("/^'([^']+)'$/", $parts[0], $m)) {
        return null;
    }

    $method = $m[1];
    $priority = null;

    if (isset($parts[1])) {
        $priorityStr = trim($parts[1]);
        if ($priorityStr !== '' && $priorityStr !== '0') {
            $priority = (int) $priorityStr;
        }
    }

    return ['method' => $method, 'priority' => $priority];
}

function buildAttributes(array $mappings): array
{
    $lines = [];

    foreach ($mappings as $mapping) {
        $params = [];
        $params[] = sprintf('event: %s', $mapping['event']);
        $params[] = sprintf("method: '%s'", $mapping['method']);
        if ($mapping['priority'] !== null) {
            $params[] = sprintf('priority: %d', $mapping['priority']);
        }

        $lines[] = sprintf('#[AsEventListener(%s)]', implode(', ', $params));
    }

    return $lines;
}

function addUseStatement(string $content): string
{
    $useStatement = 'use Symfony\\Component\\EventDispatcher\\Attribute\\AsEventListener;';

    // Check if already present
    if (str_contains($content, $useStatement)) {
        return $content;
    }

    // Add after the last existing use statement in the file (before class declaration)
    // Find the position of the last 'use' statement
    if (preg_match_all('/^use\s+[^;]+;$/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
        $lastUse = end($matches[0]);
        $insertPos = $lastUse[1] + strlen($lastUse[0]);
        $content = substr($content, 0, $insertPos) . "\n" . $useStatement . substr($content, $insertPos);
    }

    return $content;
}

function removeEventSubscriberUseStatement(string $content): string
{
    // Remove "use Symfony\Component\EventDispatcher\EventSubscriberInterface;"
    $content = preg_replace(
        '/^use\s+Symfony\\\\Component\\\\EventDispatcher\\\\EventSubscriberInterface;\n/m',
        '',
        $content
    );

    return $content;
}

function addAttributesToClass(string $content, array $attributes): string
{
    // Find the class declaration line and add attributes before it
    // Handle: class, final class, final readonly class, abstract class, etc.
    $pattern = '/^((?:\/\*\*.*?\*\/\s*)?)((?:final\s+)?(?:readonly\s+)?(?:abstract\s+)?class\s+\w+)/ms';

    if (preg_match($pattern, $content, $match, PREG_OFFSET_CAPTURE)) {
        $classDeclarationStart = $match[2][1];
        $attributeBlock = implode("\n", $attributes) . "\n";
        $content = substr($content, 0, $classDeclarationStart) . $attributeBlock . substr($content, $classDeclarationStart);
    }

    return $content;
}

function removeImplementsClause(string $content): string
{
    // Handle DispatchBufferedPimEventSubscriberInterface (extends EventSubscriberInterface)
    // For classes implementing DispatchBufferedPimEventSubscriberInterface, DON'T remove the implements
    // because they still implement that interface for the other methods
    if (str_contains($content, 'implements DispatchBufferedPimEventSubscriberInterface')) {
        // Don't remove, these still need the interface for the other contract methods
        return $content;
    }

    // Case 1: Only implements EventSubscriberInterface
    // "implements EventSubscriberInterface" -> remove entirely
    $content = preg_replace(
        '/\s+implements\s+EventSubscriberInterface(?=\s*[\n{])/',
        '',
        $content
    );

    // Case 2: EventSubscriberInterface is first in a list: "implements EventSubscriberInterface, Foo"
    $content = preg_replace(
        '/implements\s+EventSubscriberInterface\s*,\s*/',
        'implements ',
        $content
    );

    // Case 3: EventSubscriberInterface is in the middle or end: "implements Foo, EventSubscriberInterface, Bar"
    // or "implements Foo, EventSubscriberInterface"
    $content = preg_replace(
        '/,\s*EventSubscriberInterface/',
        '',
        $content
    );

    return $content;
}

function removeGetSubscribedEventsMethod(string $content): string
{
    // Remove the PHPDoc + method definition
    // First try with PHPDoc
    $pattern = '/\n\s*(?:\/\*\*[^*]*\*+(?:[^\/*][^*]*\*+)*\/\s*\n)?(\s*)public\s+static\s+function\s+getSubscribedEvents\s*\(\s*\)\s*:\s*array\s*\{[^}]*(?:\{[^}]*\}[^}]*)*\}\n/s';

    $newContent = preg_replace($pattern, "\n", $content, 1);
    if ($newContent !== $content) {
        return $newContent;
    }

    // Try without return type
    $pattern = '/\n\s*(?:\/\*\*[^*]*\*+(?:[^\/*][^*]*\*+)*\/\s*\n)?(\s*)public\s+static\s+function\s+getSubscribedEvents\s*\(\s*\)\s*\{[^}]*(?:\{[^}]*\}[^}]*)*\}\n/s';

    $newContent = preg_replace($pattern, "\n", $content, 1);
    if ($newContent !== $content) {
        return $newContent;
    }

    // Fallback: match more aggressively using balanced braces
    $newContent = removeMethodByBraceMatching($content, 'getSubscribedEvents');
    return $newContent;
}

function removeMethodByBraceMatching(string $content, string $methodName): string
{
    // Find the method signature
    $signaturePattern = '/(\n\s*(?:\/\*\*.*?\*\/\s*\n)?\s*public\s+static\s+function\s+' . preg_quote($methodName) . '\s*\([^)]*\)[^{]*)\{/s';

    if (!preg_match($signaturePattern, $content, $match, PREG_OFFSET_CAPTURE)) {
        return $content;
    }

    // Find where PHPDoc starts (if any)
    $fullMatch = $match[0][0];
    $startOffset = $match[0][1];

    // Find the opening brace position
    $bracePos = $startOffset + strlen($fullMatch) - 1;

    // Match balanced braces
    $depth = 1;
    $pos = $bracePos + 1;
    $len = strlen($content);

    while ($pos < $len && $depth > 0) {
        if ($content[$pos] === '{') {
            $depth++;
        } elseif ($content[$pos] === '}') {
            $depth--;
        }
        $pos++;
    }

    // Remove from startOffset to pos (inclusive of closing brace)
    // Also eat any trailing newline
    if ($pos < $len && $content[$pos] === "\n") {
        $pos++;
    }

    return substr($content, 0, $startOffset) . "\n" . substr($content, $pos);
}
