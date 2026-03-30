<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Bundle\EventSubscriber\RemoveAttributesFromFamilyVariantsOnFamilyUpdateSubscriber;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveAttributesFromFamilyVariantsOnFamilyUpdateSubscriberTest extends TestCase
{
    private RemoveAttributesFromFamilyVariantsOnFamilyUpdateSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new RemoveAttributesFromFamilyVariantsOnFamilyUpdateSubscriber();
    }

}
