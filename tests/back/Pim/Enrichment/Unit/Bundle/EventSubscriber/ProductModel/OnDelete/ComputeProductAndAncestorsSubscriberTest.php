<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\ProductModel\OnDelete;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnDelete\ComputeProductAndAncestorsSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeProductAndAncestorsSubscriberTest extends TestCase
{
    private ProductModelDescendantsAndAncestorsIndexer|MockObject $productModelDescendantsAndAncestorsIndexer;
    private Client|MockObject $esClient;
    private ComputeProductAndAncestorsSubscriber $sut;

    protected function setUp(): void
    {
        $this->productModelDescendantsAndAncestorsIndexer = $this->createMock(ProductModelDescendantsAndAncestorsIndexer::class);
        $this->esClient = $this->createMock(Client::class);
        $this->sut = new ComputeProductAndAncestorsSubscriber($this->productModelDescendantsAndAncestorsIndexer, $this->esClient);
    }

}
