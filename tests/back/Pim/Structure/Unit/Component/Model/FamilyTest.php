<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslation;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FamilyTest extends TestCase
{
    private Family $sut;

    protected function setUp(): void
    {
        $this->sut = new Family();
    }

    public function test_it_gets_a_translation_even_if_the_locale_case_is_wrong(): void
    {
        $translationEn = $this->createMock(FamilyTranslation::class);

        $translationEn->method('getLocale')->willReturn('EN_US');
        $this->sut->addTranslation($translationEn);
        $this->assertSame($translationEn, $this->sut->getTranslation('en_US'));
    }
}
