<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel;

/**
 * Represents the average volume and maximum volume for a given entity.
 *
 * For example, the maximum number of attributes per family, among all the families,
 * and the average number of attributes per family.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AverageMaxVolumes
{
    public function __construct(private readonly int $maxVolume, private readonly int $averageVolume, private readonly string $volumeName) {}

    public function getMaxVolume(): int
    {
        return $this->maxVolume;
    }

    public function getAverageVolume(): int
    {
        return $this->averageVolume;
    }

    public function getVolumeName(): string
    {
        return $this->volumeName;
    }
}
