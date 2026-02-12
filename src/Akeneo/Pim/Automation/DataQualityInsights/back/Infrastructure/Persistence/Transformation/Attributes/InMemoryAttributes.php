<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Attributes;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class InMemoryAttributes implements AttributesInterface
{
    private array $attributesCodesByIds;

    /**
     * @param array<string, int> $attributesIdsByCodes
     */
    public function __construct(private array $attributesIdsByCodes)
    {
        $this->attributesCodesByIds = array_flip($attributesIdsByCodes);
    }

    public function getCodesByIds(array $attributesIds): array
    {
        return array_flip(array_intersect($this->attributesIdsByCodes, $attributesIds));
    }

    public function getIdsByCodes(array $attributesCodes): array
    {
        return array_flip(array_intersect($this->attributesCodesByIds, $attributesCodes));
    }
}
