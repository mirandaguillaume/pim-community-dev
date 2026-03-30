<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\TextareaProductValueRenderer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

class TextareaProductValueRendererTest extends TestCase
{
    private TextareaProductValueRenderer $sut;

    protected function setUp(): void
    {
        $this->sut = new TextareaProductValueRenderer();
    }

}
