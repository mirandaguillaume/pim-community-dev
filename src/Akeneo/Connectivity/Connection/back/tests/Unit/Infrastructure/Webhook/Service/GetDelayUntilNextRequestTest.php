<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectEventsApiRequestCountWithinLastHourQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\GetDelayUntilNextRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetDelayUntilNextRequestTest extends TestCase
{
    private SelectEventsApiRequestCountWithinLastHourQueryInterface|MockObject $selectEventsApiRequestCountWithinLastHourQuery;
    private GetDelayUntilNextRequest $sut;

    protected function setUp(): void
    {
        $this->selectEventsApiRequestCountWithinLastHourQuery = $this->createMock(SelectEventsApiRequestCountWithinLastHourQueryInterface::class);
        $this->sut = new GetDelayUntilNextRequest($this->selectEventsApiRequestCountWithinLastHourQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetDelayUntilNextRequest::class, $this->sut);
    }

    public function test_it_returns_the_delay_until_next_request_even_when_there_is_no_entry(): void
    {
        $this->selectEventsApiRequestCountWithinLastHourQuery->method('execute')->with(new \DateTimeImmutable('2021-01-08 10:12:30', new \DateTimeZone('UTC')))->willReturn([]);
        $this->assertSame(0, $this->sut->execute(new \DateTimeImmutable('2021-01-08 10:12:30', new \DateTimeZone('UTC')), 100));
    }

    public function test_it_returns_the_delay_until_next_request(): void
    {
        $this->selectEventsApiRequestCountWithinLastHourQuery->method('execute')->with(new \DateTimeImmutable('2021-01-08 11:02:10', new \DateTimeZone('UTC')))->willReturn([
                        [
                            'event_count' => 20,
                            'updated' => '2021-01-08 10:32:30',
                        ],
                        [
                            'event_count' => 90,
                            'updated' => '2021-01-08 10:11:30', // Limit will be reached here, minute xx:11:30 = 690 seconds.
                        ],
                    ]);
        $this->assertSame(560, $this->sut->execute(new \DateTimeImmutable('2021-01-08 11:02:10', new \DateTimeZone('UTC')), 100));
        // Current time minute is xx:02:10 = 130 seconds, so 690 - 130 = 560 seconds
    }
}
