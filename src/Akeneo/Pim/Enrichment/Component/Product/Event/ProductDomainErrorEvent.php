<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Event;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class ProductDomainErrorEvent
{
    public function __construct(private DomainErrorInterface $error, private ?\Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface $product) {}

    public function getError(): DomainErrorInterface
    {
        return $this->error;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }
}
