<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\DefaultProductValueRenderer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

class DefaultProductValueRendererTest extends TestCase
{
    private DefaultProductValueRenderer $sut;

    protected function setUp(): void
    {
        $this->sut = new DefaultProductValueRenderer();
    }

}
