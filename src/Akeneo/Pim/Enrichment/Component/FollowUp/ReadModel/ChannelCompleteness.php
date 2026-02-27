<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel;

/**
 * ChannelCompleteness class represents the completeness for a channel to show it in the dashboard
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelCompleteness
{
    /**
     * @param LocaleCompleteness[] $localeCompletenesses
     */
    public function __construct(private readonly string $channelCode, private readonly int $numberOfCompleteProducts, private readonly int $numberTotalOfProducts, private readonly array $localeCompletenesses, private readonly array $channelLabels = []) {}

    public function channel(): string
    {
        return $this->channelCode;
    }

    public function numberOfCompleteProducts(): int
    {
        return $this->numberOfCompleteProducts;
    }

    public function numberTotalOfProducts(): int
    {
        return $this->numberTotalOfProducts;
    }

    public function localeCompletenesses(): array
    {
        return $this->localeCompletenesses;
    }

    public function toArray(): array
    {
        $locales = [];
        foreach ($this->localeCompletenesses as $localeCompleteness) {
            $locales = array_merge($locales, $localeCompleteness->toArray());
        }

        return [
            'labels' => $this->channelLabels,
            'total' => $this->numberTotalOfProducts,
            'complete' => $this->numberOfCompleteProducts,
            'locales' => $locales,
        ];
    }
}
