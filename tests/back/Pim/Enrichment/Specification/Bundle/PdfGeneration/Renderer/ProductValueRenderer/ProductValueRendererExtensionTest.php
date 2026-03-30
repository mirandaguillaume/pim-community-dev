<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\ProductValueRenderer;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\ProductValueRendererExtension;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\ProductValueRendererRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class ProductValueRendererExtensionTest extends TestCase
{
    private ProductValueRendererExtension $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductValueRendererExtension();
    }

}
