<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Association\ParentAssociationsFilter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation\QuantifiedAssociationsFromAncestorsFilter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductUpdater;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Validator\QuantifiedAssociationsStructureValidatorInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use PHPUnit\Framework\TestCase;

class ProductUpdaterTest extends TestCase
{
    private ProductUpdater $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductUpdater();
    }

}
