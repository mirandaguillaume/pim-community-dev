<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\StorageUtilsBundle\EventSubscriber;

use Akeneo\Tool\Bundle\StorageUtilsBundle\EventSubscriber\ResolveTargetRepositorySubscriber;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResolveTargetRepositorySubscriberTest extends TestCase
{
    private ResolveTargetRepositorySubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new ResolveTargetRepositorySubscriber();
    }

    public function test_it_adds_new_targeted_repository(): void
    {
        $args = $this->createMock(LoadClassMetadataEventArgs::class);
        $cm = new ClassMetadata('foo');

        $this->sut->addResolveTargetRepository('foo', 'barRepository');
        $args->method('getClassMetadata')->willReturn($cm);
        $this->sut->loadClassMetadata($args);
        $this->assertSame('barRepository', $cm->customRepositoryClassName);
    }
}
