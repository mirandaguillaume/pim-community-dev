<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Twig\Environment;

class TextareaProductValueRenderer implements ProductValueRenderer
{
    public function render(Environment $environment, AttributeInterface $attribute, ?ValueInterface $value, string $localeCode): ?string
    {
        if (!$value instanceof \Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface) {
            return null;
        }

        if ($attribute->isWysiwygEnabled()) {
            return strip_tags(
                (string) $value->getData(),
                '<p><br><b><i><u><strong><em><ul><ol><li><h1><h2><h3><h4><table><tr><td><th><thead><tbody>'
            );
        }

        /** @phpstan-ignore-next-line */
        return \twig_escape_filter($environment, $value);
    }

    public function supportsAttributeType(string $attributeType): bool
    {
        return $attributeType === AttributeTypes::TEXTAREA;
    }
}
