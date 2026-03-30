<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\PdfGeneration\Renderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Exception\RendererRequiredException;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\RendererInterface;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\RendererRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PHPUnit\Framework\TestCase;

class RendererRegistryTest extends TestCase
{
    private RendererRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new RendererRegistry();
    }

}
