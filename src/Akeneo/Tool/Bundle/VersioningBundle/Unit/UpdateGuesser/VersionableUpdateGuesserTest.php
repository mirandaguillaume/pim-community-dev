<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\VersionableUpdateGuesser;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VersionableUpdateGuesserTest extends TestCase
{
    private EntityManager|MockObject $em;
    private VersionableUpdateGuesser $sut;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManager::class);
        $this->sut = new VersionableUpdateGuesser(['stdClass']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(VersionableUpdateGuesser::class, $this->sut);
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

    public function test_it_guesses_versionable_entity_updates(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $object = new \stdClass();
        $this->assertSame([$attribute], $this->sut->guessUpdates($this->em, $attribute, UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
        $this->assertSame([$object], $this->sut->guessUpdates($this->em, $object, UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
    }

    public function test_it_returns_no_pending_updates_if_not_given_versionable_class(): void
    {
        $locale = $this->createMock(LocaleInterface::class);

        $this->assertSame([], $this->sut->guessUpdates($this->em, $locale, UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
    }
}
