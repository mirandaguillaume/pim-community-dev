<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class DashboardProjectionCode implements \Stringable
{
    public const CATALOG = 'catalog';

    private function __construct(private string $code) {}

    public function __toString(): string
    {
        return $this->code;
    }

    public static function catalog(): self
    {
        return new self(self::CATALOG);
    }

    public static function family(FamilyCode $familyCode): self
    {
        return new self(strval($familyCode));
    }

    public static function category(CategoryCode $categoryCode): self
    {
        return new self(strval($categoryCode));
    }
}
