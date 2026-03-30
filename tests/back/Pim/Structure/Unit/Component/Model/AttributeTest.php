<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeTranslation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    private Attribute $sut;

    protected function setUp(): void
    {
        $this->sut = new Attribute();
    }

    public function test_it_gets_a_translation_even_if_the_locale_case_is_wrong(): void
    {
        $translationEn = $this->createMock(AttributeTranslation::class);

        $translationEn->method('getLocale')->willReturn('EN_US');
        $this->sut->addTranslation($translationEn);
        $this->assertSame($translationEn, $this->sut->getTranslation('en_US'));
    }
}
