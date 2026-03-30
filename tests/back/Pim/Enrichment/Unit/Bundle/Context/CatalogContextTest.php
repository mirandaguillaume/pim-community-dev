<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Context;

use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use PHPUnit\Framework\TestCase;

class CatalogContextTest extends TestCase
{
    private CatalogContext $sut;

    protected function setUp(): void
    {
        $this->sut = new CatalogContext();
    }

}
