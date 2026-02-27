<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductCompletenessWithMissingAttributeCodes
{
    private readonly int $requiredCount;

    /** @var string[] */
    private readonly array $missingAttributeCodes;

    public function __construct(
        private readonly string $channelCode,
        private readonly string $localeCode,
        int $requiredCount,
        array $missingAttributeCodes
    ) {
        if ($requiredCount < 0) {
            throw new \InvalidArgumentException('$requiredCount expects a positive integer');
        }
        $this->requiredCount = $requiredCount;
        $this->missingAttributeCodes = array_values($missingAttributeCodes);
    }

    public function channelCode(): string
    {
        return $this->channelCode;
    }

    public function localeCode(): string
    {
        return $this->localeCode;
    }

    public function requiredCount(): int
    {
        return $this->requiredCount;
    }

    public function missingAttributesCount(): int
    {
        return \count($this->missingAttributeCodes);
    }

    public function missingAttributeCodes(): array
    {
        return $this->missingAttributeCodes;
    }

    public function ratio(): int
    {
        if (0 === $this->requiredCount) {
            return 100;
        }

        return (int) floor(100 * ($this->requiredCount - count($this->missingAttributeCodes)) / $this->requiredCount);
    }
}
