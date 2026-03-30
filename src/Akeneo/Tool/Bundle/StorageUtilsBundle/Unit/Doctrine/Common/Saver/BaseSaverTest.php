<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\Exception\DuplicateObjectException;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Saver\BaseSaver;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

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
        $this->eventDispatcher->method('dispatch')->with($this->anything(), $this->isType('string'))->willReturn($this->isType('object'));
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
        $this->objectManager->expects($this->once())->method('persist')->with($type1);
        $this->objectManager->expects($this->once())->method('persist')->with($type2);
        $this->objectManager->expects($this->once())->method('flush');
        $this->sut->saveAll([$type1, $type2]);
    }

    public function test_it_throws_exception_when_saving_anything_else_than_the_expected_class(): void
    {
        $anythingElse = new ModelNotToSave();
        $exception = new \InvalidArgumentException(
            sprintf(
                'Expects a "%s", "%s" provided.',
                ModelToSave::class,
                $anythingElse::class
            )
        );
        $this->expectException($exception);
        $this->sut->save($anythingElse);
        $this->expectException($exception);
        $this->sut->saveAll([$anythingElse, $anythingElse]);
    }

    public function test_it_dispatches_events_according_to_the_objects_state_on_unitary_save(): void
    {
        $newObject = new ModelToSave();
        $newObjectEvent = new GenericEvent($newObject, ['unitary' => true, 'is_new' => true]);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($newObjectEvent, StorageEvents::PRE_SAVE);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($newObjectEvent, StorageEvents::POST_SAVE);
        $this->sut->save($newObject);
        $updatedObject = new ModelToSave(42);
        $updatedObjectEvent = new GenericEvent($updatedObject, ['unitary' => true, 'is_new' => false]);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($updatedObjectEvent, StorageEvents::PRE_SAVE);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($updatedObjectEvent, StorageEvents::POST_SAVE);
        $this->sut->save($updatedObject);
    }

    public function test_it_dispatches_events_according_to_the_objects_state_on_bulk_save(): void
    {
        $newObject = new ModelToSave();
        $updatedObject = new ModelToSave(42);
        $bulkEvent = new GenericEvent([$newObject, $updatedObject], ['unitary' => false]);
        $newObjectEvent = new GenericEvent($newObject, ['unitary' => false, 'is_new' => true]);
        $updatedObjectEvent = new GenericEvent($updatedObject, ['unitary' => false, 'is_new' => false]);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($bulkEvent, StorageEvents::PRE_SAVE_ALL);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($newObjectEvent, StorageEvents::PRE_SAVE);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($updatedObjectEvent, StorageEvents::PRE_SAVE);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($newObjectEvent, StorageEvents::POST_SAVE);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($updatedObjectEvent, StorageEvents::POST_SAVE);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($bulkEvent, StorageEvents::POST_SAVE_ALL);
        $this->sut->saveAll([$newObject, $updatedObject]);
    }

    public function test_it_catches_orm_exception_and_throws_a_business_exception(): void
    {
        $type = new ModelToSave();
        $this->objectManager->method('persist')->with($type)->willThrowException(UniqueConstraintViolationException::class);
        $this->objectManager->expects($this->never())->method('flush');
        $this->expectException(DuplicateObjectException::class);
        $this->sut->save($type);
    }
}
