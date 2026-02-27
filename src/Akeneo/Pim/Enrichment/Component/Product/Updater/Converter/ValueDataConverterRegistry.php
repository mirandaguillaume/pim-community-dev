<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class ValueDataConverterRegistry
{
    /**
     * @param \Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverter[] $converters
     */
    public function __construct(private iterable $converters)
    {
    }

    public function getDataConverter(
        AttributeInterface $sourceAttribute,
        AttributeInterface $targetAttribute
    ): ?ValueDataConverter {
        foreach ($this->converters as $converter) {
            if ($converter->supportsAttributes($sourceAttribute, $targetAttribute)) {
                return $converter;
            }
        }

        return null;
    }
}
