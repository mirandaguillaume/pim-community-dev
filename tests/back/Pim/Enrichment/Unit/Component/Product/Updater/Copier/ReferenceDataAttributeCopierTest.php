<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\ReferenceDataAttributeCopier;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;

class ReferenceDataAttributeCopierTest extends TestCase
{
    private ReferenceDataAttributeCopier $sut;

    protected function setUp(): void
    {
        $this->sut = new ReferenceDataAttributeCopier();
    }

}
