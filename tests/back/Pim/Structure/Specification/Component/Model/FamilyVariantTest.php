<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\CommonAttributeCollection;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantTranslation;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FamilyVariantTest extends TestCase
{
    private FamilyVariant $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyVariant();
    }

    public function test_it_gets_a_translation_even_if_the_locale_case_is_wrong(): void
    {
        $translationEn = $this->createMock(FamilyVariantTranslation::class);

        $translationEn->method('getLocale')->willReturn('EN_US');
        $this->sut->addTranslation($translationEn);
        $this->assertSame($translationEn, $this->sut->getTranslation('en_US'));
    }
}
