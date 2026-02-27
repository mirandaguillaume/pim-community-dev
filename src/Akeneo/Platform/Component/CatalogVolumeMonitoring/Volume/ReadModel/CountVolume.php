<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel;

/**
 * Represents the volume of an axis of limitation.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountVolume
{
    public function __construct(private readonly int $volume, private readonly string $volumeName)
    {
    }

    public function getVolume(): int
    {
        return $this->volume;
    }

    public function getVolumeName(): string
    {
        return $this->volumeName;
    }
}
