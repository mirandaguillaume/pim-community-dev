<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Grid\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;
use PHPUnit\Framework\TestCase;

class AdditionalPropertiesTest extends TestCase
{
    private AdditionalProperties $sut;

    protected function setUp(): void
    {
        $this->sut = new AdditionalProperties();
    }

}
