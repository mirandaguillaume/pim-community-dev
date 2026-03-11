<?php

declare(strict_types=1);

/**
 * Polyfill for Psr\Log\Test\TestLogger, removed in psr/log v3.
 *
 * This class provides the same API as the v1 TestLogger: an in-memory logger
 * that collects records for test assertions (hasInfo, hasWarning, etc.).
 *
 * @see https://github.com/php-fig/log/blob/1.1.4/Psr/Log/Test/TestLogger.php
 */

namespace Psr\Log\Test;

use Psr\Log\AbstractLogger;

class TestLogger extends AbstractLogger
{
    /** @var array<int, array{level: string, message: string|\Stringable, context: array<mixed>}> */
    public array $records = [];

    /** @var array<string, array<int, array{level: string, message: string|\Stringable, context: array<mixed>}>> */
    public array $recordsByLevel = [];

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $record = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
        $this->recordsByLevel[$level][] = $record;
        $this->records[] = $record;
    }

    /**
     * @param array<string, mixed>|string $record
     */
    public function hasRecord(array|string $record, string $level): bool
    {
        if (is_string($record)) {
            $record = ['message' => $record];
        }

        return $this->hasRecordThatPasses(function (array $rec) use ($record): bool {
            if ($rec['message'] !== $record['message']) {
                return false;
            }
            if (isset($record['context']) && $rec['context'] !== $record['context']) {
                return false;
            }

            return true;
        }, $level);
    }

    public function hasRecords(string $level): bool
    {
        return isset($this->recordsByLevel[$level]);
    }

    public function hasRecordThatContains(string $message, string $level): bool
    {
        return $this->hasRecordThatPasses(
            fn (array $rec): bool => str_contains((string) $rec['message'], $message),
            $level,
        );
    }

    public function hasRecordThatMatches(string $regex, string $level): bool
    {
        return $this->hasRecordThatPasses(
            fn (array $rec): bool => preg_match($regex, (string) $rec['message']) > 0,
            $level,
        );
    }

    public function hasRecordThatPasses(callable $predicate, string $level): bool
    {
        if (!isset($this->recordsByLevel[$level])) {
            return false;
        }
        foreach ($this->recordsByLevel[$level] as $i => $rec) {
            if ($predicate($rec, $i)) {
                return true;
            }
        }

        return false;
    }

    public function reset(): void
    {
        $this->records = [];
        $this->recordsByLevel = [];
    }

    /**
     * Magic caller that dispatches hasInfo(), hasWarning(), hasInfoRecords(), etc.
     *
     * @param array<mixed> $args
     */
    public function __call(string $method, array $args): mixed
    {
        $levels = 'Debug|Info|Notice|Warning|Error|Critical|Alert|Emergency';
        if (preg_match('/(.*)(' . $levels . ')(.*)/i', $method, $matches) > 0) {
            $genericMethod = $matches[1] . ($matches[3] !== 'Records' ? 'Record' : '') . $matches[3];
            $level = strtolower($matches[2]);
            if (method_exists($this, $genericMethod)) {
                $args[] = $level;

                return $this->$genericMethod(...$args);
            }
        }

        throw new \BadMethodCallException('Call to undefined method ' . static::class . '::' . $method . '()');
    }
}
