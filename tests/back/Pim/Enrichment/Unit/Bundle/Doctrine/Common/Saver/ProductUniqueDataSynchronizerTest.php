<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ProductUniqueDataFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class ProductUniqueDataSynchronizerTest extends TestCase
{
    private ProductUniqueDataSynchronizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductUniqueDataSynchronizer();
    }

}
