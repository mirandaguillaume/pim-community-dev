<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Query\PublicApi\Family\Cache;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Family\Cache\LRUCachedGetRequiredAttributesMasks;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;
use PHPUnit\Framework\TestCase;

class LRUCachedGetRequiredAttributesMasksTest extends TestCase
{
    private LRUCachedGetRequiredAttributesMasks $sut;

    protected function setUp(): void
    {
        $this->sut = new LRUCachedGetRequiredAttributesMasks();
    }

}
