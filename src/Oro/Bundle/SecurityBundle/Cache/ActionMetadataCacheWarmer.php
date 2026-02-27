<?php

namespace Oro\Bundle\SecurityBundle\Cache;

use Oro\Bundle\SecurityBundle\Metadata\ActionMetadataProvider;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class ActionMetadataCacheWarmer implements CacheWarmerInterface
{
    /**
     * Constructor
     */
    public function __construct(private readonly ActionMetadataProvider $provider)
    {
    }

    /**
     * {inheritdoc}
     */
    public function warmUp($cacheDir): array
    {
        $this->provider->warmUpCache();

        return [];
    }

    /**
     * {inheritdoc}
     */
    public function isOptional(): bool
    {
        return true;
    }
}
