<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder\LocalizableScopableAttribute;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAndNotScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAndScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAndNotScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAndScopableAttributeException;
use PHPUnit\Framework\TestCase;

class LocalizableScopableAttributeTest extends TestCase
{
    private LocalizableScopableAttribute $sut;

    protected function setUp(): void
    {
        $this->sut = new LocalizableScopableAttribute();
    }

}
