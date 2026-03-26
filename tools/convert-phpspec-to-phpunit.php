#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * PHPSpec → PHPUnit Conversion Script
 *
 * Converts PHPSpec *Spec.php files to PHPUnit *Test.php files.
 *
 * Usage:
 *   php tools/convert-phpspec-to-phpunit.php <file-or-directory> [--write] [--verbose]
 *
 * Options:
 *   --write    Write output files (default: dry-run, shows diff)
 *   --verbose  Show detailed conversion info
 */

// ─── Entry Point ───────────────────────────────────────────────────────────────

function main(array $argv): int
{
    $args = parseArgs($argv);

    if ($args['help'] || !$args['path']) {
        echo "Usage: php tools/convert-phpspec-to-phpunit.php <file-or-directory> [--write] [--verbose]\n";
        return $args['help'] ? 0 : 1;
    }

    $files = collectSpecFiles($args['path']);
    if (empty($files)) {
        echo "No *Spec.php files found at {$args['path']}\n";
        return 1;
    }

    $stats = ['converted' => 0, 'skipped' => 0, 'errors' => 0, 'todos' => 0];

    foreach ($files as $inputPath) {
        try {
            $result = convertFile($inputPath, $args['verbose']);
            $outputPath = computeOutputPath($inputPath);

            if ($args['write']) {
                $dir = dirname($outputPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                file_put_contents($outputPath, $result['content']);
                echo "  ✓ " . relativePath($outputPath) . "\n";
            } else {
                echo "--- " . relativePath($inputPath) . "\n";
                echo "+++ " . relativePath($outputPath) . "\n";
                echo $result['content'] . "\n";
            }

            $stats['converted']++;
            $stats['todos'] += $result['todoCount'];
        } catch (\Exception $e) {
            echo "  ✗ " . relativePath($inputPath) . ": " . $e->getMessage() . "\n";
            $stats['errors']++;
        }
    }

    echo "\n── Summary ──\n";
    echo "Converted: {$stats['converted']}\n";
    echo "Skipped:   {$stats['skipped']}\n";
    echo "Errors:    {$stats['errors']}\n";
    echo "TODOs:     {$stats['todos']}\n";

    return $stats['errors'] > 0 ? 1 : 0;
}

function parseArgs(array $argv): array
{
    return [
        'path'    => $argv[1] ?? null,
        'write'   => in_array('--write', $argv),
        'verbose' => in_array('--verbose', $argv),
        'help'    => in_array('--help', $argv) || in_array('-h', $argv),
    ];
}

function collectSpecFiles(string $path): array
{
    if (is_file($path)) {
        return [$path];
    }

    $files = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($it as $file) {
        if (preg_match('/Spec\.php$/', $file->getFilename())) {
            $files[] = $file->getPathname();
        }
    }
    sort($files);
    return $files;
}

function relativePath(string $path): string
{
    $cwd = getcwd() . '/';
    if (str_starts_with($path, $cwd)) {
        return substr($path, strlen($cwd));
    }
    return $path;
}

// ─── Path & Namespace Mapping ──────────────────────────────────────────────────

function computeOutputPath(string $inputPath): string
{
    $output = $inputPath;

    // /tests/Specification/ → /tests/Unit/
    $output = preg_replace('#/tests/Specification/#', '/tests/Unit/', $output);

    // /Acceptance/Specification/ → /Acceptance/ (nested acceptance specs)
    $output = preg_replace('#/Acceptance/Specification/#', '/Acceptance/', $output);

    // For legacy paths: tests/back/Context/Specification/ → tests/back/Context/Unit/
    $output = preg_replace('#(tests/back/\w+)/Specification/#', '$1/Unit/', $output);

    // Also handle: tests/back/Acceptance/spec/ → tests/back/Acceptance/Unit/
    $output = preg_replace('#/Acceptance/spec/#', '/Acceptance/Unit/', $output);

    // FooSpec.php → FooTest.php
    $output = preg_replace('/Spec\.php$/', 'Test.php', $output);

    return $output;
}

function computeOutputNamespace(string $inputNs, string $inputPath): string
{
    // Remove Specification\ prefix
    $ns = preg_replace('/^Specification\\\\/', '', $inputNs);

    // Determine the bounded context from the namespace
    // Pattern: Akeneo\{Context}\... → Akeneo\Test\{Context}\Unit\...
    // Pattern: Akeneo\Test\{Context}\Acceptance\... → stays as is (just remove Specification prefix)
    if (preg_match('/^Akeneo\\\\Test\\\\/', $ns)) {
        // Already under Test namespace (acceptance specs) — just strip Specification prefix
        return $ns;
    }

    if (preg_match('/^Akeneo\\\\(\w+)\\\\(.+)$/', $ns, $m)) {
        $context = $m[1];
        $rest = $m[2];
        return "Akeneo\\Test\\{$context}\\Unit\\{$rest}";
    }

    // Fallback: just prepend Test and add Unit
    return "Akeneo\\Test\\Unit\\{$ns}";
}

// ─── File Conversion ───────────────────────────────────────────────────────────

function convertFile(string $inputPath, bool $verbose = false): array
{
    $content = file_get_contents($inputPath);
    $meta = extractMetadata($content, $inputPath);

    if ($verbose) {
        echo "  Parsing: " . relativePath($inputPath) . "\n";
        echo "    Subject: {$meta['subjectFqcn']}\n";
        echo "    Let params: " . count($meta['letParams']) . "\n";
        echo "    Methods: " . count($meta['testMethods']) . "\n";
    }

    $result = generateTestFile($meta);

    return $result;
}

// ─── Metadata Extraction ───────────────────────────────────────────────────────

function extractMetadata(string $content, string $filePath): array
{
    $meta = [
        'filePath'           => $filePath,
        'hasStrictTypes'     => (bool) preg_match('/declare\(strict_types=1\)/', $content),
        'namespace'          => '',
        'useStatements'      => [],
        'specClassName'      => '',
        'subjectClassName'   => '',
        'subjectFqcn'        => '',
        'letParams'          => [],         // name => FQCN
        'letBodyExtra'       => '',         // non-beConstructed code in let()
        'constructionMode'   => 'new',      // 'new', 'static', or 'none'
        'constructionMethod' => '',         // for 'static': method name
        'constructorArgs'    => '',         // raw args string
        'testMethods'        => [],         // [{name, params, body}]
        'privateHelpers'     => [],         // [{name, signature, body}]
        'hasCustomMatchers'  => false,
        'docComment'         => '',
    ];

    // Namespace
    if (preg_match('/^namespace\s+(.+?);/m', $content, $m)) {
        $meta['namespace'] = $m[1];
    }

    // Use statements
    preg_match_all('/^use\s+(.+?);/m', $content, $matches);
    $meta['useStatements'] = $matches[1] ?? [];

    // Class name and doc comment
    if (preg_match('/(\/\*\*.*?\*\/\s*)?class\s+(\w+Spec)\s+extends\s+ObjectBehavior/s', $content, $m)) {
        $meta['docComment'] = trim($m[1] ?? '');
        $meta['specClassName'] = $m[2];
        $meta['subjectClassName'] = preg_replace('/Spec$/', '', $m[2]);
    }

    // Determine subject FQCN from use statements or namespace
    $meta['subjectFqcn'] = resolveSubjectFqcn($meta);

    // Extract let() method
    extractLetMethod($content, $meta);

    // Extract test methods (it_* and its_*)
    extractTestMethods($content, $meta);

    // Extract private helper methods
    extractPrivateHelpers($content, $meta);

    // Check for custom matchers
    $meta['hasCustomMatchers'] = str_contains($content, 'function getMatchers()');

    return $meta;
}

function resolveSubjectFqcn(array $meta): string
{
    $subjectClass = $meta['subjectClassName'];

    // Try to find exact use statement
    foreach ($meta['useStatements'] as $use) {
        $parts = explode('\\', $use);
        $shortName = end($parts);
        if ($shortName === $subjectClass) {
            return $use;
        }
    }

    // Derive from namespace: Specification\Akeneo\Channel\API\Query → Akeneo\Channel\API\Query\ClassName
    $ns = preg_replace('/^Specification\\\\/', '', $meta['namespace']);
    return $ns . '\\' . $subjectClass;
}

function extractLetMethod(string $content, array &$meta): void
{
    // Match let() method with optional parameters
    $pattern = '/public\s+function\s+let\s*\(([^)]*)\)(?:\s*:\s*void)?\s*\{/';

    if (!preg_match($pattern, $content, $m, PREG_OFFSET_CAPTURE)) {
        $meta['constructionMode'] = 'none';
        return;
    }

    $paramsStr = trim($m[1][0]);
    $bodyStart = $m[0][1] + strlen($m[0][0]);

    // Parse parameters
    if (!empty($paramsStr)) {
        $meta['letParams'] = parseMethodParams($paramsStr, $meta['useStatements']);
    }

    // Extract body (find matching closing brace)
    $body = extractBracedBlock($content, $bodyStart - 1);
    // Remove outer braces
    $body = trim(substr($body, 1, -1));

    // Parse beConstructedWith/Through
    if (preg_match('/\$this->beConstructedWith\((.+?)\)\s*;/s', $body, $cm)) {
        $meta['constructionMode'] = 'new';
        $meta['constructorArgs'] = trim($cm[1]);

        // Remove beConstructedWith line from body to find extra code
        $body = preg_replace('/\s*\$this->beConstructedWith\(.+?\)\s*;/s', '', $body);
    } elseif (preg_match('/\$this->beConstructedThrough\(\s*[\'"](\w+)[\'"]\s*,\s*\[(.+?)\]\s*\)\s*;/s', $body, $cm)) {
        $meta['constructionMode'] = 'static';
        $meta['constructionMethod'] = $cm[1];
        $meta['constructorArgs'] = trim($cm[2]);

        $body = preg_replace('/\s*\$this->beConstructedThrough\(.+?\)\s*;/s', '', $body);
    }

    $meta['letBodyExtra'] = trim($body);
}

function parseMethodParams(string $paramsStr, array $useStatements): array
{
    $params = [];
    // Match: TypeHint $name patterns
    foreach (explode(',', $paramsStr) as $param) {
        $param = trim($param);
        if (preg_match('/^(\S+)\s+\$(\w+)$/', $param, $m)) {
            $type = $m[1];
            $name = $m[2];
            // Resolve FQCN from use statements
            $fqcn = resolveType($type, $useStatements);
            $params[$name] = $fqcn;
        }
    }
    return $params;
}

function resolveType(string $shortType, array $useStatements): string
{
    // Check if it's already a FQCN
    if (str_contains($shortType, '\\')) {
        return ltrim($shortType, '\\');
    }

    // Look up in use statements
    foreach ($useStatements as $use) {
        // Handle aliased imports: Foo\Bar as Baz
        if (preg_match('/^(.+)\s+as\s+(\w+)$/', $use, $m)) {
            if ($m[2] === $shortType) {
                return $m[1];
            }
        }

        $parts = explode('\\', $use);
        if (end($parts) === $shortType) {
            return $use;
        }
    }

    return $shortType; // Return as-is (might be a built-in)
}

function extractBracedBlock(string $content, int $openBracePos): string
{
    $depth = 0;
    $len = strlen($content);
    $start = $openBracePos;

    // Find the opening brace
    for ($i = $start; $i < $len; $i++) {
        if ($content[$i] === '{') {
            $depth = 1;
            $start = $i;
            break;
        }
    }

    // Find matching close
    for ($i = $start + 1; $i < $len; $i++) {
        $char = $content[$i];

        // Skip strings
        if ($char === "'" || $char === '"') {
            $i = skipString($content, $i);
            continue;
        }

        if ($char === '{') $depth++;
        if ($char === '}') {
            $depth--;
            if ($depth === 0) {
                return substr($content, $start, $i - $start + 1);
            }
        }
    }

    return substr($content, $start);
}

function skipString(string $content, int $quotePos): int
{
    $quote = $content[$quotePos];
    $len = strlen($content);
    for ($i = $quotePos + 1; $i < $len; $i++) {
        if ($content[$i] === '\\') {
            $i++; // skip escaped char
            continue;
        }
        if ($content[$i] === $quote) {
            return $i;
        }
    }
    return $len - 1;
}

function extractTestMethods(string $content, array &$meta): void
{
    // Match it_* and its_* methods
    $pattern = '/public\s+function\s+(it_\w+|its_\w+)\s*\(([^)]*)\)(?:\s*:\s*void)?\s*\{/';
    preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

    for ($i = 0; $i < count($matches[0]); $i++) {
        $name = $matches[1][$i][0];
        $paramsStr = trim($matches[2][$i][0]);
        $bodyStart = $matches[0][$i][1] + strlen($matches[0][$i][0]);

        $body = extractBracedBlock($content, $bodyStart - 1);
        $body = trim(substr($body, 1, -1)); // Remove outer braces

        $params = [];
        if (!empty($paramsStr)) {
            $params = parseMethodParams($paramsStr, $meta['useStatements']);
        }

        $meta['testMethods'][] = [
            'name'   => $name,
            'params' => $params,
            'body'   => $body,
        ];
    }
}

function extractPrivateHelpers(string $content, array &$meta): void
{
    $pattern = '/\b(private|protected)\s+function\s+(\w+)\s*\(([^)]*)\)(?:\s*:\s*\S+)?\s*\{/';
    preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

    for ($i = 0; $i < count($matches[0]); $i++) {
        $name = $matches[2][$i][0];
        if ($name === 'getMatchers') continue;

        $fullMatch = $matches[0][$i][0];
        $matchStart = $matches[0][$i][1];

        // Extract signature without the trailing '{'
        $signature = rtrim(substr($fullMatch, 0, -1));

        // Extract body block (includes { ... })
        $bracePos = $matchStart + strlen($fullMatch) - 1;
        $body = extractBracedBlock($content, $bracePos);

        $meta['privateHelpers'][] = [
            'name'      => $name,
            'signature' => $signature,
            'body'      => $body,
        ];
    }
}

// ─── Test File Generation ──────────────────────────────────────────────────────

function generateTestFile(array $meta): array
{
    $todoCount = 0;
    $lines = [];

    // File header
    $lines[] = '<?php';
    $lines[] = '';
    $lines[] = 'declare(strict_types=1);';
    $lines[] = '';

    // Namespace
    $outputNs = computeOutputNamespace($meta['namespace'], $meta['filePath']);
    $lines[] = "namespace {$outputNs};";
    $lines[] = '';

    // Use statements
    $useStatements = generateUseStatements($meta);
    foreach ($useStatements as $use) {
        $lines[] = "use {$use};";
    }
    $lines[] = '';

    // Class declaration
    $testClassName = $meta['subjectClassName'] . 'Test';
    if (!empty($meta['docComment'])) {
        $lines[] = $meta['docComment'];
    }
    $lines[] = "class {$testClassName} extends TestCase";
    $lines[] = '{';

    // Properties (mocks + SUT)
    $properties = generateProperties($meta);
    if (!empty($properties)) {
        foreach ($properties as $prop) {
            $lines[] = "    {$prop}";
        }
        $lines[] = '';
    }

    // setUp()
    $setUp = generateSetUp($meta);
    if ($setUp !== null) {
        foreach (explode("\n", $setUp) as $line) {
            $lines[] = $line;
        }
        $lines[] = '';
    }

    // Test methods
    foreach ($meta['testMethods'] as $idx => $method) {
        $result = generateTestMethod($method, $meta);
        $todoCount += $result['todoCount'];
        foreach (explode("\n", $result['content']) as $line) {
            $lines[] = $line;
        }
        if ($idx < count($meta['testMethods']) - 1 || !empty($meta['privateHelpers'])) {
            $lines[] = '';
        }
    }

    // Private helpers
    foreach ($meta['privateHelpers'] as $idx => $helper) {
        // signature = "private function foo(): array"
        // body = "{ return [...]; }"
        $lines[] = "    {$helper['signature']}";
        // Indent the body block
        foreach (explode("\n", $helper['body']) as $bodyLine) {
            $lines[] = "    {$bodyLine}";
        }
        if ($idx < count($meta['privateHelpers']) - 1) {
            $lines[] = '';
        }
    }

    // Custom matchers warning
    if ($meta['hasCustomMatchers']) {
        $lines[] = '';
        $lines[] = '    // TODO: Custom matchers from getMatchers() need manual conversion';
        $todoCount++;
    }

    $lines[] = '}';
    $lines[] = '';

    return [
        'content'   => implode("\n", $lines),
        'todoCount' => $todoCount,
    ];
}

function generateUseStatements(array $meta): array
{
    $uses = [];

    // Keep non-PHPSpec use statements
    $skipPrefixes = [
        'PhpSpec\\',
        'Prophecy\\',
    ];

    foreach ($meta['useStatements'] as $use) {
        $skip = false;
        foreach ($skipPrefixes as $prefix) {
            if (str_starts_with($use, $prefix)) {
                $skip = true;
                break;
            }
        }
        if (!$skip) {
            $uses[] = $use;
        }
    }

    // Add PHPUnit imports
    $needsMockObject = !empty($meta['letParams']);
    // Also check if any test method has mock params
    if (!$needsMockObject) {
        foreach ($meta['testMethods'] as $method) {
            if (!empty($method['params'])) {
                $needsMockObject = true;
                break;
            }
        }
    }

    $uses[] = 'PHPUnit\\Framework\\TestCase';
    if ($needsMockObject) {
        $uses[] = 'PHPUnit\\Framework\\MockObject\\MockObject';
    }

    // Add subject class if not already imported
    $subjectFqcn = $meta['subjectFqcn'];
    $outputNs = computeOutputNamespace($meta['namespace'], $meta['filePath']);
    // Only add if not in same namespace
    $subjectNs = implode('\\', array_slice(explode('\\', $subjectFqcn), 0, -1));
    if ($subjectNs !== $outputNs && !in_array($subjectFqcn, $uses)) {
        $uses[] = $subjectFqcn;
    }

    sort($uses);
    return array_unique($uses);
}

function generateProperties(array $meta): array
{
    $props = [];

    foreach ($meta['letParams'] as $name => $fqcn) {
        $shortType = shortClassName($fqcn);
        $props[] = "private {$shortType}|MockObject \${$name};";
    }

    // Always declare SUT property (even for no-arg constructors)
    $shortSubject = shortClassName($meta['subjectFqcn']);
    $props[] = "private {$shortSubject} \$sut;";

    return $props;
}

function shortClassName(string $fqcn): string
{
    $parts = explode('\\', $fqcn);
    return end($parts);
}

function generateSetUp(array $meta): ?string
{
    $lines = [];
    $lines[] = '    protected function setUp(): void';
    $lines[] = '    {';

    // Extra let() body (non-beConstructed code)
    if (!empty($meta['letBodyExtra'])) {
        $extraBody = $meta['letBodyExtra'];
        foreach (explode("\n", $extraBody) as $line) {
            $trimmed = trim($line);
            if (!empty($trimmed)) {
                $lines[] = "        {$trimmed}";
            }
        }
        $lines[] = '';
    }

    // Create mocks
    foreach ($meta['letParams'] as $name => $fqcn) {
        $shortType = shortClassName($fqcn);
        $lines[] = "        \$this->{$name} = \$this->createMock({$shortType}::class);";
    }

    // Construct SUT
    $shortSubject = shortClassName($meta['subjectFqcn']);
    if ($meta['constructionMode'] === 'new') {
        $args = transformConstructorArgs($meta['constructorArgs'], $meta['letParams']);
        $lines[] = "        \$this->sut = new {$shortSubject}({$args});";
    } elseif ($meta['constructionMode'] === 'static') {
        $args = $meta['constructorArgs'];
        $method = $meta['constructionMethod'];
        $lines[] = "        \$this->sut = {$shortSubject}::{$method}({$args});";
    } else {
        // No-arg constructor
        $lines[] = "        \$this->sut = new {$shortSubject}();";
    }

    $lines[] = '    }';

    return implode("\n", $lines);
}

function transformConstructorArgs(string $argsStr, array $letParams): string
{
    // Replace $varName with $this->varName for let() params
    $result = $argsStr;
    foreach ($letParams as $name => $fqcn) {
        $result = preg_replace('/\$' . preg_quote($name, '/') . '\b/', "\$this->{$name}", $result);
    }
    return $result;
}

// ─── Test Method Generation ────────────────────────────────────────────────────

function generateTestMethod(array $method, array $meta): array
{
    $todoCount = 0;

    // Convert method name: it_does_something → test_it_does_something
    $testName = 'test_' . $method['name'];

    // Identify local mocks (params not in let())
    $localMocks = [];
    foreach ($method['params'] as $name => $fqcn) {
        if (!isset($meta['letParams'][$name])) {
            $localMocks[$name] = $fqcn;
        }
    }

    // All mock variable names (let + local)
    $allMocks = array_merge($meta['letParams'], $localMocks);

    // Transform the method body
    $bodyResult = transformMethodBody($method['body'], $allMocks, $meta, $localMocks);
    $todoCount += $bodyResult['todoCount'];

    $lines = [];
    $lines[] = "    public function {$testName}(): void";
    $lines[] = '    {';

    // Create local mocks at the top
    foreach ($localMocks as $name => $fqcn) {
        $shortType = shortClassName($fqcn);
        $lines[] = "        \${$name} = \$this->createMock({$shortType}::class);";
    }
    if (!empty($localMocks)) {
        $lines[] = '';
    }

    // Add transformed body
    foreach (explode("\n", $bodyResult['content']) as $line) {
        if (!empty(trim($line))) {
            $lines[] = "        {$line}";
        } else {
            $lines[] = '';
        }
    }

    $lines[] = '    }';

    return [
        'content'   => implode("\n", $lines),
        'todoCount' => $todoCount,
    ];
}

function transformMethodBody(string $body, array $allMocks, array $meta, array $localMocks): array
{
    $todoCount = 0;

    // Split body into statements (handling multi-line)
    $statements = splitStatements($body);
    $transformed = [];

    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (empty($stmt)) {
            $transformed[] = '';
            continue;
        }

        $result = transformStatement($stmt, $allMocks, $meta, $localMocks);
        $todoCount += $result['todoCount'];
        $transformed[] = $result['content'];
    }

    return [
        'content'   => implode("\n", $transformed),
        'todoCount' => $todoCount,
    ];
}

