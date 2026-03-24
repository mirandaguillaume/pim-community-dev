<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type TextTransformationNormalized 'no'|'uppercase'|'lowercase'
 */
final readonly class TextTransformation
{
    public const string NO = 'no';
    public const string UPPERCASE = 'uppercase';
    public const string LOWERCASE = 'lowercase';

    /**
     * @param TextTransformationNormalized $value
     */
    private function __construct(
        private string $value
    ) {}

    public static function fromString(string $value): self
    {
        Assert::oneOf($value, [self::NO, self::UPPERCASE, self::LOWERCASE]);

        return new self($value);
    }

    /**
     * @return TextTransformationNormalized
     */
    public function normalize(): string
    {
        return $this->value;
    }
}
