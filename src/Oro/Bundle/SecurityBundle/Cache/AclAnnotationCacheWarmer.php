<?php

namespace Oro\Bundle\SecurityBundle\Cache;

use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class AclAnnotationCacheWarmer implements CacheWarmerInterface
{
    /**
     * Constructor
     */
    public function __construct(private readonly AclAnnotationProvider $provider) {}

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
