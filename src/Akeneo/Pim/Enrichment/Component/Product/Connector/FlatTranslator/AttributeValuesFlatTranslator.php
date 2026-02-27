<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class AttributeValuesFlatTranslator
{
    public function __construct(private readonly AttributeValueRegistry $attributeValueRegistry, private readonly AttributeColumnInfoExtractor $attributeColumnInfoExtractor, private readonly AttributeColumnsResolver $attributeColumnsResolver) {}

    public function supports(string $columnName): bool
    {
        return in_array($columnName, $this->attributeColumnsResolver->resolveAttributeColumns());
    }

    public function translate(string $columnName, array $values, string $locale): array
    {
        $attribute = $this->getAttributeFromColumnName($columnName);
        $attributeType = $attribute->getType();
        $attributeCode = $attribute->getCode();
        $attributeProperties = $attribute->getProperties();
        $measurementFamilyCode = $attribute->getMetricFamily();

        if (null !== $measurementFamilyCode) {
            $attributeProperties['measurement_family_code'] = $measurementFamilyCode;
        }

        $translator = $this->attributeValueRegistry->getTranslator($attributeType, $columnName);

        return null === $translator ? $values : $translator->translate($attributeCode, $attributeProperties, $values, $locale);
    }

    private function getAttributeFromColumnName(string $columnName): AttributeInterface
    {
        $columnInformations = $this->attributeColumnInfoExtractor->extractColumnInfo($columnName);

        return $columnInformations['attribute'];
    }
}
