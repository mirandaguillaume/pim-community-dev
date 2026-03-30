<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Command;

use Akeneo\Pim\Enrichment\Component\Product\Command\GroupProductsCommand;
use Akeneo\Pim\Enrichment\Component\Product\Command\GroupProductsHandler;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindProductUuidsInGroup;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class GroupProductsHandlerTest extends TestCase
{
    private GroupProductsHandler $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupProductsHandler();
    }

}
