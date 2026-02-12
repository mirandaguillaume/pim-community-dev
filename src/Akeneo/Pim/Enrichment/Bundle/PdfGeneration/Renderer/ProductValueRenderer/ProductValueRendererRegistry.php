<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

class ProductValueRendererRegistry
{
    /**
     * @param ProductValueRenderer[] $renderers
     */
    public function __construct(private readonly iterable $renderers, private readonly ProductValueRenderer $defaultRenderer)
    {
    }

    public function getProductValueRenderer($attributeType): ProductValueRenderer
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supportsAttributeType($attributeType)) {
                return $renderer;
            }
        }

        return $this->defaultRenderer;
    }
}
