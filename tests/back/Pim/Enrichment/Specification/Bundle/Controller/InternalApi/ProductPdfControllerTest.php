<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductPdfController;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Exception\RendererRequiredException;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\RendererRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ProductPdfControllerTest extends TestCase
{
    private ProductPdfController $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductPdfController();
    }

}
