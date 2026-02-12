<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query;

/**
 * Represent data regarding the variant product completenesses to build the ratio on the PMEF.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompleteVariantProducts
{
    public function __construct(private readonly array $completenesses)
    {
    }

    /**
     * Count the number of complete variant product and the total number of variant product for all channels and locales
     */
    public function values(): array
    {
        $completenesses = [];
        $completenesses['completenesses'] =  $this->parsedFlatCompletenesses();
        $completenesses['total'] = $this->numberOfProducts();

        return $completenesses;
    }

    /**
     * Count the number of complete variant product and the total number of variant product depending on a channel and a locale
     *
     *
     */
    public function value(string $channel, string $locale): array
    {
        $completenesses =  $this->parsedFlatCompletenesses();

        if (!isset($completenesses[$channel][$locale])) {
            return [
                'complete' => 0,
                'total' => $this->numberOfProducts()
            ];
        }

        return [
            'complete' => $completenesses[$channel][$locale],
            'total' => $this->numberOfProducts()
        ];
    }

    /**
     * Return number of product variant
     */
    private function numberOfProducts(): int
    {
        return count(
            array_unique(
                array_column($this->completenesses, 'product_uuid')
            )
        );
    }

    /**
     * Return the structured variant product completenesses
     *
     * [
     *      ecommerce => [
     *          en_US => 1
     *          fr_FR => 2
     *      ]
     * ]
     */
    private function parsedFlatCompletenesses(): array
    {
        $completenesses = [];
        foreach ($this->completenesses as $completeness) {
            $locale = $completeness['locale_code'];
            $channel = $completeness['channel_code'];
            if (!isset($completenesses[$channel][$locale])) {
                $completenesses[$channel][$locale] = 0;
            }

            $completenesses[$channel][$locale] = $completenesses[$channel][$locale] + $completeness['complete'];
        }

        return $completenesses;
    }
}