function splitStatements(string $body): array
{
    $statements = [];
    $current = '';
    $depth = 0;       // Brace depth
    $parenDepth = 0;  // Parenthesis depth
    $bracketDepth = 0; // Square bracket depth
    $inString = false;
    $stringChar = '';
    $len = strlen($body);

    for ($i = 0; $i < $len; $i++) {
        $char = $body[$i];

        // Handle strings
        if ($inString) {
            $current .= $char;
            if ($char === '\\' && $i + 1 < $len) {
                $current .= $body[++$i];
                continue;
            }
            if ($char === $stringChar) {
                $inString = false;
            }
            continue;
        }

        if ($char === "'" || $char === '"') {
            $inString = true;
            $stringChar = $char;
            $current .= $char;
            continue;
        }

        // Track nesting
        if ($char === '(') $parenDepth++;
        if ($char === ')') $parenDepth--;
        if ($char === '[') $bracketDepth++;
        if ($char === ']') $bracketDepth--;
        if ($char === '{') $depth++;
        if ($char === '}') $depth--;

        $current .= $char;

        // Statement boundary: semicolon at top level, or closing brace of block
        if ($char === ';' && $depth === 0 && $parenDepth === 0 && $bracketDepth === 0) {
            $statements[] = trim($current);
            $current = '';
        } elseif ($char === '}' && $depth === 0 && $parenDepth === 0 && $bracketDepth === 0) {
            // Block statement (if, foreach, etc.)
            $statements[] = trim($current);
            $current = '';
        }
    }

    if (!empty(trim($current))) {
        $statements[] = trim($current);
    }

    return $statements;
}

