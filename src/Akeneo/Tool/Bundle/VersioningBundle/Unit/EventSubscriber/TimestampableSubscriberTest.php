<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadata as ORMClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\UnitOfWork as ORMUnitOfWork;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\TimestampableSubscriber;

class TimestampableSubscriberTest extends TestCase
{
    private EntityManagerInterface|MockObject $em;
    private TimestampableSubscriber $sut;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->sut = new TimestampableSubscriber($this->em);
    }

    public function test_it_does_not_apply_on_non_version_object(): void
    {
        $args = $this->createMock(LifecycleEventArgs::class);
        $object = $this->createMock(stdClass::class);

        $args->method('getObject')->willReturn($object);
        $args->expects($this->never())->method('getObjectManager');
        $this->sut->prePersist($args);
    }

    public function test_it_does_not_apply_on_non_timestampable_versioned_object(): void
    {
        $args = $this->createMock(LifecycleEventArgs::class);
        $version = $this->createMock(Version::class);
        $metadata = $this->createMock(ClassMetadata::class);

        $this->em->method('getClassMetadata')->with('bar')->willReturn($metadata);
        $metadata->method('getReflectionClass')->willReturn(new \ReflectionClass(NonTimestampableInterface::class));
        $version->method('getResourceId')->willReturn('foo');
        $version->method('getResourceName')->willReturn('bar');
        $args->method('getObject')->willReturn($version);
        $this->em->expects($this->never())->method('find');
        $this->sut->prePersist($args);
    }

    public function test_it_applies_on_timestampable_versioned_object_with_an_entity_manager(): void
    {
        $args = $this->createMock(LifecycleEventArgs::class);
        $uow = $this->createMock(UnitOfWork::class);
        $version = $this->createMock(Version::class);
        $object = $this->createMock(TimestampableInterface::class);
        $metadata = $this->createMock(ClassMetadata::class);

        $this->em->method('getClassMetadata')->with('bar')->willReturn($metadata);
        $metadata->method('getReflectionClass')->willReturn(new \ReflectionClass(TimestampableInterface::class));
        $version->method('getResourceId')->willReturn('foo');
        $version->method('getResourceName')->willReturn('bar');
        $version->method('getLoggedAt')->willReturn('foobar');
        $args->method('getObject')->willReturn($version);
        $this->em->method('getUnitOfWork')->willReturn($uow);
        $this->em->method('find')->with('bar', 'foo')->willReturn($object);
        $uow->expects($this->once())->method('computeChangeSet')->with($metadata, $object);
        $object->expects($this->once())->method('setUpdated')->with('foobar');
        $this->sut->prePersist($args);
    }

    public function test_it_applies_on_timestampable_versioned_object_with_a_document_manager(): void
    {
        $args = $this->createMock(LifecycleEventArgs::class);
        $uow = $this->createMock(UnitOfWork::class);
        $version = $this->createMock(Version::class);
        $object = $this->createMock(TimestampableInterface::class);
        $metadata = $this->createMock(ClassMetadata::class);

        $this->em->method('getClassMetadata')->with('bar')->willReturn($metadata);
        $metadata->method('getReflectionClass')->willReturn(new \ReflectionClass(TimestampableInterface::class));
        $version->method('getResourceId')->willReturn('foo');
        $version->method('getResourceName')->willReturn('bar');
        $version->method('getLoggedAt')->willReturn('foobar');
        $args->method('getObject')->willReturn($version);
        $this->em->method('getUnitOfWork')->willReturn($uow);
        $this->em->method('find')->with('bar', 'foo')->willReturn($object);
        $uow->expects($this->once())->method('computeChangeSet')->with($metadata, $object);
        $object->expects($this->once())->method('setUpdated')->with('foobar');
        $this->sut->prePersist($args);
    }
}
