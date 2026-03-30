<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\AttributeOption;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\AttributeOption\SetAttributeOptionSortOrderSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Attribute\GetAttributeOptionsMaxSortOrder;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;

class SetAttributeOptionSortOrderSubscriberTest extends TestCase
{
    private SetAttributeOptionSortOrderSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new SetAttributeOptionSortOrderSubscriber();
    }

}
