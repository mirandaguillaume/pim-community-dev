<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class DashboardProjectionType implements \Stringable
{
    public const CATALOG = 'catalog';
    public const CATEGORY = 'category';
    public const FAMILY = 'family';

    private function __construct(private string $type) {}

    public function __toString(): string
    {
        return $this->type;
    }

    public static function catalog(): self
    {
        return new self(self::CATALOG);
    }

    public static function family(): self
    {
        return new self(self::FAMILY);
    }

    public static function category(): self
    {
        return new self(self::CATEGORY);
    }
}