function transformStatement(string $stmt, array $allMocks, array $meta, array $localMocks): array
{
    $todoCount = 0;
    $letParams = $meta['letParams'];

    // ── .will(function ...) — mark as TODO ──
    if (preg_match('/->will\s*\(\s*function\s*\(/', $stmt)) {
        // Comment out every line of the original statement
        $commented = implode("\n", array_map(fn($l) => "// {$l}", explode("\n", $stmt)));
        $result = "// TODO: manual conversion needed — complex .will() callback\n{$commented}";
        return ['content' => $result, 'todoCount' => 1];
    }

    // ── $this->shouldHaveType(Class::class) ──
    if (preg_match('/^\$this->shouldHaveType\((.+?)\)\s*;$/', $stmt, $m)) {
        $class = $m[1];
        return ['content' => "\$this->assertInstanceOf({$class}, \$this->sut);", 'todoCount' => 0];
    }

    // ── $this->shouldImplement(Interface::class) ──
    if (preg_match('/^\$this->shouldImplement\((.+?)\)\s*;$/', $stmt, $m)) {
        $class = $m[1];
        return ['content' => "\$this->assertInstanceOf({$class}, \$this->sut);", 'todoCount' => 0];
    }

    // ── $this->shouldThrow(Ex)->during('method', [args]) ──
    if (preg_match('/^\$this\s*->\s*shouldThrow\((.+?)\)\s*->\s*during\(\s*[\'"](\w+)[\'"]\s*,\s*\[(.+?)\]\s*\)\s*;$/s', $stmt, $m)) {
        $exception = trim($m[1]);
        $method = $m[2];
        $args = trim($m[3]);

        // Transform args: replace mock var refs
        $args = transformMockVarRefs($args, $letParams, $localMocks);

        // Determine if it's a static call (beConstructedThrough) or instance call
        if ($meta['constructionMode'] === 'static' || $meta['constructionMode'] === 'none') {
            // Check if the method is the factory method
            $shortSubject = shortClassName($meta['subjectFqcn']);
            $lines = "\$this->expectException({$exception});\n{$shortSubject}::{$method}({$args});";
        } else {
            $lines = "\$this->expectException({$exception});\n\$this->sut->{$method}({$args});";
        }
        return ['content' => $lines, 'todoCount' => 0];
    }

    // ── $this->method(args)->shouldReturn(val) / shouldBe / shouldBeLike / etc. ──
    // Also match multi-line: $this\n  ->method(args)\n  ->shouldReturn(val);
    if (preg_match('/^\$this\s*->/s', $stmt)) {
        $result = transformSutChain($stmt, $allMocks, $meta, $localMocks);
        if ($result !== null) {
            return ['content' => $result, 'todoCount' => 0];
        }
    }

    // ── Mock calls: $mockVar->method(args)->willReturn(val) / shouldBeCalled() / etc. ──
    $mockResult = transformMockChain($stmt, $allMocks, $letParams, $localMocks);
    if ($mockResult !== null) {
        return ['content' => $mockResult, 'todoCount' => 0];
    }

    // ── Plain $this->method(args); → $this->sut->method(args); ──
    if (preg_match('/^\$this->(\w+)\(/', $stmt, $m)) {
        $method = $m[1];
        // Skip PHPSpec-specific methods
        $phpspecMethods = ['beConstructedWith', 'beConstructedThrough', 'shouldThrow', 'shouldHaveType', 'shouldImplement', 'getMatchers'];
        if (!in_array($method, $phpspecMethods)) {
            $transformed = preg_replace('/^\$this->/', '$this->sut->', $stmt);
            $transformed = transformMockVarRefs($transformed, $letParams, $localMocks);
            return ['content' => $transformed, 'todoCount' => 0];
        }
    }

    // ── Block statements (foreach, if, etc.) ──
    if (preg_match('/^(foreach|if|for|while|switch)\s*\(/', $stmt)) {
        $transformed = transformBlockStatement($stmt, $allMocks, $meta, $localMocks);
        return $transformed;
    }

    // ── Variable assignments and other statements ──
    $transformed = transformMockVarRefs($stmt, $letParams, $localMocks);
    return ['content' => $transformed, 'todoCount' => 0];
}

