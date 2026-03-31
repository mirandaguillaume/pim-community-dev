<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Saver;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Saver\BaseSaver;
use Akeneo\Tool\Component\StorageUtils\Exception\DuplicateObjectException;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ModelToSave
{
    public function __construct(private readonly ?int $id = null)
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}

class ModelNotToSave
{
}

class BaseSaverTest extends TestCase
{
    private ObjectManager|MockObject $objectManager;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private BaseSaver $sut;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->sut = new BaseSaver(
            $this->objectManager,
            $this->eventDispatcher,
            ModelToSave::class
        );
        $this->eventDispatcher->method('dispatch')->willReturnArgument(0);
    }

    public function test_it_is_a_saver(): void
    {
        $this->assertInstanceOf(SaverInterface::class, $this->sut);
        $this->assertInstanceOf(BulkSaverInterface::class, $this->sut);
    }

    public function test_it_persists_the_object_and_flushes_the_unit_of_work(): void
    {
        $type = new ModelToSave();
        $this->objectManager->expects($this->once())->method('persist')->with($type);
        $this->objectManager->expects($this->once())->method('flush');
        $this->sut->save($type);
    }

    public function test_it_persists_the_objects_and_flushes_the_unit_of_work(): void
    {
        $type1 = new ModelToSave();
        $type2 = new ModelToSave();
        $this->objectManager->expects($this->exactly(2))->method('persist');
        $this->objectManager->expects($this->once())->method('flush');
        $this->sut->saveAll([$type1, $type2]);
    }

    public function test_it_throws_exception_when_saving_anything_else_than_the_expected_class(): void
    {
        $anythingElse = new ModelNotToSave();
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->save($anythingElse);
    }

    public function test_it_dispatches_events_according_to_the_objects_state_on_unitary_save(): void
    {
        $newObject = new ModelToSave();
        $updatedObject = new ModelToSave(42);

        $dispatchedEvents = [];
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->eventDispatcher->method('dispatch')->willReturnCallback(
            function (object $event, string $eventName) use (&$dispatchedEvents) {
                $dispatchedEvents[] = $eventName;
                return $event;
            }
        );
        $this->sut = new BaseSaver($this->objectManager, $this->eventDispatcher, ModelToSave::class);

        $this->sut->save($newObject);
        $this->assertContains(StorageEvents::PRE_SAVE, $dispatchedEvents);
        $this->assertContains(StorageEvents::POST_SAVE, $dispatchedEvents);

        $dispatchedEvents = [];
        $this->sut->save($updatedObject);
        $this->assertContains(StorageEvents::PRE_SAVE, $dispatchedEvents);
        $this->assertContains(StorageEvents::POST_SAVE, $dispatchedEvents);
    }

    public function test_it_dispatches_events_according_to_the_objects_state_on_bulk_save(): void
    {
        $newObject = new ModelToSave();
        $updatedObject = new ModelToSave(42);

        $dispatchedEvents = [];
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->eventDispatcher->method('dispatch')->willReturnCallback(
            function (object $event, string $eventName) use (&$dispatchedEvents) {
                $dispatchedEvents[] = $eventName;
                return $event;
            }
        );
        $this->sut = new BaseSaver($this->objectManager, $this->eventDispatcher, ModelToSave::class);

        $this->sut->saveAll([$newObject, $updatedObject]);
        $this->assertContains(StorageEvents::PRE_SAVE_ALL, $dispatchedEvents);
        $this->assertContains(StorageEvents::POST_SAVE_ALL, $dispatchedEvents);
        $this->assertContains(StorageEvents::PRE_SAVE, $dispatchedEvents);
        $this->assertContains(StorageEvents::POST_SAVE, $dispatchedEvents);
    }

    public function test_it_catches_orm_exception_and_throws_a_business_exception(): void
    {
        $type = new ModelToSave();
        $this->objectManager->method('persist')->with($type)->willThrowException($this->createMock(UniqueConstraintViolationException::class));
        $this->objectManager->expects($this->never())->method('flush');
        $this->expectException(DuplicateObjectException::class);
        $this->sut->save($type);
    }
}
