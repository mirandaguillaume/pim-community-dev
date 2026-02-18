<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Cache;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class LruCachedGetExistingAttributeOptionsWithValues implements GetExistingAttributeOptionsWithValues
{
    private \Akeneo\Tool\Component\StorageUtils\Cache\LRUCache $cache;

    public function __construct(private GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues)
    {
        $this->cache = new LRUCache(10000);
    }

    /**
     * {@inheritDoc}
     */
    public function fromAttributeCodeAndOptionCodes(array $optionKeys): array
    {
        if (empty($optionKeys)) {
            return [];
        }

        return $this->cache->getForKeys(
            $optionKeys,
            \Closure::fromCallable($this->getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(...))
        );
    }
}
