<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\ProductModelsEvaluated;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\BulkUpdateProductQualityScoresInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: ProductModelsEvaluated::class, method: 'bulkUpdateProductModelQualityScoresIndex')]
final readonly class BulkUpdateProductModelQualityScoresIndexSubscriber
{
    public function __construct(private BulkUpdateProductQualityScoresInterface $bulkUpdateProductQualityScores)
    {
    }

    public function bulkUpdateProductModelQualityScoresIndex(ProductModelsEvaluated $event): void
    {
        ($this->bulkUpdateProductQualityScores)($event->getProductModelIds());
    }
}
