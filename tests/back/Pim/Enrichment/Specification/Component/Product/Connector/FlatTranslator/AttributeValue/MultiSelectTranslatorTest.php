<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\MultiSelectTranslator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultiSelectTranslatorTest extends TestCase
{
    private MultiSelectTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new MultiSelectTranslator();
    }

    public function test_it_is_case_insensitive_to_find_options_labels(): void
    {
        $getExistingAttributeOptionsWithValues = $this->createMock(GetExistingAttributeOptionsWithValues::class);

        $locale = 'fr_FR';
        $getExistingAttributeOptionsWithValues->method('fromAttributeCodeAndOptionCodes')->with([
                        $this->optionKey('color', 'red'),
                        $this->optionKey('color', 'yellow'),
                        $this->optionKey('color', 'purple')
                    ])->willReturn([
                        $this->optionKey('color', 'red')    => [$locale => 'rouge'],
                        $this->optionKey('color', 'yellow') => [$locale => 'jaune'],
                        $this->optionKey('color', 'purple') => [$locale => 'purple']
                    ]);
        $this->assertSame(['rouge,jaune', 'purple', ''], $this->sut->translate('color', [], ['ReD,YeLLoW', 'PURPle', ''], $locale));
    }

    private function optionKey(string $attributeCode, string $optionCode): string
    {
            return sprintf('%s.%s', $attributeCode, $optionCode);
        }
}
