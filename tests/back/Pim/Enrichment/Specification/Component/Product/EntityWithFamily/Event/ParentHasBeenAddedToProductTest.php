<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\EntityWithFamily\Event;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\Event\ParentHasBeenAddedToProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class ParentHasBeenAddedToProductTest extends TestCase
{
    private ParentHasBeenAddedToProduct $sut;

    protected function setUp(): void
    {
        $this->sut = new ParentHasBeenAddedToProduct();
    }

}
