<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Command\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelCommand;
use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelHandler;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use PHPUnit\Framework\TestCase;

class RemoveProductModelHandlerTest extends TestCase
{
    private RemoveProductModelHandler $sut;

    protected function setUp(): void
    {
        $this->sut = new RemoveProductModelHandler();
    }

}
