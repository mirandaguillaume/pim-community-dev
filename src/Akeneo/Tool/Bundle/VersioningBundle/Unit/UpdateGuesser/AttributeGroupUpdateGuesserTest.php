<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\AttributeGroupUpdateGuesser;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeGroupUpdateGuesserTest extends TestCase
{
    private EntityManager|MockObject $em;
    private UnitOfWork|MockObject $uow;
    private AttributeGroupUpdateGuesser $sut;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManager::class);
        $this->uow = $this->createMock(UnitOfWork::class);
        $this->sut = new AttributeGroupUpdateGuesser();
        $this->em->method('getUnitOfWork')->willReturn($this->uow);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AttributeGroupUpdateGuesser::class, $this->sut);
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

    public function test_it_returns_no_pending_updates_if_not_given_an_attribute(): void
    {
        $this->assertSame([], $this->sut->guessUpdates($this->em, new \stdClass(), UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
    }
}
