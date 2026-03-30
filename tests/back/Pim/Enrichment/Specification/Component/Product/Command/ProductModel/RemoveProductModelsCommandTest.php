<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Command\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelCommand;
use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelsCommand;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use PHPUnit\Framework\TestCase;

class RemoveProductModelsCommandTest extends TestCase
{
    private RemoveProductModelsCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new RemoveProductModelsCommand();
    }

}
