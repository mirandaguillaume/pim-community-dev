<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Model;

/**
 * Represents a previously aggregated volume.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AggregatedVolume
{
    public function __construct(private readonly string $volumeName, private readonly array $volume, private readonly \DateTime $aggregatedAt) {}

    public function getVolumeName(): string
    {
        return $this->volumeName;
    }

    public function getVolume(): array
    {
        return $this->volume;
    }

    public function aggregatedAt(): \DateTime
    {
        return $this->aggregatedAt;
    }
}
