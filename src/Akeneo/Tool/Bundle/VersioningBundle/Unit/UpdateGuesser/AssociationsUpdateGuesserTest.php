<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\AssociationsUpdateGuesser;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AssociationsUpdateGuesserTest extends TestCase
{
    private AssociationsUpdateGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociationsUpdateGuesser(['stdClass']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AssociationsUpdateGuesser::class, $this->sut);
    }

    public function test_it_is_an_update_guesser(): void
    {
        $this->assertInstanceOf(UpdateGuesserInterface::class, $this->sut);
    }

    public function test_it_supports_update_action(): void
    {
        $this->assertSame(true, $this->sut->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
        $this->assertSame(false, $this->sut->supportAction('foo'));
    }

    public function test_it_guesses_associations_updates(): void
    {
        $association = $this->createMock(AssociationInterface::class);
        $owner = $this->createMock(EntityWithAssociationsInterface::class);
        $em = $this->createMock(EntityManager::class);

        $association->method('getOwner')->willReturn($owner);
        $this->assertSame([$owner], $this->sut->guessUpdates($em, $association, UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
    }

    public function test_it_returns_no_pending_updates_if_not_given_association_interface(): void
    {
        $em = $this->createMock(EntityManager::class);
        $locale = $this->createMock(LocaleInterface::class);

        $this->assertSame([], $this->sut->guessUpdates($em, $locale, UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
    }
}
