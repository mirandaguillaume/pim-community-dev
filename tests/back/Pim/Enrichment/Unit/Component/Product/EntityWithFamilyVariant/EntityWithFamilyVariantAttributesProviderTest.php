<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\CommonAttributeCollection;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class EntityWithFamilyVariantAttributesProviderTest extends TestCase
{
    private EntityWithFamilyVariantAttributesProvider $sut;

    protected function setUp(): void
    {
        $this->sut = new EntityWithFamilyVariantAttributesProvider();
    }

}
