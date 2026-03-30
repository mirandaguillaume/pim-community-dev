<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\AttributeOption;

use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\AttributeOption\LocalizableSubscriber;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;

class LocalizableSubscriberTest extends TestCase
{
    private LocalizableSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new LocalizableSubscriber();
    }

}
