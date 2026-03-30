<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Audit;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;
use Akeneo\Connectivity\Connection\Infrastructure\Audit\AggregateAuditData;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AggregateAuditDataTest extends TestCase
{
    private AggregateAuditData $sut;

    protected function setUp(): void
    {
        $this->sut = new AggregateAuditData();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AggregateAuditData::class, $this->sut);
    }

    public function test_it_normalizes_period_event_counts(): void
    {
        $periodEventCounts = [
                    new Read\PeriodEventCount(
                        '<all>',
                        new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
                        new \DateTimeImmutable('2020-01-03 00:00:00', new \DateTimeZone('UTC')),
                        [
                            new Read\HourlyEventCount(
                                new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
                                10
                            ),
                        ]
                    ),
                    new Read\PeriodEventCount(
                        'magento',
                        new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')),
                        new \DateTimeImmutable('2020-01-05 00:00:00', new \DateTimeZone('UTC')),
                        [
                            new Read\HourlyEventCount(
                                new \DateTimeImmutable('2020-01-04 23:00:00', new \DateTimeZone('UTC')),
                                100
                            ),
                        ]
                    ),
                ];
        $dateTimeZone = new \DateTimeZone('UTC');
        $this->assertSame([
                    '<all>' => [
                        'previous_week' => [
                            '2020-01-01' => 10,
                        ],
                        'current_week' => [
                            '2020-01-02' => 0,
                        ],
                        'current_week_total' => 0,
                    ],
                    'magento' => [
                        'previous_week' => [
                            '2020-01-02' => 0,
                        ],
                        'current_week' => [
                            '2020-01-03' => 0,
                            '2020-01-04' => 100,
                        ],
                        'current_week_total' => 100,
                    ],
                ], AggregateAuditData::normalize($periodEventCounts, $dateTimeZone));
    }

    public function test_it_normalizes_period_event_counts_with_the_user_timezone(): void
    {
        $periodEventCounts = [
                    new Read\PeriodEventCount(
                        '<all>',
                        new \DateTimeImmutable('2019-12-31 15:00:00', new \DateTimeZone('UTC')),
                        new \DateTimeImmutable('2020-01-03 15:00:00', new \DateTimeZone('UTC')),
                        [
                            new Read\HourlyEventCount(
                                new \DateTimeImmutable('2019-12-31 15:00:00', new \DateTimeZone('UTC')),
                                10
                            ),
                            new Read\HourlyEventCount(
                                new \DateTimeImmutable('2020-01-01 15:00:00', new \DateTimeZone('UTC')),
                                100
                            ),
                            new Read\HourlyEventCount(
                                new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')),
                                1000
                            ),
                            new Read\HourlyEventCount(
                                new \DateTimeImmutable('2020-01-02 15:00:00', new \DateTimeZone('UTC')),
                                10000
                            ),
                        ]
                    ),
                ];
        $dateTimeZone = new \DateTimeZone('Asia/Tokyo');
        // UTC+09:00 no Daylight saving time

        $this->assertSame([
            '<all>' => [
                'previous_week' => [
                    '2020-01-01' => 10,
                ],
                'current_week' => [
                    '2020-01-02' => 1100,
                    '2020-01-03' => 10000,
                ],
                'current_week_total' => 11100,
            ],
        ], AggregateAuditData::normalize($periodEventCounts, $dateTimeZone));
    }
}
