<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\Family;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family\FamilyAttributeAsLabelChangedSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\FindAttributeCodeAsLabelForFamilyInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\TestCase;
use PhpParser\Node\Arg;
use Symfony\Component\EventDispatcher\GenericEvent;

class FamilyAttributeAsLabelChangedSubscriberTest extends TestCase
{
    private FamilyAttributeAsLabelChangedSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyAttributeAsLabelChangedSubscriber();
    }

}
