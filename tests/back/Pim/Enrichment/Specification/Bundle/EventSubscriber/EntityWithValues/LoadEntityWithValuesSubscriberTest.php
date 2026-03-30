<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\EntityWithValues;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithValues\LoadEntityWithValuesSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;

class LoadEntityWithValuesSubscriberTest extends TestCase
{
    private LoadEntityWithValuesSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new LoadEntityWithValuesSubscriber();
    }

}
