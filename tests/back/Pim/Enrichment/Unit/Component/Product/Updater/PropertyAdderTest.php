<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AttributeAdderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\FieldAdderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertyAdder;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;

class PropertyAdderTest extends TestCase
{
    private PropertyAdder $sut;

    protected function setUp(): void
    {
        $this->sut = new PropertyAdder();
    }

}
