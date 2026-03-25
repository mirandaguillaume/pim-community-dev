<?php

namespace Akeneo\Platform\Bundle\UIBundle\VersionStrategy;

use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class CacheBusterVersionStrategy implements VersionStrategyInterface
{
    /** @var VersionProviderInterface */
    protected $versionProvider;

    public function __construct(VersionProviderInterface $versionProvider)
    {
        $this->versionProvider = $versionProvider;
    }

    public function getVersion(string $path): string
    {
        return $this->versionProvider->getPatch();
    }

    public function applyVersion(string $path): string
    {
        $versioned = sprintf('%s?%s', ltrim($path, DIRECTORY_SEPARATOR), md5($this->getVersion($path)));

        if ($path && DIRECTORY_SEPARATOR == $path[0]) {
            return DIRECTORY_SEPARATOR . $versioned;
        }

        return $versioned;
    }
}
