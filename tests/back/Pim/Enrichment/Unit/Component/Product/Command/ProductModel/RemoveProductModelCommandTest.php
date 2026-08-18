<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Command\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelCommand;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveProductModelCommandTest extends TestCase
{
    public function test_it_returns_the_product_model_code(): void
    {
        $command = new RemoveProductModelCommand('a_product_model');

        $this->assertSame('a_product_model', $command->productModelCode());
    }
}
