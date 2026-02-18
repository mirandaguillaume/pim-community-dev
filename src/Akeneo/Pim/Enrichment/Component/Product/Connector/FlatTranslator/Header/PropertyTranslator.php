<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class PropertyTranslator implements FlatHeaderTranslatorInterface
{
    public function __construct(private readonly LabelTranslatorInterface $labelTranslator, private readonly array $supportedFields)
    {
    }

    public function supports(string $columnName): bool
    {
        return in_array($columnName, $this->supportedFields);
    }

    public function warmup(array $columnNames, string $locale): void
    {
    }

    public function translate(string $columnName, string $locale): string
    {
        return $this->labelTranslator->translate(
            sprintf('pim_common.%s', $columnName),
            $locale,
            sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $columnName)
        );
    }
}
