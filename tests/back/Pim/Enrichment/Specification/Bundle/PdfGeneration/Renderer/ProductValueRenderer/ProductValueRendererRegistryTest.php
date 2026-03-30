<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\ProductValueRenderer;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\ProductValueRendererRegistry;
use PHPUnit\Framework\TestCase;

class ProductValueRendererRegistryTest extends TestCase
{
    private ProductValueRendererRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductValueRendererRegistry();
    }

}
