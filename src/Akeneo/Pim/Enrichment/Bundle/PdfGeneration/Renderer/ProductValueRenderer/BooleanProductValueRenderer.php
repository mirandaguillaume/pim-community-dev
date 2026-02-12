<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class BooleanProductValueRenderer implements ProductValueRenderer
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function render(Environment $environment, AttributeInterface $attribute, ?ValueInterface $value, string $localeCode): ?string
    {
        if (null === $value) {
            return null;
        }

        return $this->translator->trans($value->getData() ? 'Yes' : 'No');
    }

    public function supportsAttributeType(string $attributeType): bool
    {
        return $attributeType === AttributeTypes::BOOLEAN;
    }
}
