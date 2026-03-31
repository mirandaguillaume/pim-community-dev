<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization\Factory;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupTranslation;
use Akeneo\Tool\Component\Localization\Factory\TranslationFactory;
use PHPUnit\Framework\TestCase;

class TranslationFactoryTest extends TestCase
{
    private TranslationFactory $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_initializable(): void
    {
        $this->sut = new TranslationFactory(
            AttributeGroupTranslation::class,
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
        $this->assertTrue(is_a(TranslationFactory::class, TranslationFactory::class, true));
    }

    public function test_it_creates_a_translation(): void
    {
        $this->sut = new TranslationFactory(
            AttributeGroupTranslation::class,
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
        $translation = $this->sut->createTranslation('en_US');
        $this->assertInstanceOf(AttributeGroupTranslation::class, $translation);
        $this->assertSame('en_US', $translation->getLocale());
    }

    public function test_it_throws_an_exception_when_an_invalid_translation_class_is_provided(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TranslationFactory(
            LocaleInterface::class,
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
    }
}
