<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\EventListener;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Bundle\EventListener\AttributeOptionRemovalSubscriber;
use Akeneo\Pim\Structure\Component\FamilyVariant\Query\FamilyVariantsByAttributeAxesInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;

class AttributeOptionRemovalSubscriberTest extends TestCase
{
    private AttributeOptionRemovalSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeOptionRemovalSubscriber();
    }

}
