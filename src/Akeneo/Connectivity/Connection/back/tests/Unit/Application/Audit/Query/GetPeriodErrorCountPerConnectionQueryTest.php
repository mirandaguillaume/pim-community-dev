<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodErrorCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetPeriodErrorCountPerConnectionQueryTest extends TestCase
{
    private GetPeriodErrorCountPerConnectionQuery $sut;

    protected function setUp(): void
    {
        $this->sut = new GetPeriodErrorCountPerConnectionQuery(new DateTimePeriod(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'))
        ));
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetPeriodErrorCountPerConnectionQuery::class, $this->sut);
    }

    public function test_it_returns_the_period(): void
    {
        $this->assertEquals(new DateTimePeriod(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'))
        ), $this->sut->period());
    }
}
