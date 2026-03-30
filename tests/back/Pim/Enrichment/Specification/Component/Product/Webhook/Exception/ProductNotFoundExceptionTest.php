<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Webhook\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductNotFoundException;
use Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNotFoundExceptionTest extends TestCase
{
    private ProductNotFoundException $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductNotFoundException();
    }

}
