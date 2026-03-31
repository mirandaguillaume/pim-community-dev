<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\AssociationUpdateGuesser;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AssociationUpdateGuesserTest extends TestCase
{
    private AssociationUpdateGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociationUpdateGuesser();
    }

    public function test_it_is_an_update_guesser(): void
    {
        $this->assertInstanceOf(UpdateGuesserInterface::class, $this->sut);
    }

    public function test_it_supports_entity_updates_and_deletion(): void
    {
        $this->assertSame(true, $this->sut->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
        $this->assertSame(true, $this->sut->supportAction(UpdateGuesserInterface::ACTION_DELETE));
        $this->assertSame(false, $this->sut->supportAction(UpdateGuesserInterface::ACTION_UPDATE_COLLECTION));
        $this->assertSame(false, $this->sut->supportAction('foo'));
    }

    public function test_it_marks_products_as_updated_when_an_association_is_updated_or_removed(): void
    {
        $em = $this->createMock(EntityManager::class);
        $foo = $this->createMock(ProductInterface::class);
        $association = $this->createMock(AssociationInterface::class);

        $association->method('getOwner')->willReturn($foo);
        $this->assertSame([$foo], $this->sut->guessUpdates($em, $association, UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
        $this->assertSame([$foo], $this->sut->guessUpdates($em, $association, UpdateGuesserInterface::ACTION_DELETE));
    }
}
