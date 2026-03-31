<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\EventsApi;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestSucceededEvent;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EventsApi\DispatchReadProductEventFromEventsApiSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Platform\Component\EventQueue\Event;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DispatchReadProductEventFromEventsApiSubscriberTest extends TestCase
{
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private DispatchReadProductEventFromEventsApiSubscriber $sut;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->sut = new DispatchReadProductEventFromEventsApiSubscriber($this->eventDispatcher);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DispatchReadProductEventFromEventsApiSubscriber::class, $this->sut);
    }

    public function test_it_dispatches_a_read_product_on_product_events_api_saved(): void
    {
        $eventsApiRequestSucceeded = $this->createMock(EventsApiRequestSucceededEvent::class);
        $productCreatedEvent = $this->createMock(ProductCreated::class);
        $productUpdatedEvent = $this->createMock(ProductUpdated::class);
        $productRemovedEvent = $this->createMock(ProductRemoved::class);

        $eventsApiRequestSucceeded->method('getEvents')->willReturn([$productCreatedEvent, $productUpdatedEvent, $productRemovedEvent]);
        $eventsApiRequestSucceeded->method('getConnectionCode')->willReturn('code');
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->callback(
                    function (ReadProductsEvent $event) {
                        return 3 === $event->getCount()
                            && 'code' === $event->getConnectionCode();
                    }));
        $this->sut->dispatchReadProductOnProductEventsApiSaved($eventsApiRequestSucceeded);
    }

    public function test_it_doesnt_dispatch_a_read_product_on_product_events_api_saved_if_no_product_saved_event_type(): void
    {
        $eventsApiRequestSucceeded = $this->createMock(EventsApiRequestSucceededEvent::class);
        $unknownEvent = $this->createMock(Event::class);

        $eventsApiRequestSucceeded->method('getEvents')->willReturn([$unknownEvent]);
        $this->eventDispatcher->expects($this->never())->method('dispatch')->with($this->anything());
        $this->sut->dispatchReadProductOnProductEventsApiSaved($eventsApiRequestSucceeded);
    }
}
