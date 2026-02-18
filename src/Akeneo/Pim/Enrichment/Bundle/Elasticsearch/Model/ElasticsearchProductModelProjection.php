<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class ElasticsearchProductModelProjection
{
    private const INDEX_DATE_FORMAT = 'c';

    /**
     * @param string[] $familyLabels
     * @param string[] $categoryCodes
     * @param string[] $ancestorCategoryCodes
     * @param string[] $ancestorAttributeCodes
     * @param string[] $attributesForThisLevel
     */
    public function __construct(private int $id, private string $code, private \DateTimeImmutable $createdDate, private \DateTimeImmutable $updatedDate, private \DateTimeImmutable $entityUpdatedDate, private string $familyCode, private array $familyLabels, private string $familyVariantCode, private array $categoryCodes, private array $ancestorCategoryCodes, private ?string $parentCode, private array $values, private array $allComplete, private array $allIncomplete, private ?int $parentId, private array $labels, private array $ancestorAttributeCodes, private array $attributesForThisLevel, private array $additionalData = [])
    {
    }

    public function addAdditionalData(array $additionalData): ElasticsearchProductModelProjection
    {
        $additionalData = array_merge($this->additionalData, $additionalData);

        return new self(
            $this->id,
            $this->code,
            $this->createdDate,
            $this->updatedDate,
            $this->entityUpdatedDate,
            $this->familyCode,
            $this->familyLabels,
            $this->familyVariantCode,
            $this->categoryCodes,
            $this->ancestorCategoryCodes,
            $this->parentCode,
            $this->values,
            $this->allComplete,
            $this->allIncomplete,
            $this->parentId,
            $this->labels,
            $this->ancestorAttributeCodes,
            $this->attributesForThisLevel,
            $additionalData
        );
    }

    public function toArray(): array
    {
        $data = [
            'id' => 'product_model_' . (string) $this->id,
            'identifier' => $this->code,
            'created' => $this->createdDate->format(self::INDEX_DATE_FORMAT),
            'updated' => $this->updatedDate->format(self::INDEX_DATE_FORMAT),
            'entity_updated' => $this->entityUpdatedDate->format(self::INDEX_DATE_FORMAT),
            'family' => [
                'code' => $this->familyCode,
                'labels' => $this->familyLabels,
            ],
            'family_variant' => $this->familyVariantCode,
            'categories' => $this->categoryCodes,
            'categories_of_ancestors' => $this->ancestorCategoryCodes,
            'parent' => $this->parentCode,
            'values' => $this->values,
            'all_complete' => $this->allComplete,
            'all_incomplete' => $this->allIncomplete,
            'ancestors' => [
                'ids' => null !== $this->parentId ? ['product_model_' . (string) $this->parentId] : [],
                'codes' => null !== $this->parentCode ? [$this->parentCode] : [],
                'labels' => null !== $this->parentId ? $this->labels : [],
            ],
            'label' => $this->labels,
            'document_type' => ProductModelInterface::class,
            'attributes_of_ancestors' => $this->ancestorAttributeCodes,
            'attributes_for_this_level' => $this->attributesForThisLevel,
        ];

        return array_merge($data, $this->additionalData);
    }
}
