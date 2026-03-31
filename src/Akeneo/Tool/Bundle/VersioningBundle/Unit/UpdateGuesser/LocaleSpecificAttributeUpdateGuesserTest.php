<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\LocaleSpecificAttributeUpdateGuesser;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\OneToManyAssociationMapping;
use Doctrine\ORM\PersistentCollection;
use PHPUnit\Framework\TestCase;

class LocaleSpecificAttributeUpdateGuesserTest extends TestCase
{
    private LocaleSpecificAttributeUpdateGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new LocaleSpecificAttributeUpdateGuesser();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(LocaleSpecificAttributeUpdateGuesser::class, $this->sut);
    }

    public function test_it_is_an_update_guesser(): void
    {
        $this->assertInstanceOf(UpdateGuesserInterface::class, $this->sut);
    }

    public function test_it_supports_update_action(): void
    {
        $this->assertSame(true, $this->sut->supportAction(UpdateGuesserInterface::ACTION_UPDATE_COLLECTION));
        $this->assertSame(false, $this->sut->supportAction('foo'));
    }

    public function test_it_guesses_attribute_locale_updates(): void
    {
        $attribute = new Attribute();
        $em = $this->createMock(EntityManager::class);
        $collection = new PersistentCollection($em, new ClassMetadata('Pim\Bundle\CatalogBundle\Entity\Attribute'), new ArrayCollection());
        $mapping = OneToManyAssociationMapping::fromMappingArray([
                    'fieldName' => 'availableLocales',
                    'targetEntity' => 'Foo',
                    'sourceEntity' => 'Bar',
                    'mappedBy' => 'foo',
                ]);
        $collection->setOwner($attribute, $mapping);
        $this->assertSame([$attribute], $this->sut->guessUpdates($em, $collection, UpdateGuesserInterface::ACTION_UPDATE_COLLECTION));
    }
}
