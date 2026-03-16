<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\ProductsEvaluated;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\BulkUpdateProductQualityScoresInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: ProductsEvaluated::class, method: 'bulkUpdateProductQualityScoresIndex')]
final readonly class BulkUpdateProductQualityScoresIndexSubscriber
{
    public function __construct(private BulkUpdateProductQualityScoresInterface $bulkUpdateProductQualityScores)
    {
    }

    public function bulkUpdateProductQualityScoresIndex(ProductsEvaluated $event): void
    {
        ($this->bulkUpdateProductQualityScores)($event->getProductIds());
    }
}
