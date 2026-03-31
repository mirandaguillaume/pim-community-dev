<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher\ObjectDetacher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ObjectDetacherTest extends TestCase
{
    private EntityManagerInterface|MockObject $manager;
    private ObjectDetacher $sut;

    protected function setUp(): void
    {
        $this->manager = $this->createMock(EntityManagerInterface::class);
        $this->sut = new ObjectDetacher($this->manager);
    }

    public function test_it_detaches_an_object_from_entity_manager(): void
    {
        $uow = $this->createMock(UnitOfWork::class);
        $classMetadata = $this->createMock(ClassMetadata::class);

        $object = new \stdClass();
        $this->manager->method('getUnitOfWork')->willReturn($uow);
        $this->manager->method('getClassMetadata')->with('stdClass')->willReturn($classMetadata);
        $classMetadata->rootEntityName = 'stdClass';
        $this->manager->expects($this->once())->method('detach')->with($object);
        $this->sut->detach($object);
    }

    public function test_it_detaches_many_objects_from_entity_manager(): void
    {
        $uow = $this->createMock(UnitOfWork::class);
        $classMetadata = $this->createMock(ClassMetadata::class);

        $object1 = new \stdClass();
        $object2 = new \stdClass();
        $objects = [$object1, $object2];
        $this->manager->method('getUnitOfWork')->willReturn($uow);
        $this->manager->method('getClassMetadata')->with('stdClass')->willReturn($classMetadata);
        $classMetadata->rootEntityName = 'stdClass';
        $this->manager->expects($this->once())->method('detach')->with($object1);
        $this->manager->expects($this->once())->method('detach')->with($object2);
        $this->sut->detachAll($objects);
    }
}
