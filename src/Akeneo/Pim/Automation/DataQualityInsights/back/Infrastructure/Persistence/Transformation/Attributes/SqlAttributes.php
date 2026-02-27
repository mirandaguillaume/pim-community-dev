<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Attributes;

use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlAttributes implements AttributesInterface
{
    private const LRU_CACHE_SIZE = 1000;

    private readonly LRUCache $attributeIdsByCodes;

    private readonly LRUCache $attributeCodesByIds;

    public function __construct(private readonly Connection $dbConnection)
    {
        $this->attributeIdsByCodes = new LRUCache(self::LRU_CACHE_SIZE);
        $this->attributeCodesByIds = new LRUCache(self::LRU_CACHE_SIZE);
    }

    public function getCodesByIds(array $attributesIds): array
    {
        // Because LRUCache can only be used with string keys
        $attributesIds = array_map(fn($attributeId) => $this->castAttributeIdIntToString($attributeId), $attributesIds);

        $rawAttributesCodes = $this->attributeCodesByIds->getForKeys($attributesIds, function ($attributesIds) {
            $attributesIds = array_map(fn($attributeId) => $this->castAttributeIdStringToInt($attributeId), $attributesIds);
            $attributesCodes = $this->dbConnection->executeQuery(
                "SELECT JSON_OBJECTAGG(CONCAT('a_', id), code) FROM pim_catalog_attribute WHERE id IN (:ids);",
                ['ids' => $attributesIds],
                ['ids' => ArrayParameterType::INTEGER]
            )->fetchOne();

            return !$attributesCodes ? [] : json_decode($attributesCodes, true, 512, JSON_THROW_ON_ERROR);
        });

        $attributesCodes = [];
        foreach ($rawAttributesCodes as $attributeId => $attributeCode) {
            $attributesCodes[$this->castAttributeIdStringToInt($attributeId)] = $attributeCode;
        }

        return $attributesCodes;
    }

    public function getIdsByCodes(array $attributesCodes): array
    {
        $attributesCodes = array_map(fn($attributeCode) => strval($attributeCode), $attributesCodes);

        return $this->attributeIdsByCodes->getForKeys($attributesCodes, function ($attributesCodes) {
            $attributesIds = $this->dbConnection->executeQuery(
                'SELECT JSON_OBJECTAGG(code, id) FROM pim_catalog_attribute WHERE code IN (:codes);',
                ['codes' => $attributesCodes],
                ['codes' => ArrayParameterType::STRING]
            )->fetchOne();

            return !$attributesIds ? [] : json_decode($attributesIds, true, 512, JSON_THROW_ON_ERROR);
        });
    }

    private function castAttributeIdIntToString(int $attributeId): string
    {
        return sprintf('a_%d', $attributeId);
    }

    private function castAttributeIdStringToInt(string $attributeId): int
    {
        return intval(substr($attributeId, 2));
    }
}
