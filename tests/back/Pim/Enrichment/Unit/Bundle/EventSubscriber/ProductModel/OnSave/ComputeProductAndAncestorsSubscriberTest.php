<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave\ComputeProductAndAncestorsSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetDescendantVariantProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeProductAndAncestorsSubscriberTest extends TestCase
{
    private ComputeAndPersistProductCompletenesses|MockObject $computeAndPersistProductCompletenesses;
    private ProductModelDescendantsAndAncestorsIndexer|MockObject $productModelDescendantsAndAncestorsIndexer;
    private GetDescendantVariantProductUuids|MockObject $getDescendantVariantProductUuids;
    private ComputeProductAndAncestorsSubscriber $sut;

    protected function setUp(): void
    {
        $this->computeAndPersistProductCompletenesses = $this->createMock(ComputeAndPersistProductCompletenesses::class);
        $this->productModelDescendantsAndAncestorsIndexer = $this->createMock(ProductModelDescendantsAndAncestorsIndexer::class);
        $this->getDescendantVariantProductUuids = $this->createMock(GetDescendantVariantProductUuids::class);
        $this->sut = new ComputeProductAndAncestorsSubscriber($this->computeAndPersistProductCompletenesses,
            $this->productModelDescendantsAndAncestorsIndexer,
            $this->getDescendantVariantProductUuids);
    }

}
