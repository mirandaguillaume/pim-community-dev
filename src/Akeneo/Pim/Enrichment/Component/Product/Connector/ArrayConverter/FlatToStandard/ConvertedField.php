<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

/**
 * Object that represent the converted field (family, category, etc.) from a flat file data (CSV, XLSX)
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConvertedField
{
    public function __construct(private string $columnName, private readonly mixed $value)
    {
    }

    public function appendTo(array $convertedField): array
    {
        if (array_key_exists($this->columnName, $convertedField)) {
            $convertedField[$this->columnName] = array_replace_recursive($convertedField[$this->columnName], $this->value);
        } else {
            $convertedField[$this->columnName] = $this->value;
        }

        return $convertedField;
    }
}
