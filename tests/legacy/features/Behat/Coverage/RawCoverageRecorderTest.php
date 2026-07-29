<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;

final class RawCoverageRecorderTest extends TestCase
{
    public function test_reduce_keeps_only_executed_lines(): void
    {
        // php-code-coverage Driver markers: 1 = LINE_EXECUTED, -1 = LINE_NOT_EXECUTED,
        // -2 = LINE_NOT_EXECUTABLE.
        $raw = [
            '/srv/pim/src/A.php' => [3 => 1, 4 => -1, 5 => 1, 6 => -2],
            '/srv/pim/src/B.php' => [10 => -1],
        ];

        // Line 4 was executable but never run and 6 is not executable; both are dropped to keep the
        // per-request record small. That is only sound because CoverageMerger::backfillExecutableLines()
        // restores the executable-line skeleton of every touched file at merge time -- see
        // CoverageMergerTest::test_a_partially_covered_file_keeps_its_unhit_executable_lines. B.php has
        // no executed line at all, so the file disappears entirely and the Filter alone rescues it.
        self::assertSame(
            ['/srv/pim/src/A.php' => [3 => 1, 5 => 1]],
            RawCoverageRecorder::reduce($raw),
        );
    }

    public function test_reduce_normalises_a_hit_count_above_one_to_exactly_one(): void
    {
        // Not cosmetic. ProcessedCodeCoverageData::markCodeAsExecutedByTestCase compares with
        // `$v === Driver::LINE_EXECUTED` — strict identity against int 1 — so a line reported as
        // executed 7 times would be silently dropped from the report if passed through as-is.
        self::assertSame(
            ['/srv/pim/src/A.php' => [3 => 1]],
            RawCoverageRecorder::reduce(['/srv/pim/src/A.php' => [3 => 7]]),
        );
    }

    public function test_reduce_returns_an_empty_array_when_nothing_was_executed(): void
    {
        self::assertSame([], RawCoverageRecorder::reduce(['/srv/pim/src/A.php' => [3 => -1]]));
    }

    public function test_encoded_records_round_trip_when_appended_to_one_blob(): void
    {
        $first = ['/srv/pim/src/A.php' => [3 => 1]];
        $second = ['/srv/pim/src/B.php' => [9 => 1, 11 => 1]];

        $blob = RawCoverageRecorder::encode($first) . RawCoverageRecorder::encode($second);

        self::assertSame([$first, $second], RawCoverageRecorder::decodeAll($blob));
    }

    public function test_decode_all_ignores_a_truncated_trailing_record(): void
    {
        // An fpm worker killed mid-write leaves a partial record; the merge must not lose
        // the complete records that precede it.
        $good = RawCoverageRecorder::encode(['/srv/pim/src/A.php' => [3 => 1]]);
        $truncated = substr(RawCoverageRecorder::encode(['/srv/pim/src/B.php' => [9 => 1]]), 0, 6);

        self::assertSame(
            [['/srv/pim/src/A.php' => [3 => 1]]],
            RawCoverageRecorder::decodeAll($good . $truncated),
        );
    }

    public function test_decode_all_returns_nothing_for_an_empty_blob(): void
    {
        self::assertSame([], RawCoverageRecorder::decodeAll(''));
    }

    public function test_union_merges_lines_across_files_and_records(): void
    {
        $accumulator = RawCoverageRecorder::union([], ['/srv/pim/src/A.php' => [3 => 1]]);
        $accumulator = RawCoverageRecorder::union($accumulator, ['/srv/pim/src/A.php' => [5 => 1]]);
        $accumulator = RawCoverageRecorder::union($accumulator, ['/srv/pim/src/B.php' => [9 => 1]]);

        self::assertSame(
            [
                '/srv/pim/src/A.php' => [3 => 1, 5 => 1],
                '/srv/pim/src/B.php' => [9 => 1],
            ],
            $accumulator,
        );
    }
}
