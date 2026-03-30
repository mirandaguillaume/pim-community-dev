<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Webhook\Event;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestSucceededEvent;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiRequestSucceededEventTest extends TestCase
{
    private EventInterface|MockObject $event;
    private EventsApiRequestSucceededEvent $sut;

    protected function setUp(): void
    {
        $this->event = $this->createMock(EventInterface::class);
        $this->sut = new EventsApiRequestSucceededEvent('connectionCode', [$this->event]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(EventsApiRequestSucceededEvent::class, $this->sut);
    }

    public function test_it_provides_the_events(): void
    {
        $this->assertSame([$this->event], $this->sut->getEvents());
    }

    public function test_it_provides_the_connection_code(): void
    {
        $this->assertSame('connectionCode', $this->sut->getConnectionCode());
    }

    public function test_it_throws_when_events_have_an_unexpected_class(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->__construct('code', [new \stdClass()]);
    }
}
