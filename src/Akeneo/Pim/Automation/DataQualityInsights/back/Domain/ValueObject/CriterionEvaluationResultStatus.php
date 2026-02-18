<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CriterionEvaluationResultStatus implements \Stringable
{
    public const DONE = 'done';
    public const IN_PROGRESS = 'in_progress';
    public const NOT_APPLICABLE = 'not_applicable';
    public const ERROR = 'error';

    private const STATUS_LIST = [
        self::DONE,
        self::IN_PROGRESS,
        self::NOT_APPLICABLE,
        self::ERROR,
    ];

    private readonly string $status;

    public function __construct(string $status)
    {
        if ('' === $status) {
            throw new \InvalidArgumentException('The status can not be an empty string.');
        }

        if (!in_array($status, self::STATUS_LIST)) {
            throw new \InvalidArgumentException(sprintf('The status "%s" does not exist.', $status));
        }

        $this->status = $status;
    }

    public function __toString(): string
    {
        return $this->status;
    }

    public static function done(): self
    {
        return new self(self::DONE);
    }

    public static function notApplicable(): self
    {
        return new self(self::NOT_APPLICABLE);
    }

    public static function error(): self
    {
        return new self(self::ERROR);
    }
}
