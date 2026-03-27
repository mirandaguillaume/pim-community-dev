<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Component\Webhook;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EventDataCollectionTest extends TestCase
{
    private EventDataCollection $sut;

    protected function setUp(): void
    {
        $this->sut = new EventDataCollection();
    }

    public function test_it_is_an_event_data_collection(): void
    {
        $this->assertInstanceOf(EventDataCollection::class, $this->sut);
    }

    public function test_it_holds_an_event_data(): void
    {
        $event = $this->createEvent();
        $data = ['data'];
        $this->sut->setEventData($event, $data);
        $this->assertSame($data, $this->sut->getEventData($event));
    }

    public function test_it_holds_an_event_data_error(): void
    {
        $event = $this->createEvent();
        $error = new \Exception();
        $this->sut->setEventDataError($event, $error);
        $this->assertSame($error, $this->sut->getEventData($event));
    }

    private function createEvent(): EventInterface
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $data = [];

        return new class ($author, $data) extends Event {
            public function getName(): string
            {
                return 'event_name';
            }
        };
    }
}
