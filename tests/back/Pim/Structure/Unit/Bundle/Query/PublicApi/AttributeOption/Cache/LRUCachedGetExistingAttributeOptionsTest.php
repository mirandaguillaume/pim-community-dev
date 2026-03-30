<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Query\PublicApi\AttributeOption\Cache;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Cache\LRUCachedGetExistingAttributeOptions;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;
use PHPUnit\Framework\TestCase;

class LRUCachedGetExistingAttributeOptionsTest extends TestCase
{
    private LRUCachedGetExistingAttributeOptions $sut;

    protected function setUp(): void
    {
        $this->sut = new LRUCachedGetExistingAttributeOptions();
    }

}
