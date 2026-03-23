<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness\AttributeCase;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BuildSqlMaskField
{
    /**
     * @param AttributeCase[] $attributeCases
     */
    public function __construct(
        private readonly iterable $attributeCases,
        private readonly SqlPlatformHelperInterface $platformHelper,
    ) {
    }

    public function __invoke(): string
    {
        if (count($this->attributeCases) === 0) {
            return $this->getDefaultMask();
        }
        return $this->getMaskWithCases($this->formatAttributeCases());
    }

    private function getDefaultMask(): string
    {
        $scopePart = $this->platformHelper->conditional('attribute.is_scopable', 'channel_locale.channel_code', "'<all_channels>'");
        $localePart = $this->platformHelper->conditional('attribute.is_localizable', 'channel_locale.locale_code', "'<all_locales>'");

        return <<<SQL
            JSON_ARRAYAGG(
                CONCAT(
                    attribute.code,
                    '-',
                    {$scopePart},
                    '-',
                    {$localePart}
                )
            )
            AS mask
            SQL;
    }

    private function formatAttributeCases(): string
    {
        $formattedCases = [];
        foreach ($this->attributeCases as $attributeCase) {
            $formattedCases[] = $attributeCase->getCase();
        }
        return implode(' ', $formattedCases);
    }

    private function getMaskWithCases(string $cases): string
    {
        $scopePart = $this->platformHelper->conditional('attribute.is_scopable', 'channel_locale.channel_code', "'<all_channels>'");
        $localePart = $this->platformHelper->conditional('attribute.is_localizable', 'channel_locale.locale_code', "'<all_locales>'");

        return "
JSON_ARRAYAGG(
    CONCAT(
        CASE
            " . $cases . "
            ELSE attribute.code
        END,
        '-',
        {$scopePart},
        '-',
        {$localePart}
    )
) AS mask
";
    }
}
