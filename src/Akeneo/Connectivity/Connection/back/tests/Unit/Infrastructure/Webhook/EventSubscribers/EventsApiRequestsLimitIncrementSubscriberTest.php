<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\UpdateEventsApiRequestCountQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers\EventsApiRequestsLimitIncrementSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiRequestsLimitIncrementSubscriberTest extends TestCase
{
    private UpdateEventsApiRequestCountQueryInterface|MockObject $eventsApiRequestCountQuery;
    private EventsApiRequestsLimitIncrementSubscriber $sut;

    protected function setUp(): void
    {
        $this->eventsApiRequestCountQuery = $this->createMock(UpdateEventsApiRequestCountQueryInterface::class);
        $this->sut = new EventsApiRequestsLimitIncrementSubscriber($this->eventsApiRequestCountQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(EventsApiRequestsLimitIncrementSubscriber::class, $this->sut);
    }

    public function test_it_increments_request_count(): void
    {
        $this->assertSame(1, $this->sut->incrementRequestCount());
        $this->assertSame(2, $this->sut->incrementRequestCount());
    }

    public function test_it_saves_request_count(): void
    {
        $this->eventsApiRequestCountQuery->expects($this->once())->method('execute')->with(/* TODO: convert Argument matcher */ Argument::type(\DateTimeImmutable::class), 1);
        $this->sut->incrementRequestCount();
        $this->sut->saveRequestCount();
    }

    public function test_it_resets_the_request_count_to_zero_after_saving_it(): void
    {
        $this->eventsApiRequestCountQuery->expects($this->once())->method('execute');
        $this->sut->incrementRequestCount();
        $this->sut->saveRequestCount();
        $this->assertSame(1, $this->sut->incrementRequestCount());
    }

    public function test_it_doesnt_save_request_count_of_zero(): void
    {
        $this->eventsApiRequestCountQuery->expects($this->never())->method('execute');
        $this->sut->saveRequestCount();
    }
}
