<?php

namespace spec\Akeneo\Tool\Component\Localization\Factory;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\Localization\Factory\TranslationFactory;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupTranslation;

class TranslationFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith(
            AttributeGroupTranslation::class,
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
        $this->shouldHaveType(TranslationFactory::class);
    }

    public function it_creates_a_translation()
    {
        $this->beConstructedWith(
            AttributeGroupTranslation::class,
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
        $this->createTranslation('en_US')
            ->shouldReturnAnInstanceOf(AttributeGroupTranslation::class);

        $this->createTranslation('en_US')
            ->getLocale()
            ->shouldReturn('en_US');
    }

    public function it_throws_an_exception_when_an_invalid_translation_class_is_provided()
    {
        $this->beConstructedWith(
            LocaleInterface::class,
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
        $this->shouldThrow('\InvalidArgumentException');
    }
}
