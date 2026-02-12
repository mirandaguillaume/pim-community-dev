<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class AddAttributeCommand
{
    private function __construct(
        public string $code,
        public string $locale,
        public ?string $label,
        public string $type,
        public bool $isScopable,
        public bool $isLocalizable,
        public string $templateUuid,
    ) {
        Assert::uuid($templateUuid);
    }

    public static function create(
        string $code,
        string $type,
        bool $isScopable,
        bool $isLocalizable,
        string $templateUuid,
        string $locale,
        ?string $label,
    ): self {
        return new self(
            code: $code,
            locale: $locale,
            label: $label,
            type: $type,
            isScopable: $isScopable,
            isLocalizable: $isLocalizable,
            templateUuid: $templateUuid,
        );
    }
}
