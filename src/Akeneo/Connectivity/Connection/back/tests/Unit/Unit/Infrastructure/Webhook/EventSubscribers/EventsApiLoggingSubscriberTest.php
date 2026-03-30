<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepositoryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers\EventsApiLoggingSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiLoggingSubscriberTest extends TestCase
{
    private EventsApiDebugRepositoryInterface|MockObject $repository;
    private EventsApiLoggingSubscriber $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EventsApiDebugRepositoryInterface::class);
        $this->sut = new EventsApiLoggingSubscriber($this->repository);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(EventsApiLoggingSubscriber::class, $this->sut);
    }

    public function test_it_flushes_the_logs(): void
    {
        $this->repository->expects($this->once())->method('flush');
        $this->sut->flushLogs();
    }
}
