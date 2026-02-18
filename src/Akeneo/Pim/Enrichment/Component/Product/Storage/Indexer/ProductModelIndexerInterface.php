<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductModelIndexerInterface
{
    public function indexFromProductModelCode(string $productModelCode, array $options = []): void;

    /**
     * @param string[] $productModelCodes
     */
    public function indexFromProductModelCodes(array $productModelCodes, array $options = []): void;

    public function removeFromProductModelId(int $productModelId, array $options = []): void;

    /**
     * @param int[] $productModelIds
     */
    public function removeFromProductModelIds(array $productModelIds, array $options = []): void;
}
