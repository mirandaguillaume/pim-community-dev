<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave\ApiAggregatorForProductPostSaveEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiAggregatorForProductPostSaveEventSubscriberTest extends TestCase
{
    private ApiAggregatorForProductPostSaveEventSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new ApiAggregatorForProductPostSaveEventSubscriber();
    }

}