function transformSutChain(string $stmt, array $allMocks, array $meta, array $localMocks): ?string
{
    $letParams = $meta['letParams'];

    // Match: $this->method(args)->shouldReturn(val);
    // Match: $this->method(args)->shouldBe(val);
    // Match: $this->method(args)->shouldBeLike(val);
    // Match: $this->method(args)->shouldBeNull();
    // Match: $this->method(args)->shouldBeAnInstanceOf(val);
    // Match: $this->method(args)->shouldReturn(val); — where method chain could be multi-line

    // Normalize whitespace for matching
    $normalized = preg_replace('/\s+/', ' ', $stmt);

    // Pattern: $this->method(args)->assertion(val);
    // We need to handle nested parens in args and val

    // Normalize multi-line: collapse $this\n  ->method into single-line form for matching
    $normalized = preg_replace('/\$this\s*\n\s*->/', '$this->', $stmt);
    $normalized = preg_replace('/\)\s*\n\s*->/', ')->', $normalized);

    // Find $this-> at the start
    if (!preg_match('/^\$this\s*->/', $normalized)) {
        return null;
    }

    // Try to find a shouldXxx assertion in the chain
    $assertionPatterns = [
        'shouldReturn'          => 'assertSame',
        'shouldBe'              => 'assertSame',
        'shouldBeLike'          => 'assertEquals',
        'shouldBeNull'          => 'assertNull',
        'shouldBeAnInstanceOf'  => 'assertInstanceOf',
        'shouldBeArray'         => 'assertIsArray',
        'shouldHaveCount'       => 'assertCount',
    ];

    foreach ($assertionPatterns as $phpspecAssertion => $phpunitAssertion) {
        $assertPattern = '->' . $phpspecAssertion . '(';
        $pos = strpos($normalized, $assertPattern);
        if ($pos === false) {
            // Also try without parens for no-arg assertions like shouldBeNull()
            if ($phpspecAssertion === 'shouldBeNull') {
                $pos = strpos($normalized, '->shouldBeNull()');
            }
            if ($pos === false) continue;
        }

        // Extract the SUT call part (before ->shouldXxx)
        $sutCallPart = substr($normalized, 0, $pos);
        $sutCallPart = trim($sutCallPart);

        // Transform $this-> to $this->sut->
        $sutCall = preg_replace('/^\$this\s*->/', '$this->sut->', $sutCallPart);
        $sutCall = transformMockVarRefs($sutCall, $letParams, $localMocks);

        // Extract assertion args
        $assertArgStart = $pos + strlen($assertPattern);
        $assertArg = extractBalancedContent($normalized, $assertArgStart - 1);
        $assertArg = transformMockVarRefs($assertArg, $letParams, $localMocks);

        if ($phpspecAssertion === 'shouldBeNull') {
            return "\$this->assertNull({$sutCall});";
        } elseif ($phpspecAssertion === 'shouldBeAnInstanceOf') {
            return "\$this->assertInstanceOf({$assertArg}, {$sutCall});";
        } elseif ($phpspecAssertion === 'shouldHaveCount') {
            return "\$this->assertCount({$assertArg}, {$sutCall});";
        } elseif ($assertArg === 'null') {
            // shouldReturn(null) → assertNull
            return "\$this->assertNull({$sutCall});";
        } else {
            return "\$this->{$phpunitAssertion}({$assertArg}, {$sutCall});";
        }
    }

    // No assertion found — this is a plain SUT call
    $transformed = preg_replace('/^\$this\s*\n?\s*->/', '$this->sut->', $stmt);
    // Also handle multiline chains within the SUT call
    $transformed = preg_replace('/\$this\s*\n\s*->/', '$this->sut->', $transformed);
    $transformed = transformMockVarRefs($transformed, $letParams, $localMocks);
    return $transformed;
}

