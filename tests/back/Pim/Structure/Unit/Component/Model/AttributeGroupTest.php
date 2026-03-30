<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupTranslation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupTest extends TestCase
{
    private AttributeGroup $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeGroup();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AttributeGroup::class, $this->sut);
    }

    public function test_it_gets_a_translation_even_if_the_locale_case_is_wrong(): void
    {
        $translationEn = $this->createMock(AttributeGroupTranslation::class);

        $translationEn->method('getLocale')->willReturn('EN_US');
        $this->sut->addTranslation($translationEn);
        $this->assertSame($translationEn, $this->sut->getTranslation('en_US'));
    }
}
