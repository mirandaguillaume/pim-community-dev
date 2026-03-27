<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Component\EventQueue;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BulkEventTest extends TestCase
{
    private BulkEvent $sut;

    protected function setUp(): void
    {
        $this->sut = new BulkEvent([]);
    }

    public function test_it_is_a_bulk_event(): void
    {
        $this->assertInstanceOf(BulkEvent::class, $this->sut);
        $this->assertInstanceOf(BulkEventInterface::class, $this->sut);
    }

    public function test_it_returns_the_events(): void
    {
        $events = [
            $this->createEvent(),
            $this->createEvent(),
        ];
        $this->sut = new BulkEvent($events);
        $this->assertSame($events, $this->sut->getEvents());
    }

    public function test_it_validates_the_events(): void
    {
        $events = [
            $this->createEvent(),
            new \stdClass(),
        ];
        $this->expectException(\InvalidArgumentException::class);
        new BulkEvent($events);
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
