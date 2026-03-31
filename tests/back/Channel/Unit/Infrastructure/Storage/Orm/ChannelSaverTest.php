<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Storage\Orm;

use Akeneo\Channel\Infrastructure\Component\Event\ChannelCategoryHasBeenUpdated;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Saver\ChannelSaverInterface;
use Akeneo\Channel\Infrastructure\Storage\Orm\ChannelSaver;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ChannelSaverTest extends TestCase
{
    private ObjectManager|MockObject $objectManager;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private ChannelSaver $sut;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->sut = new ChannelSaver($this->objectManager, $this->eventDispatcher);
    }

    public function test_it_is_a_channel_saver(): void
    {
        $this->assertInstanceOf(ChannelSaverInterface::class, $this->sut);
    }

    public function test_it_saves_a_channel(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $channel->method('getId')->willReturn(null);
        $channel->method('popEvents')->willReturn([]);
        $dispatched = [];
        $this->eventDispatcher->expects($this->exactly(2))->method('dispatch')->willReturnCallback(
            function ($event, $eventName = null) use (&$dispatched) {
                $dispatched[] = [$event, $eventName];
                return $event;
            }
        );
        $this->objectManager->expects($this->once())->method('persist')->with($channel);
        $this->objectManager->expects($this->once())->method('flush');
        $this->sut->save($channel);

        // Verify pre_save and post_save events
        $this->assertSame(StorageEvents::PRE_SAVE, $dispatched[0][1]);
        $this->assertInstanceOf(GenericEvent::class, $dispatched[0][0]);
        $this->assertSame(StorageEvents::POST_SAVE, $dispatched[1][1]);
        $this->assertInstanceOf(GenericEvent::class, $dispatched[1][0]);
    }

    public function test_it_saves_multiple_channels(): void
    {
        $channel1 = $this->createMock(ChannelInterface::class);
        $channel2 = $this->createMock(ChannelInterface::class);

        $channel1->method('getId')->willReturn(null);
        $channel1->method('popEvents')->willReturn([]);
        $channel2->method('getId')->willReturn(null);
        $channel2->method('popEvents')->willReturn([]);
        $dispatched = [];
        $this->eventDispatcher->expects($this->exactly(6))->method('dispatch')->willReturnCallback(
            function ($event, $eventName = null) use (&$dispatched) {
                $dispatched[] = [$event, $eventName];
                return $event;
            }
        );
        $this->objectManager->expects($this->exactly(2))->method('persist');
        $this->objectManager->expects($this->once())->method('flush');
        $this->sut->saveAll([$channel1, $channel2]);

        $eventNames = array_column($dispatched, 1);
        $this->assertContains(StorageEvents::PRE_SAVE_ALL, $eventNames);
        $this->assertContains(StorageEvents::POST_SAVE_ALL, $eventNames);
    }

    public function test_it_adds_the_option_is_new_when_a_channel_is_created(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $channel->method('getId')->willReturn(0);
        $channel->method('getCode')->willReturn('channel-code');
        $channel->method('popEvents')->willReturn([]);
        $dispatched = [];
        $this->eventDispatcher->expects($this->exactly(2))->method('dispatch')->willReturnCallback(
            function ($event, $eventName = null) use (&$dispatched) {
                $dispatched[] = [$event, $eventName];
                return $event;
            }
        );
        $this->sut->save($channel);

        // getId() returns 0 (truthy in the SUT: `null !== $channel->getId()`), so is_new = false
        $this->assertSame(StorageEvents::PRE_SAVE, $dispatched[0][1]);
        $this->assertFalse($dispatched[0][0]->getArgument('is_new'));
        $this->assertSame(StorageEvents::POST_SAVE, $dispatched[1][1]);
        $this->assertFalse($dispatched[1][0]->getArgument('is_new'));
    }

    public function test_it_doesnt_add_the_option_is_new_when_a_channel_is_updated(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $channel->method('getId')->willReturn(null);
        $channel->method('popEvents')->willReturn([]);
        $dispatched = [];
        $this->eventDispatcher->expects($this->exactly(2))->method('dispatch')->willReturnCallback(
            function ($event, $eventName = null) use (&$dispatched) {
                $dispatched[] = [$event, $eventName];
                return $event;
            }
        );
        $this->sut->save($channel);

        // getId() returns null → is_new = true
        $this->assertSame(StorageEvents::PRE_SAVE, $dispatched[0][1]);
        $this->assertTrue($dispatched[0][0]->getArgument('is_new'));
        $this->assertSame(StorageEvents::POST_SAVE, $dispatched[1][1]);
        $this->assertTrue($dispatched[1][0]->getArgument('is_new'));
    }

    public function test_it_triggers_a_specific_event_when_a_channel_category_is_updated(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $channelCategoryHasBeenUpdated = new ChannelCategoryHasBeenUpdated('channel-code', 'previous-category-code', 'new-category-code');
        $channel->method('getId')->willReturn(null);
        $channel->method('popEvents')->willReturn([$channelCategoryHasBeenUpdated]);
        $dispatched = [];
        $this->eventDispatcher->expects($this->exactly(3))->method('dispatch')->willReturnCallback(
            function ($event, $eventName = null) use (&$dispatched) {
                $dispatched[] = [$event, $eventName];
                return $event;
            }
        );
        $this->sut->save($channel);

        $this->assertSame(StorageEvents::PRE_SAVE, $dispatched[0][1]);
        $this->assertSame(StorageEvents::POST_SAVE, $dispatched[1][1]);
        // Third dispatch is the ChannelCategoryHasBeenUpdated event
        $this->assertInstanceOf(ChannelCategoryHasBeenUpdated::class, $dispatched[2][0]);
        $this->assertSame('channel-code', $dispatched[2][0]->channelCode());
        $this->assertSame('previous-category-code', $dispatched[2][0]->previousCategoryCode());
        $this->assertSame('new-category-code', $dispatched[2][0]->newCategoryCode());
    }

    public function test_it_throws_an_exception_when_trying_to_save_anything_else_than_a_channel(): void
    {
        $anythingElse = new \stdClass();
        $this->expectException(\TypeError::class);
        $this->sut->save($anythingElse);
    }
}
