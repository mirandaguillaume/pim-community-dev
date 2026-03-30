<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\EventSubscriber;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Pim\Structure\Bundle\EventSubscriber\AddUniqueAttributesToVariantProductAttributeSetSubscriber;
use Akeneo\Pim\Structure\Component\FamilyVariant\AddUniqueAttributes;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;

class AddUniqueAttributesToVariantProductAttributeSetSubscriberTest extends TestCase
{
    private AddUniqueAttributesToVariantProductAttributeSetSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new AddUniqueAttributesToVariantProductAttributeSetSubscriber();
    }

}
