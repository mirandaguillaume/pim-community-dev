<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\FamilyVariant;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\FamilyVariant\AddUniqueAttributes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class AddUniqueAttributesTest extends TestCase
{
    private AddUniqueAttributes $sut;

    protected function setUp(): void
    {
        $this->sut = new AddUniqueAttributes();
    }

}
