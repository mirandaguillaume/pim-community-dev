<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Get;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class GetNomenclatureQuery
{
    public function __construct(private string $propertyCode) {}

    public function propertyCode(): string
    {
        return $this->propertyCode;
    }
}
