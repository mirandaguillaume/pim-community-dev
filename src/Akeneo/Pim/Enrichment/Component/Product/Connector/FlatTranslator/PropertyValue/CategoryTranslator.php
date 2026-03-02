<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;

class CategoryTranslator implements FlatPropertyValueTranslatorInterface
{
    public function __construct(private readonly GetCategoryTranslations $getCategoryTranslations)
    {
    }

    public function supports(string $columnName): bool
    {
        return 'categories' === $columnName;
    }

    public function translate(array $values, string $locale, string $scope): array
    {
        $result = [];
        $categoryCodesExtracted = $this->extractCategoryCodes($values);
        $categoryTranslations = $this->getCategoryTranslations->byCategoryCodesAndLocale($categoryCodesExtracted, $locale);

        foreach ($values as $valueIndex => $value) {
            if (empty($value)) {
                $result[$valueIndex] = $value;
                continue;
            }

            $categoryCodes = explode(',', (string) $value);
            $categoriesLabelized = [];

            foreach ($categoryCodes as $categoryCode) {
                $categoriesLabelized[] = $categoryTranslations[$categoryCode]
                    ?? sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $categoryCode);
            }

            $result[$valueIndex] = implode(',', $categoriesLabelized);
        }

        return $result;
    }

    private function extractCategoryCodes(array $values): array
    {
        $categoryCodes = [];
        foreach ($values as $value) {
            if (empty($value)) {
                continue;
            }
            $categoryCodes = array_merge($categoryCodes, explode(',', (string) $value));
        }

        return array_unique($categoryCodes);
    }
}
