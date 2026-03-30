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
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->callback(
                        function (GenericEvent $event) {
                            return $event->getSubject() instanceof ChannelInterface
                                && $event->getArgument('unitary') === true;
                        }
                    ),
                    StorageEvents::PRE_SAVE);
        $this->objectManager->expects($this->once())->method('persist')->with($channel);
        $this->objectManager->expects($this->once())->method('flush');
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->callback(
                        function (GenericEvent $event) {
                            return $event->getSubject() instanceof ChannelInterface
                                && $event->getArgument('unitary') === true;
                        }
                    ),
                    StorageEvents::POST_SAVE);
        $this->eventDispatcher->expects($this->never())->method('dispatch')->with($this->isInstanceOf(ChannelCategoryHasBeenUpdated::class),
                    ChannelCategoryHasBeenUpdated::class);
        $this->sut->save($channel);
    }

    public function test_it_saves_multiple_channels(): void
    {
        $channel1 = $this->createMock(ChannelInterface::class);
        $channel2 = $this->createMock(ChannelInterface::class);

        $channel1->method('getId')->willReturn(null);
        $channel1->method('popEvents')->willReturn([]);
        $channel2->method('getId')->willReturn(null);
        $channel2->method('popEvents')->willReturn([]);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->callback(
                        function (GenericEvent $event) {
                            return is_countable($event->getSubject()) &&  count($event->getSubject()) === 2
                                && $event->getArgument('unitary') === false;
                        }
                    ),
                    StorageEvents::PRE_SAVE_ALL);
        $this->eventDispatcher->expects($this->exactly(2))->method('dispatch')->with($this->callback(
                        function (GenericEvent $event) {
                            return $event->getSubject() instanceof ChannelInterface
                                && $event->getArgument('unitary') === false;
                        }
                    ),
                    StorageEvents::PRE_SAVE);
        $this->objectManager->expects($this->once())->method('persist')->with($channel1);
        $this->objectManager->expects($this->once())->method('persist')->with($channel2);
        $this->objectManager->expects($this->once())->method('flush');
        $this->eventDispatcher->expects($this->exactly(2))->method('dispatch')->with($this->callback(
                        function (GenericEvent $event) {
                            return $event->getSubject() instanceof ChannelInterface
                                && $event->getArgument('unitary') === false;
                        }
                    ),
                    StorageEvents::POST_SAVE);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->callback(
                        function (GenericEvent $event) {
                            return is_countable($event->getSubject()) && count($event->getSubject()) === 2
                                && $event->getArgument('unitary') === false;
                        }
                    ),
                    StorageEvents::POST_SAVE_ALL);
        $this->eventDispatcher->expects($this->never())->method('dispatch')->with($this->isInstanceOf(ChannelCategoryHasBeenUpdated::class),
                    ChannelCategoryHasBeenUpdated::class);
        $this->sut->saveAll([$channel1, $channel2]);
    }

    public function test_it_adds_the_option_is_new_when_a_channel_is_created(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $channel->method('getId')->willReturn(0);
        $channel->method('getCode')->willReturn('channel-code');
        $channel->method('popEvents')->willReturn([]);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->callback(
                        function (GenericEvent $event) {
                            return $event->getSubject() instanceof ChannelInterface
                                && $event->getArgument('is_new') === false;
                        }
                    ),
                    StorageEvents::PRE_SAVE);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->callback(
                        function (GenericEvent $event) {
                            return $event->getSubject() instanceof ChannelInterface
                                && $event->getArgument('is_new') === false;
                        }
                    ),
                    StorageEvents::POST_SAVE);
        $this->sut->save($channel);
    }

    public function test_it_doesnt_add_the_option_is_new_when_a_channel_is_updated(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $channel->method('getId')->willReturn(null);
        $channel->method('popEvents')->willReturn([]);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->callback(
                        function (GenericEvent $event) {
                            return $event->getSubject() instanceof ChannelInterface
                                && $event->getArgument('is_new') === true;
                        }
                    ),
                    StorageEvents::PRE_SAVE);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->callback(
                        function (GenericEvent $event) {
                            return $event->getSubject() instanceof ChannelInterface
                                && $event->getArgument('is_new') === true;
                        }
                    ),
                    StorageEvents::POST_SAVE);
        $this->sut->save($channel);
    }

    public function test_it_triggers_a_specific_event_when_a_channel_category_is_updated(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $channelCategoryHasBeenUpdated = new ChannelCategoryHasBeenUpdated('channel-code', 'previous-category-code', 'new-category-code');
        $channel->method('getId')->willReturn(null);
        $channel->method('popEvents')->willReturn([$channelCategoryHasBeenUpdated]);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->isInstanceOf(GenericEvent::class),
                    StorageEvents::PRE_SAVE);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->isInstanceOf(GenericEvent::class),
                    StorageEvents::POST_SAVE);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->callback(
                        function ($event) {
                            return $event instanceof ChannelCategoryHasBeenUpdated
                                && $event->channelCode() === 'channel-code'
                                && $event->previousCategoryCode() === 'previous-category-code'
                                && $event->newCategoryCode() === 'new-category-code';
                        }
                    ));
        $this->sut->save($channel);
    }

    public function test_it_throws_an_exception_when_trying_to_save_anything_else_than_a_channel(): void
    {
        $anythingElse = new \stdClass();
        $this->expectException(\TypeError::class);
        $this->sut->save($anythingElse);
    }
}
