<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Component\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @author Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ChannelCategoryHasBeenUpdated extends Event
{
    public function __construct(private readonly string $channelCode, private readonly string $previousCategoryCode, private readonly string $newCategoryCode)
    {
    }

    public function channelCode(): string
    {
        return $this->channelCode;
    }

    public function previousCategoryCode(): string
    {
        return $this->previousCategoryCode;
    }

    public function newCategoryCode(): string
    {
        return $this->newCategoryCode;
    }
}