function extractBalancedContent(string $str, int $openParenPos): string
{
    $depth = 0;
    $start = $openParenPos;
    $len = strlen($str);

    for ($i = $start; $i < $len; $i++) {
        if ($str[$i] === '(') {
            if ($depth === 0) {
                $start = $i + 1;
            }
            $depth++;
        } elseif ($str[$i] === ')') {
            $depth--;
            if ($depth === 0) {
                return trim(substr($str, $start, $i - $start));
            }
        }
    }

    return trim(substr($str, $start));
}

function transformMockChain(string $stmt, array $allMocks, array $letParams, array $localMocks): ?string
{
    // Check if statement starts with a mock variable
    if (!preg_match('/^\$(\w+)\s*\n?\s*->/', $stmt, $varMatch)) {
        return null;
    }

    $varName = $varMatch[1];
    if (!isset($allMocks[$varName])) {
        return null; // Not a mock variable
    }

    // Parse the mock chain
    $chain = parseMockChain($stmt, $varName);
    if ($chain === null) {
        return null;
    }

    // Determine the variable reference ($this->var for let params, $var for local)
    $varRef = isset($letParams[$varName]) ? "\$this->{$varName}" : "\${$varName}";

    // Build PHPUnit mock call
    return buildPhpUnitMockCall($varRef, $chain, $letParams, $localMocks);
}

