<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CommandInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class UpdateNomenclatureCommand implements CommandInterface
{
    /**
     * @param array<string, ?string> $values
     */
    public function __construct(
        private string $propertyCode,
        private ?string $operator,
        private ?int $value,
        private ?bool $generateIfEmpty,
        private ?array $values = [],
    ) {}

    /**
     * @return array<string, ?string>
     */
    public function getValues(): array
    {
        return $this->values ?? [];
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function getGenerateIfEmpty(): ?bool
    {
        return $this->generateIfEmpty;
    }

    public function getPropertyCode(): string
    {
        return $this->propertyCode;
    }
}
