<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AttributeCopierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\FieldCopierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertyCopier;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;

class PropertyCopierTest extends TestCase
{
    private PropertyCopier $sut;

    protected function setUp(): void
    {
        $this->sut = new PropertyCopier();
    }

}