function parseMockChain(string $stmt, string $varName): ?array
{
    $chain = [
        'methodName'    => null,
        'methodArgs'    => null,
        'willReturn'    => null,
        'willThrow'     => null,
        'shouldBeCalled'     => false,
        'shouldNotBeCalled'  => false,
        'shouldBeCalledOnce' => false,
        'shouldBeCalledTimes' => null,
        'willCallback'  => null,
    ];

    // Normalize the statement (collapse whitespace for easier matching)
    $normalized = preg_replace('/\s+/', ' ', $stmt);
    $normalized = trim($normalized, "; \t\n\r");

    // Match: $var -> methodName(args)
    $prefix = '$' . $varName . ' -> ';
    if (!str_starts_with($normalized, $prefix)) {
        // Try without space around ->
        $normalized = preg_replace('/\s*->\s*/', ' -> ', $normalized);
    }

    // Extract method name: first -> after $var
    $rest = $normalized;
    $pattern = '/^\$' . preg_quote($varName) . '\s*->\s*(\w+)\s*\(/';
    if (!preg_match($pattern, $rest, $m)) {
        return null;
    }

    $methodName = $m[1];

    // Check if this method IS a prophecy assertion (not a real method)
    $prophecyMethods = ['willReturn', 'willThrow', 'shouldBeCalled', 'shouldNotBeCalled',
        'shouldBeCalledOnce', 'shouldBeCalledTimes', 'will', 'shouldHave', 'shouldNotHave'];
    if (in_array($methodName, $prophecyMethods)) {
        return null; // This is a prophecy chain without a method call first
    }

    $chain['methodName'] = $methodName;

    // Extract method args using balanced parens from the original statement
    $origPattern = '/\$' . preg_quote($varName) . '\s*\n?\s*->\s*' . preg_quote($methodName) . '\s*\(/s';
    if (preg_match($origPattern, $stmt, $m, PREG_OFFSET_CAPTURE)) {
        $argsStart = $m[0][1] + strlen($m[0][0]) - 1;
        $chain['methodArgs'] = extractBalancedContent($stmt, $argsStart);
    }

    // Parse the rest of the chain for prophecy calls
    // Look for: ->willReturn(val), ->shouldBeCalled(), etc.
    if (preg_match('/->willReturn\s*\(/', $stmt, $m, PREG_OFFSET_CAPTURE)) {
        $pos = $m[0][1] + strlen($m[0][0]) - 1;
        $chain['willReturn'] = extractBalancedContent($stmt, $pos);
    }

    if (preg_match('/->willThrow\s*\(/', $stmt, $m, PREG_OFFSET_CAPTURE)) {
        $pos = $m[0][1] + strlen($m[0][0]) - 1;
        $chain['willThrow'] = extractBalancedContent($stmt, $pos);
    }

    if (str_contains($stmt, '->shouldBeCalled()')) {
        $chain['shouldBeCalled'] = true;
    }
    if (str_contains($stmt, '->shouldNotBeCalled()')) {
        $chain['shouldNotBeCalled'] = true;
    }
    if (str_contains($stmt, '->shouldBeCalledOnce()')) {
        $chain['shouldBeCalledOnce'] = true;
    }
    if (preg_match('/->shouldBeCalledTimes\((\d+)\)/', $stmt, $m)) {
        $chain['shouldBeCalledTimes'] = (int) $m[1];
    }

    return $chain;
}

