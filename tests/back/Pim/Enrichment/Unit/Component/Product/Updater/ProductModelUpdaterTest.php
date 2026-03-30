<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Association\ParentAssociationsFilter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation\QuantifiedAssociationsFromAncestorsFilter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductModelUpdater;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Validator\QuantifiedAssociationsStructureValidatorInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use PHPUnit\Framework\TestCase;

class ProductModelUpdaterTest extends TestCase
{
    private ProductModelUpdater $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelUpdater();
    }

}
