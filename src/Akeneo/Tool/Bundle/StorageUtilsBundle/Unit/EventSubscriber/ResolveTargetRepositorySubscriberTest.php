<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\StorageUtilsBundle\EventSubscriber;

use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\StorageUtilsBundle\EventSubscriber\ResolveTargetRepositorySubscriber;

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
        $cm = $this->createMock(ClassMetadata::class);

        $this->sut->addResolveTargetRepository('foo', 'barRepository');
        $args->method('getClassMetadata')->willReturn($cm);
        $cm->method('getName')->willReturn('foo');
        $this->sut->loadClassMetadata($args);
    }
}
