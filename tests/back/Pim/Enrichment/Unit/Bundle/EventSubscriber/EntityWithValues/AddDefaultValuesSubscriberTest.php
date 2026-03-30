<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\EntityWithValues;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithValues\AddDefaultValuesSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\CommonAttributeCollection;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;

class AddDefaultValuesSubscriberTest extends TestCase
{
    private AddDefaultValuesSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new AddDefaultValuesSubscriber();
    }

    private function createAttributeWithDefaultValue(
        string $code,
        bool $defaultValue,
        bool $isLocalizable = false,
        bool $isScopable = false,
        array $availableLocaleCodes = []
    ): Attribute
    {
            return new Attribute(
                $code,
                'pim_catalog_boolean',
                ['default_value' => $defaultValue],
                $isLocalizable,
                $isScopable,
                null,
                null,
                null,
                'bool',
                $availableLocaleCodes
            );
        }
}
