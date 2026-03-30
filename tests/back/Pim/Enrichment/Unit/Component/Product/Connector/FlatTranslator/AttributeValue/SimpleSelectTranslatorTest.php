<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\SimpleSelectTranslator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleSelectTranslatorTest extends TestCase
{
    private SimpleSelectTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new SimpleSelectTranslator();
    }

    private function optionKey(string $attributeCode, string $optionCode): string
    {
            return sprintf('%s.%s', $attributeCode, $optionCode);
        }
}
