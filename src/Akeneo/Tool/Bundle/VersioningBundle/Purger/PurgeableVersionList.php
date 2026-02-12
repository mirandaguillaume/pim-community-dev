<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\Purger;

final readonly class PurgeableVersionList implements \Countable
{
    /**
     * @param int[] $versionIds
     */
    public function __construct(private string $resourceName, private array $versionIds)
    {
    }

    public function getVersionIds(): array
    {
        return $this->versionIds;
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    public function count(): int
    {
        return count($this->versionIds);
    }

    public function remove(array $versionIds): self
    {
        if (empty($versionIds)) {
            return $this;
        }

        $versionIds = array_values(array_diff($this->versionIds, $versionIds));

        return new self($this->resourceName, $versionIds);
    }

    public function keep(array $versionIds): self
    {
        if (!empty($versionIds)) {
            $versionIds = array_values(array_intersect($this->versionIds, $versionIds));
        }

        return new self($this->resourceName, $versionIds);
    }
}