function buildPhpUnitMockCall(string $varRef, array $chain, array $letParams, array $localMocks): string
{
    $method = $chain['methodName'];
    $args = $chain['methodArgs'];

    // Transform args: replace mock var refs and Argument:: matchers
    if ($args !== null && $args !== '') {
        $args = transformMockVarRefs($args, $letParams, $localMocks);
        $args = transformArgumentMatchers($args);
    }

    // Determine expectation type
    $hasExpectation = $chain['shouldBeCalled'] || $chain['shouldNotBeCalled']
        || $chain['shouldBeCalledOnce'] || $chain['shouldBeCalledTimes'] !== null;

    if ($chain['shouldNotBeCalled']) {
        $result = "{$varRef}->expects(\$this->never())->method('{$method}')";
    } elseif ($chain['shouldBeCalledOnce'] || $chain['shouldBeCalled']) {
        $result = "{$varRef}->expects(\$this->once())->method('{$method}')";
    } elseif ($chain['shouldBeCalledTimes'] !== null) {
        $n = $chain['shouldBeCalledTimes'];
        $result = "{$varRef}->expects(\$this->exactly({$n}))->method('{$method}')";
    } else {
        // No expectation — just a stub
        $result = "{$varRef}->method('{$method}')";
    }

    // Add argument matcher if there are args
    if ($args !== null && $args !== '') {
        $result .= "->with({$args})";
    }

    // Add return value
    if ($chain['willReturn'] !== null) {
        $returnVal = transformMockVarRefs($chain['willReturn'], $letParams, $localMocks);
        $result .= "->willReturn({$returnVal})";
    }

    // Add throw
    if ($chain['willThrow'] !== null) {
        $throwVal = transformMockVarRefs($chain['willThrow'], $letParams, $localMocks);
        $result .= "->willThrowException({$throwVal})";
    }

    return $result . ';';
}

