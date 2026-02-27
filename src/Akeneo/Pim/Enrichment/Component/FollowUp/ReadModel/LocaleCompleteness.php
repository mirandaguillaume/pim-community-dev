<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel;

/**
 * LocaleCompleteness class represents the completeness for a locale to show it in the dashboard
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleCompleteness
{
    public function __construct(private readonly string $locale, private readonly int $numberOfCompleteProducts)
    {
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function numberOfCompleteProducts(): int
    {
        return $this->numberOfCompleteProducts;
    }

    public function toArray(): array
    {
        return [
            $this->locale => $this->numberOfCompleteProducts,
        ];
    }
}
