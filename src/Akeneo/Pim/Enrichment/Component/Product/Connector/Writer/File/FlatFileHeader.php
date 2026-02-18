<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FlatFileHeader
{
    private ?bool $usesUnit = null;

    private ?bool $usesCurrencies = null;

    private ?bool $isLocaleSpecific = null;

    private ?array $specificToLocales = null;

    public function __construct(
        private readonly string $code,
        private readonly ?bool $isScopable = false,
        private readonly ?string $channelCode = null,
        private readonly ?bool $isLocalizable = false,
        private readonly ?array $localeCodes = [],
        private readonly ?bool $isMedia = false,
        ?bool $usesUnit = false,
        ?bool $usesCurrencies = false,
        private readonly ?array $channelCurrencyCodes = [],
        ?bool $isLocaleSpecific = false,
        ?array $specificToLocales = []
    ) {
        if ($isLocaleSpecific && empty($specificToLocales)) {
            throw new \InvalidArgumentException(
                'A list of locales to which the header is specific to must be provided '.
                'when the header is defined as locale specific'
            );
        }

        if ($usesCurrencies && $usesUnit) {
            throw new \InvalidArgumentException(
                'A header cannot have both currencies and unit.'
            );
        }
        $this->usesUnit = $usesUnit;

        $this->usesCurrencies = $usesCurrencies;

        $this->isLocaleSpecific = $isLocaleSpecific;
        $this->specificToLocales = $specificToLocales;
    }

    /**
     * Build a FlatFileHeader from product attribute
     */
    public static function buildFromAttributeData(
        string $attributeCode,
        string $attributeType,
        bool $scopable,
        string $channelCode,
        bool $localizable,
        array $localeCodes,
        array $channelCurrencyCodes,
        array $specificToLocales
    ): FlatFileHeader {
        $mediaAttributeTypes = [
            AttributeTypes::IMAGE,
            AttributeTypes::FILE
        ];

        return new FlatFileHeader(
            $attributeCode,
            $scopable,
            $channelCode,
            $localizable,
            $localeCodes,
            (in_array($attributeType, $mediaAttributeTypes)),
            (AttributeTypes::METRIC === $attributeType),
            (AttributeTypes::PRICE_COLLECTION === $attributeType),
            $channelCurrencyCodes,
            !empty($specificToLocales),
            $specificToLocales
        );
    }

    /**
     * Indicate whether the header is associated to a media information
     */
    public function isMedia(): bool
    {
        return $this->isMedia;
    }

    /**
     * Generate headers string contextualized on channel
     */
    public function generateHeaderStrings(): array
    {
        if ($this->isLocaleSpecific && count(array_intersect($this->localeCodes, $this->specificToLocales)) === 0) {
            return [];
        }

        $prefixes = [];

        if ($this->isLocalizable && $this->isScopable) {
            foreach ($this->localeCodes as $localeCode) {
                if (!$this->isLocaleSpecific ||
                    ($this->isLocaleSpecific && in_array($localeCode, $this->specificToLocales))) {
                    $prefixes[] = sprintf('%s-%s-%s', $this->code, $localeCode, $this->channelCode);
                }
            }
        } elseif ($this->isLocalizable) {
            foreach ($this->localeCodes as $localeCode) {
                if (!$this->isLocaleSpecific ||
                    ($this->isLocaleSpecific && in_array($localeCode, $this->specificToLocales))) {
                    $prefixes[] = sprintf('%s-%s', $this->code, $localeCode);
                }
            }
        } elseif ($this->isScopable) {
            $prefixes[] = sprintf('%s-%s', $this->code, $this->channelCode);
        } else {
            $prefixes[] = $this->code;
        }

        $headers = [];

        if ($this->usesCurrencies) {
            foreach ($prefixes as $prefix) {
                foreach ($this->channelCurrencyCodes as $currencyCode) {
                    $headers[] = sprintf('%s-%s', $prefix, $currencyCode);
                }
            }
        } elseif ($this->usesUnit) {
            foreach ($prefixes as $prefix) {
                $headers[] = $prefix;
                $headers[] = sprintf('%s-unit', $prefix);
            }
        } else {
            $headers = $prefixes;
        }

        return $headers;
    }
}