function transformMockVarRefs(string $str, array $letParams, array $localMocks): string
{
    // Replace $varName with $this->varName for let() params
    foreach ($letParams as $name => $fqcn) {
        $str = preg_replace('/\$' . preg_quote($name, '/') . '\b/', "\$this->{$name}", $str);
    }
    // Local mocks keep their $varName as-is
    return $str;
}

function transformArgumentMatchers(string $str): string
{
    // Argument::any() → $this->anything()
    $str = str_replace('Argument::any()', '$this->anything()', $str);

    // Argument::type('string') → $this->isType('string')
    $str = preg_replace('/Argument::type\(([\'"][^"\']+[\'"])\)/', '$this->isType($1)', $str);

    // Argument::type(Foo::class) → $this->isInstanceOf(Foo::class)
    $str = preg_replace('/Argument::type\((\w+(?:::\w+)?)\)/', '$this->isInstanceOf($1)', $str);

    // Argument::cetera() → mark as TODO
    if (str_contains($str, 'Argument::')) {
        $str = "/* TODO: convert Argument matcher */ {$str}";
    }

    return $str;
}

function transformBlockStatement(string $stmt, array $allMocks, array $meta, array $localMocks): array
{
    $todoCount = 0;
    $letParams = $meta['letParams'];

    // For block statements (foreach, if), transform the body recursively
    // Simple approach: transform $this-> and mock refs within the block

    $transformed = $stmt;

    // Replace $this->method(args)->shouldReturn(val) inside blocks
    $transformed = preg_replace_callback(
        '/\$this->(\w+)\(([^)]*)\)->shouldReturn\(([^)]*)\)/',
        function ($m) {
            $method = $m[1];
            $args = $m[2];
            $val = $m[3];
            return "\$this->assertSame({$val}, \$this->sut->{$method}({$args}))";
        },
        $transformed
    );

    // Replace plain $this->method() calls
    $transformed = preg_replace_callback(
        '/\$this->(\w+)\(/',
        function ($m) {
            $method = $m[1];
            $phpspecMethods = ['shouldThrow', 'shouldHaveType', 'shouldImplement', 'beConstructedWith',
                'beConstructedThrough', 'assertSame', 'assertEquals', 'assertNull', 'assertInstanceOf',
                'assertCount', 'assertIsArray', 'assertTrue', 'assertFalse'];
            if (in_array($method, $phpspecMethods)) {
                return $m[0]; // Don't transform
            }
            return "\$this->sut->{$method}(";
        },
        $transformed
    );

    $transformed = transformMockVarRefs($transformed, $letParams, $localMocks);

    return ['content' => $transformed, 'todoCount' => $todoCount];
}

// ─── Main ──────────────────────────────────────────────────────────────────────

exit(main($argv));
