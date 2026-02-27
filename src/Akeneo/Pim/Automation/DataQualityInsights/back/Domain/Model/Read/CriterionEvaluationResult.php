<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class CriterionEvaluationResult
{
    public function __construct(private ChannelLocaleRateCollection $rates, private CriterionEvaluationResultStatusCollection $statusCollection, private array $data)
    {
    }

    public static function fromArray(array $rawResult): self
    {
        $rates = ChannelLocaleRateCollection::fromArrayInt($rawResult['rates'] ?? []);
        $status = CriterionEvaluationResultStatusCollection::fromArrayString($rawResult['status'] ?? []);

        return new self($rates, $status, $rawResult['data'] ?? []);
    }

    public function getRates(): ChannelLocaleRateCollection
    {
        return $this->rates;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getAttributes(): ChannelLocaleDataCollection
    {
        $attributes = $this->data['attributes_with_rates'] ?? [];

        if ((is_countable($attributes) ? count($attributes) : 0) > 0) {
            return ChannelLocaleDataCollection::fromNormalizedChannelLocaleData($attributes, function ($attributes) {
                if (false === is_array($attributes)) {
                    return [];
                }

                $attributes = array_keys(array_filter($attributes, fn ($rate) => $rate < 100));

                return $attributes;
            });
        }

        // The 'attributes' array key is deprecated but kept here to allow backward compatibility
        $attributes = $this->data['attributes'] ?? [];

        return ChannelLocaleDataCollection::fromNormalizedChannelLocaleData($attributes, fn ($attributeCodes) => is_array($attributeCodes) ? $attributeCodes : []);
    }

    public function getStatus(): CriterionEvaluationResultStatusCollection
    {
        return $this->statusCollection;
    }
}
