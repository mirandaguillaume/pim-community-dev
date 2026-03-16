<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetAncestorAndDescendantProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\Event\ParentHasBeenRemovedFromVariantProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * This subscriber reindexes the former ancestor product models of a variant product converted to a simple product
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: ParentHasBeenRemovedFromVariantProduct::class, method: 'store')]
#[AsEventListener(event: StorageEvents::POST_SAVE, method: 'reIndex')]
#[AsEventListener(event: StorageEvents::POST_SAVE_ALL, method: 'reIndexAll')]
class ReindexFormerAncestorsSubscriber
{
    private array $formerParentCodes = [];

    public function __construct(private readonly GetAncestorAndDescendantProductModelCodes $getAncestorProductModelCodes, private readonly ProductModelIndexerInterface $productModelIndexer)
    {
    }

    public function store(ParentHasBeenRemovedFromVariantProduct $event): void
    {
        $this->formerParentCodes[$event->getProduct()->getUuid()->toString()] = $event->getFormerParentProductModelCode();
    }

    public function reIndex(GenericEvent $event): void
    {
        $product = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;
        if (false === $unitary || empty($this->formerParentCodes) || !$product instanceof ProductInterface) {
            return;
        }

        $formerParentCode = $this->formerParentCodes[$product->getUuid()->toString()] ?? null;
        if (null !== $formerParentCode) {
            unset($this->formerParentCodes[$product->getUuid()->toString()]);
            $this->reindexFromProductModelCodes([$formerParentCode]);
        }
    }

    public function reIndexAll(GenericEvent $event): void
    {
        $products = $event->getSubject();
        if (empty($this->formerParentCodes) || !reset($products) instanceof ProductInterface) {
            return;
        }

        $formerParentCodes = [];
        foreach ($products as $product) {
            $formerParentCode = $this->formerParentCodes[$product->getUuid()->toString()] ?? null;
            if (null !== $formerParentCode) {
                unset($this->formerParentCodes[$product->getUuid()->toString()]);
                $formerParentCodes[] = $formerParentCode;
            }
        }

        $this->reindexFromProductModelCodes(array_values(array_unique($formerParentCodes)));
    }

    private function reindexFromProductModelCodes(array $productModelCodes): void
    {
        if ([] === $productModelCodes) {
            return;
        }
        $rootProductModelCodes = $this->getAncestorProductModelCodes->fromProductModelCodes($productModelCodes);

        $this->productModelIndexer->indexFromProductModelCodes(array_merge($productModelCodes, $rootProductModelCodes));
    }
}
