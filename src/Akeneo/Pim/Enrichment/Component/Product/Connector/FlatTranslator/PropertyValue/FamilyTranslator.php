<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilyTranslations;

class FamilyTranslator implements FlatPropertyValueTranslatorInterface
{
    public function __construct(private readonly GetFamilyTranslations $getFamilyTranslations) {}

    public function supports(string $columnName): bool
    {
        return 'family' === $columnName;
    }

    public function translate(array $values, string $locale, string $scope): array
    {
        $familyTranslations = $this->getFamilyTranslations->byFamilyCodesAndLocale($values, $locale);

        $familyLabelized = [];
        foreach ($values as $valueIndex => $value) {
            $familyLabelized[$valueIndex] = $familyTranslations[$value]
                ?? sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $value);
        }

        return $familyLabelized;
    }
}
