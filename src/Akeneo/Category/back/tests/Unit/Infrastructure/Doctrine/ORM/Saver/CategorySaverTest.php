<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Doctrine\ORM\Saver;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Doctrine\ORM\Saver\CategorySaver;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

class CategorySaverTest extends TestCase
{
    private ObjectManager|MockObject $objectManager;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private LockFactory|MockObject $lockFactory;
    private CategorySaver $sut;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->lockFactory = $this->createMock(LockFactory::class);
        $this->sut = new CategorySaver($this->objectManager, $this->eventDispatcher, $this->lockFactory);
    }

    public function testItIsASaver(): void
    {
        $this->assertInstanceOf(SaverInterface::class, $this->sut);
    }

    public function testItIsABulkSaver(): void
    {
        $this->assertInstanceOf(BulkSaverInterface::class, $this->sut);
    }

    public function testItSavesANewCategory(): void
    {
        $lock = $this->createMock(LockInterface::class);
        $category = $this->createMock(CategoryInterface::class);

        $category->expects($this->atLeastOnce())->method('getId')->willReturn(null);
        $category->expects($this->atLeastOnce())->method('getRoot')->willReturn(1);
        $this->lockFactory->expects($this->once())->method('createLock')->with('create_category_in_root_1', 10)->willReturn($lock);
        $lock->expects($this->once())->method('acquire')->with(true)->willReturn(true);

        $dispatchedEvents = [];
        $this->eventDispatcher->expects($this->exactly(2))->method('dispatch')->willReturnCallback(
            function (GenericEvent $event, string $eventName) use (&$dispatchedEvents) {
                $dispatchedEvents[] = $eventName;
                $this->assertInstanceOf(CategoryInterface::class, $event->getSubject());
                $this->assertTrue($event->getArgument('unitary'));
                $this->assertTrue($event->getArgument('is_new'));
                $this->assertContains($eventName, [StorageEvents::PRE_SAVE, StorageEvents::POST_SAVE]);

                return $event;
            },
        );
        $this->objectManager->expects($this->once())->method('persist')->with($category);
        $this->objectManager->expects($this->once())->method('flush');
        $lock->expects($this->once())->method('release');
        $this->sut->save($category);

        $this->assertSame([StorageEvents::PRE_SAVE, StorageEvents::POST_SAVE], $dispatchedEvents);
    }

    public function testItSavesAnExistingCategory(): void
    {
        $lock = $this->createMock(LockInterface::class);
        $category = $this->createMock(CategoryInterface::class);

        $category->expects($this->atLeastOnce())->method('getId')->willReturn(42);
        $category->expects($this->atLeastOnce())->method('getRoot')->willReturn(1);
        $this->lockFactory->expects($this->once())->method('createLock')->with('create_category_in_root_1', 10)->willReturn($lock);
        $lock->expects($this->once())->method('acquire')->with(true)->willReturn(true);

        $this->eventDispatcher->expects($this->exactly(2))->method('dispatch')->willReturnCallback(
            function (GenericEvent $event, string $eventName) {
                $this->assertInstanceOf(CategoryInterface::class, $event->getSubject());
                $this->assertTrue($event->getArgument('unitary'));
                $this->assertFalse($event->getArgument('is_new'));

                return $event;
            },
        );
        $this->objectManager->expects($this->once())->method('persist')->with($category);
        $this->objectManager->expects($this->once())->method('flush');
        $lock->expects($this->once())->method('release');
        $this->sut->save($category);
    }

    public function testItThrowsIfTheLockCannotBeAcquired(): void
    {
        $lock = $this->createMock(LockInterface::class);
        $category = $this->createMock(CategoryInterface::class);

        $category->method('getId')->willReturn(null);
        $category->method('getRoot')->willReturn(1);
        $this->lockFactory->expects($this->once())->method('createLock')->with('create_category_in_root_1', 10)->willReturn($lock);
        $lock->expects($this->exactly(3))->method('acquire')->with(true)->willThrowException(new LockConflictedException());
        $this->objectManager->expects($this->never())->method('persist');
        $this->objectManager->expects($this->never())->method('flush');
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('The lock for creating new categories cannot be acquired.');
        $this->sut->save($category);
    }

    public function testItThrowsOnNonCategoryObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->save(new \stdClass());
    }

    public function testLockReleasesEvenOnException(): void
    {
        $lock = $this->createMock(LockInterface::class);
        $category = $this->createMock(CategoryInterface::class);

        $category->method('getId')->willReturn(null);
        $category->method('getRoot')->willReturn(2);
        $this->lockFactory->expects($this->once())->method('createLock')->with('create_category_in_root_2', 10)->willReturn($lock);
        $lock->expects($this->once())->method('acquire')->with(true)->willReturn(true);
        $this->objectManager->expects($this->once())->method('persist')->with($category);
        $this->objectManager->expects($this->once())->method('flush')->willThrowException(new \RuntimeException('DB error'));
        $lock->expects($this->once())->method('release');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB error');
        $this->sut->save($category);
    }

    public function testSaveAllWithEmptyArray(): void
    {
        $this->objectManager->expects($this->never())->method('persist');
        $this->objectManager->expects($this->never())->method('flush');
        $this->eventDispatcher->expects($this->never())->method('dispatch');
        $this->sut->saveAll([]);
    }

    public function testSaveAllDispatchesEventsInCorrectOrder(): void
    {
        $category1 = $this->createMock(CategoryInterface::class);
        $category2 = $this->createMock(CategoryInterface::class);
        $category1->method('getId')->willReturn(null);
        $category2->method('getId')->willReturn(5);

        $dispatchedEvents = [];
        $this->eventDispatcher->expects($this->exactly(6))->method('dispatch')->willReturnCallback(
            function (GenericEvent $event, string $eventName) use (&$dispatchedEvents) {
                $dispatchedEvents[] = $eventName;

                return $event;
            },
        );
        $this->objectManager->expects($this->exactly(2))->method('persist');
        $this->objectManager->expects($this->once())->method('flush');

        $this->sut->saveAll([$category1, $category2]);

        $this->assertSame([
            StorageEvents::PRE_SAVE_ALL,
            StorageEvents::PRE_SAVE,
            StorageEvents::PRE_SAVE,
            StorageEvents::POST_SAVE,
            StorageEvents::POST_SAVE,
            StorageEvents::POST_SAVE_ALL,
        ], $dispatchedEvents);
    }

    public function testSaveAllSetsUnitaryToFalse(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $category->method('getId')->willReturn(null);

        $this->eventDispatcher->expects($this->exactly(4))->method('dispatch')->willReturnCallback(
            function (GenericEvent $event, string $eventName) {
                if (in_array($eventName, [StorageEvents::PRE_SAVE, StorageEvents::POST_SAVE])) {
                    $this->assertFalse($event->getArgument('unitary'));
                }

                return $event;
            },
        );
        $this->objectManager->expects($this->once())->method('persist');
        $this->objectManager->expects($this->once())->method('flush');

        $this->sut->saveAll([$category]);
    }

    public function testSaveAllTracksIsNewPerObject(): void
    {
        $newCategory = $this->createMock(CategoryInterface::class);
        $existingCategory = $this->createMock(CategoryInterface::class);
        $newCategory->method('getId')->willReturn(null);
        $existingCategory->method('getId')->willReturn(10);

        $isNewValues = [];
        $this->eventDispatcher->expects($this->exactly(6))->method('dispatch')->willReturnCallback(
            function (GenericEvent $event, string $eventName) use (&$isNewValues) {
                if (in_array($eventName, [StorageEvents::PRE_SAVE, StorageEvents::POST_SAVE])) {
                    $isNewValues[] = $event->getArgument('is_new');
                }

                return $event;
            },
        );
        $this->objectManager->expects($this->exactly(2))->method('persist');
        $this->objectManager->expects($this->once())->method('flush');

        $this->sut->saveAll([$newCategory, $existingCategory]);
        // PRE_SAVE for new, PRE_SAVE for existing, POST_SAVE for new, POST_SAVE for existing
        $this->assertSame([true, false, true, false], $isNewValues);
    }

    public function testLockUsesCorrectRootId(): void
    {
        $lock = $this->createMock(LockInterface::class);
        $category = $this->createMock(CategoryInterface::class);

        $category->method('getId')->willReturn(null);
        $category->expects($this->atLeastOnce())->method('getRoot')->willReturn(42);
        $this->lockFactory->expects($this->once())->method('createLock')->with('create_category_in_root_42', 10)->willReturn($lock);
        $lock->method('acquire')->willReturn(true);
        $this->eventDispatcher->method('dispatch')->willReturnArgument(0);
        $this->objectManager->method('persist');
        $this->objectManager->method('flush');
        $lock->expects($this->once())->method('release');

        $this->sut->save($category);
    }
}
