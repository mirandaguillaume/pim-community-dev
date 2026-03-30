<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\ImageProductValueRenderer;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class ImageProductValueRendererTest extends TestCase
{
    private ImageProductValueRenderer $sut;

    protected function setUp(): void
    {
        $this->sut = new ImageProductValueRenderer();
    }

}
