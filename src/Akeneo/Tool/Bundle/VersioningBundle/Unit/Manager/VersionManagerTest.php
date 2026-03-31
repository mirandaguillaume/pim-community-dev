<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\Manager;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Builder\VersionBuilder;
use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvent;
use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvents;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class VersionManagerTest extends TestCase
{
    private VersionBuilder|MockObject $builder;
    private ObjectManager|MockObject $om;
    private VersionRepositoryInterface|MockObject $versionRepository;
    private VersionContext|MockObject $versionContext;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private VersionManager $sut;

    protected function setUp(): void
    {
        $this->builder = $this->createMock(VersionBuilder::class);
        $this->om = $this->createMock(ObjectManager::class);
        $this->versionRepository = $this->createMock(VersionRepositoryInterface::class);
        $this->versionContext = $this->createMock(VersionContext::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->sut = new VersionManager($this->om, $this->builder, $this->versionContext, $this->eventDispatcher);
        $this->eventDispatcher->method('dispatch')->with($this->isInstanceOf(BuildVersionEvent::class), BuildVersionEvents::PRE_BUILD)->willReturn(new BuildVersionEvent());
        $this->om->method('getRepository')->with($this->anything())->willReturn($this->versionRepository);
        $this->versionRepository->method('findBy')->willReturn([]);
        $this->versionRepository->method('getNewestLogEntry')->willReturn(null);
    }

    public function test_it_is_aware_of_the_versioning_mode(): void
    {
        $this->assertSame(true, $this->sut->isRealTimeVersioning());
        $this->sut->setRealTimeVersioning(false);
        $this->assertSame(false, $this->sut->isRealTimeVersioning());
    }

    public function test_it_uses_version_builder_to_build_versions(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $product->method('getUuid')->willReturn(Uuid::fromString('dc9ac794-fdfb-49e6-8a24-f01e0f68907d'));
        $this->sut->setUsername('julia');
        $this->sut->buildVersion($product);
        $this->eventDispatcher->method('dispatch')->with($this->isInstanceOf(BuildVersionEvent::class), BuildVersionEvents::PRE_BUILD)->willReturn('julia');
        $this->builder->method('buildVersion')->with($product, 'julia', null, null);
    }

    public function test_it_builds_versions_for_versionable_entities(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $product->method('getUuid')->willReturn(Uuid::fromString('dc9ac794-fdfb-49e6-8a24-f01e0f68907d'));
        $this->builder->method('buildVersion')->willReturn(new Version('foo', 1, null, 'bar'));
        $versions = $this->buildVersion($product);
        $versions->shouldHaveCount(1);
        $versions[0]->shouldBeAnInstanceOf(Version::class);
    }

    public function test_it_creates_pending_versions_when_real_time_versioning_is_disabled(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $this->sut->setRealTimeVersioning(false);
        $this->builder->method('createPendingVersion')->willReturn(new Version('foo', 1, null, 'bar'));
        $versions = $this->buildVersion($product);
        $versions->shouldHaveCount(1);
        $version = $versions[0];
        $version->shouldBeAnInstanceOf(Version::class);
        $version->isPending()->shouldReturn(true);
    }

    public function test_it_builds_pending_versions_and_last_version_when_versioning_an_entity(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $product->method('getUuid')->willReturn(Uuid::fromString('dc9ac794-fdfb-49e6-8a24-f01e0f68907d'));
        $pending1 = new Version('Product', null, Uuid::fromString('114c9108-444d-408a-ab43-195068166d2c'), 'julia');
        $pending1->setChangeset(['foo' => 'bar']);
        $pending2 = new Version('Product', null, Uuid::fromString('114c9108-444d-408a-ab43-195068166d2c'), 'julia');
        $pending2->setChangeset(['foo' => 'fubar']);
        $this->versionRepository->method('findBy')->willReturn([$pending1, $pending2]);
        $this->builder->expects($this->once())->method('buildPendingVersion')->with($pending1, null)->willReturn($pending1);
        $this->builder->expects($this->once())->method('buildPendingVersion')->with($pending2, $pending1)->willReturn($pending2);
        $this->builder->expects($this->once())->method('buildVersion')->willReturn(new Version('Product', null, Uuid::fromString('114c9108-444d-408a-ab43-195068166d2c'), 'julia'));
        $this->om->expects($this->once())->method('detach')->with($pending2);
        $versions = $this->buildVersion($product);
        $versions->shouldHaveCount(3);
    }

    public function test_it_builds_pending_versions_for_a_given_entity(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $product->method('getUuid')->willReturn(Uuid::fromString('dc9ac794-fdfb-49e6-8a24-f01e0f68907d'));
        $pending1 = new Version('Product', 1, null, 'julia');
        $pending1->setChangeset(['foo' => 'bar']);
        $pending2 = new Version('Product', 1, null, 'julia');
        $pending2->setChangeset(['foo' => 'fubar']);
        $this->versionRepository->method('findBy')->willReturn([$pending1, $pending2]);
        $this->builder->expects($this->once())->method('buildPendingVersion')->with($pending1, null)->willReturn($pending1);
        $this->builder->expects($this->once())->method('buildPendingVersion')->with($pending2, $pending1)->willReturn($pending2);
        $versions = $this->buildPendingVersions($product);
        $versions->shouldHaveCount(2);
    }
}
