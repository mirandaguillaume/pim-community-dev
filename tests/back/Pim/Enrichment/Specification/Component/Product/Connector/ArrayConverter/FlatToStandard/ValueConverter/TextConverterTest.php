<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\TextConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Connector\Exception\BusinessArrayConversionException;
use PHPUnit\Framework\TestCase;

class TextConverterTest extends TestCase
{
    private TextConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new TextConverter();
    }

    private function initFieldNameInfo(AttributeInterface $attribute): array
    {
            $attribute->getCode()->willReturn(self::TEST_ATTRIBUTE_CODE);
            $attribute->getType()->willReturn('attribute_type');
    
            $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];
            return $fieldNameInfo;
        }

    private function initResult(string $str): array
    {
            return [self::TEST_ATTRIBUTE_CODE => [[
                'locale' => 'en_US',
                'scope' => 'mobile',
                'data' => $str,
            ]]];
        }
}
