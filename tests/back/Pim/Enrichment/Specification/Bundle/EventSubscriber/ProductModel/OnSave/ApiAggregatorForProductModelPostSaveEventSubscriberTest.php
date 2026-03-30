<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave\ApiAggregatorForProductModelPostSaveEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiAggregatorForProductModelPostSaveEventSubscriberTest extends TestCase
{
    private ApiAggregatorForProductModelPostSaveEventSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new ApiAggregatorForProductModelPostSaveEventSubscriber();
    }

}
