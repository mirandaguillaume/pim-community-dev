<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Domain\Model;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Status
{
    final public const int COMPLETED = 1;
    final public const int STARTING = 2;
    final public const int IN_PROGRESS = 3;
    final public const int STOPPING = 4;
    final public const int STOPPED = 5;
    final public const int FAILED = 6;
    final public const int ABANDONED = 7;
    final public const int UNKNOWN = 8;
    final public const int PAUSING = 9;
    final public const int PAUSED = 10;

    public static array $labels = [
        self::COMPLETED => 'COMPLETED',
        self::STARTING => 'STARTING',
        self::IN_PROGRESS => 'IN_PROGRESS',
        self::STOPPING => 'STOPPING',
        self::STOPPED => 'STOPPED',
        self::FAILED => 'FAILED',
        self::ABANDONED => 'ABANDONED',
        self::UNKNOWN => 'UNKNOWN',
        self::PAUSING => 'PAUSING',
        self::PAUSED => 'PAUSED',
    ];

    public static function fromStatus(int $status): self
    {
        if (!array_key_exists($status, self::$labels)) {
            throw new \InvalidArgumentException(sprintf('Invalid status "%s"', $status));
        }

        return new self($status);
    }

    public static function fromLabel(string $status): self
    {
        if (!in_array($status, self::$labels)) {
            throw new \InvalidArgumentException(sprintf('Invalid label "%s"', $status));
        }

        return new self(array_flip(self::$labels)[$status]);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getLabel(): string
    {
        return self::$labels[$this->status];
    }

    private function __construct(
        private readonly int $status = self::UNKNOWN,
    ) {
    }
}
