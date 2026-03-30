<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\AttributeOptionUpdateGuesser;

class AttributeOptionUpdateGuesserTest extends TestCase
{
    private EntityManager|MockObject $em;
    private AttributeInterface|MockObject $attribute;
    private AttributeOptionInterface|MockObject $option;
    private AttributeOptionValueInterface|MockObject $optionValue;
    private AttributeOptionUpdateGuesser $sut;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManager::class);
        $this->attribute = $this->createMock(AttributeInterface::class);
        $this->option = $this->createMock(AttributeOptionInterface::class);
        $this->optionValue = $this->createMock(AttributeOptionValueInterface::class);
        $this->sut = new AttributeOptionUpdateGuesser();
        $this->option->method('getAttribute')->willReturn($this->attribute);
        $this->optionValue->method('getOption')->willReturn($this->option);
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

    public function test_it_marks_attributes_as_updated_when_an_attribute_option_is_removed_or_updated(): void
    {
        $this->assertSame([$this->attribute], $this->sut->guessUpdates($this->em, $this->option, UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
        $this->assertSame([$this->attribute], $this->sut->guessUpdates($this->em, $this->option, UpdateGuesserInterface::ACTION_DELETE));
    }

    public function test_it_marks_attribute_options_as_updated_when_an_attribute_option_value_is_updated(): void
    {
        $this->assertSame([$this->option], $this->sut->guessUpdates($this->em, $this->optionValue, UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
        $this->assertSame([], $this->sut->guessUpdates($this->em, $this->optionValue, UpdateGuesserInterface::ACTION_DELETE));
    }
}
