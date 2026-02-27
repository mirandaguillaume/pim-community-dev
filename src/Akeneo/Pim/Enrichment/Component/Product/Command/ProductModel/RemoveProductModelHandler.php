<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveProductModelHandler
{
    public function __construct(private readonly ProductModelRepositoryInterface $productModelRepository, private readonly RemoverInterface $productModelRemover) {}

    public function __invoke(RemoveProductModelCommand $command): void
    {
        $productModel = $this->productModelRepository->findOneByIdentifier($command->productModelCode());
        Assert::notNull($productModel);
        $this->productModelRemover->remove($productModel);
    }
}
