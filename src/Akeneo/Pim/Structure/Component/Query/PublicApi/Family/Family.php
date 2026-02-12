<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

final readonly class Family
{
    /**
     * @param array<string, string> $labels
     * @params list<string> $attributeCodes
     */
    public function __construct(
        public string $code,
        public array $labels,
        public array $attributeCodes,
    ) {
    }
}
