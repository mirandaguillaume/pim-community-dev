<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetErrorCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetErrorCountPerConnectionQueryTest extends TestCase
{
    private GetErrorCountPerConnectionQuery $sut;

    protected function setUp(): void
    {
        $this->sut = new GetErrorCountPerConnectionQuery(
            ErrorTypes::BUSINESS,
            new \DateTimeImmutable('2020-05-10 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-05-12 00:00:00', new \DateTimeZone('UTC'))
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetErrorCountPerConnectionQuery::class, $this->sut);
    }

    public function test_it_returns_the_error_type(): void
    {
        $this->assertSame(ErrorTypes::BUSINESS, $this->sut->errorType());
    }

    public function test_it_returns_the_from_date_time(): void
    {
        $this->assertEquals(new \DateTimeImmutable('2020-05-10 00:00:00', new \DateTimeZone('UTC')), $this->sut->fromDateTime());
    }

    public function test_it_returns_the_up_to_date_time(): void
    {
        $this->assertEquals(new \DateTimeImmutable('2020-05-12 00:00:00', new \DateTimeZone('UTC')), $this->sut->upToDateTime());
    }

    public function test_it_checks_that_the_from_date_time_is_utc(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GetErrorCountPerConnectionQuery(
            ErrorTypes::TECHNICAL,
            new \DateTimeImmutable('2020-05-01 00:00:00', new \DateTimeZone('Europe/Paris')),
            new \DateTimeImmutable('2020-05-02 00:00:00', new \DateTimeZone('UTC'))
        );
    }

    public function test_it_checks_that_the_up_to_date_time_is_utc(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GetErrorCountPerConnectionQuery(
            ErrorTypes::TECHNICAL,
            new \DateTimeImmutable('2020-05-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('Europe/Paris'))
        );
    }
}
