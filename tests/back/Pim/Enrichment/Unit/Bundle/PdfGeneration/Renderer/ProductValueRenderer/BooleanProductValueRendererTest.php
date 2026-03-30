<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\BooleanProductValueRenderer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class BooleanProductValueRendererTest extends TestCase
{
    private BooleanProductValueRenderer $sut;

    protected function setUp(): void
    {
        $this->sut = new BooleanProductValueRenderer();
    }

}
