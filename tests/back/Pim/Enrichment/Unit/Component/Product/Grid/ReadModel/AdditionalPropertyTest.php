<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Grid\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;
use PHPUnit\Framework\TestCase;

class AdditionalPropertyTest extends TestCase
{
    private AdditionalProperty $sut;

    protected function setUp(): void
    {
        $this->sut = new AdditionalProperty();
    }

}
