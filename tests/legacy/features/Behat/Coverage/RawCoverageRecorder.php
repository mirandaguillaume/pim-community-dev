<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

/**
 * The pure, I/O-free and PCOV-free half of the Behat coverage collector.
 *
 * Everything here runs inside an HTTP request that a Behat scenario is waiting on, so it is
 * deliberately dumb: array reduction plus one gzip. No php-code-coverage object is built, no source
 * file is parsed, no filter is applied. All of that happens once, offline, in CoverageMerger --
 * doing it per request is what cost ~5.1x and got the nightly flag disabled on 2026-07-28.
 *
 * The reduced shape -- array<string $file, array<int $line, int 1>> -- is chosen to be exactly what
 * RawCodeCoverageData::fromXdebugWithoutPathCoverage() consumes, so the merge needs no conversion.
 */
final class RawCoverageRecorder
{
    /**
     * Records are length-prefixed so many of them can be appended to a single per-worker file and
     * read back unambiguously: 4-byte big-endian length, then that many bytes of payload.
     */
    private const LENGTH_BYTES = 4;

    /**
     * Drop everything PCOV reports that is not an actual execution, and normalise hit counts to 1.
     *
     * PCOV reports Driver::LINE_NOT_EXECUTED (-1) for an executable line that was not run and
     * LINE_NOT_EXECUTABLE (-2) otherwise. Neither is needed: the merge derives executable lines by
     * static analysis over the whole of src/, which yields a correct denominator including files no
     * request ever touched. Keeping only positives is what makes the per-request record small.
     *
     * Normalising to exactly 1 is required, not cosmetic:
     * ProcessedCodeCoverageData::markCodeAsExecutedByTestCase compares with
     * `$v === Driver::LINE_EXECUTED`, a strict identity against int 1, so any other positive count
     * would be silently discarded from the report.
     *
     * @param array<string, array<int, int>> $rawPcov
     *
     * @return array<string, array<int, int>>
     */
    public static function reduce(array $rawPcov): array
    {
        $hits = [];

        foreach ($rawPcov as $file => $lines) {
            $executed = [];

            foreach ($lines as $line => $count) {
                if ($count > 0) {
                    $executed[$line] = 1;
                }
            }

            if ($executed !== []) {
                $hits[$file] = $executed;
            }
        }

        return $hits;
    }

    /**
     * @param array<string, array<int, int>> $hits
     */
    public static function encode(array $hits): string
    {
        $payload = \gzencode(\serialize($hits), 1);

        return \pack('N', \strlen($payload)) . $payload;
    }

    /**
     * Decode every complete record in a blob, ignoring a truncated tail.
     *
     * A truncated tail is expected, not exceptional: php-fpm can kill a worker mid-write, and the
     * merge must still keep every complete record written before it.
     *
     * @return list<array<string, array<int, int>>>
     */
    public static function decodeAll(string $blob): array
    {
        $records = [];
        $offset = 0;
        $total = \strlen($blob);

        while ($offset + self::LENGTH_BYTES <= $total) {
            /** @var array{1: int}|false $header */
            $header = \unpack('N', \substr($blob, $offset, self::LENGTH_BYTES));

            if ($header === false) {
                break;
            }

            $length = $header[1];
            $offset += self::LENGTH_BYTES;

            if ($length <= 0 || $offset + $length > $total) {
                break; // truncated tail
            }

            $payload = @\gzdecode(\substr($blob, $offset, $length));
            $offset += $length;

            if ($payload === false) {
                continue;
            }

            $record = @\unserialize($payload, ['allowed_classes' => false]);

            if (\is_array($record)) {
                /** @var array<string, array<int, int>> $record */
                $records[] = $record;
            }
        }

        return $records;
    }

    /**
     * @param array<string, array<int, int>> $accumulator
     * @param array<string, array<int, int>> $record
     *
     * @return array<string, array<int, int>>
     */
    public static function union(array $accumulator, array $record): array
    {
        foreach ($record as $file => $lines) {
            // `+` on int-keyed arrays keeps the left operand for duplicate keys. Every value is 1,
            // so which side wins is irrelevant and this is markedly faster than array_merge.
            $accumulator[$file] = isset($accumulator[$file]) ? $accumulator[$file] + $lines : $lines;
        }

        return $accumulator;
    }
}
