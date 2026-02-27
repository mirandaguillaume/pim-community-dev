<?php

namespace Oro\Bundle\SecurityBundle\Cache;

use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class AclAnnotationCacheClearer implements CacheClearerInterface
{
    /**
     * Constructor
     */
    public function __construct(private readonly AclAnnotationProvider $provider) {}

    /**
     * {inheritdoc}
     */
    public function clear($cacheDir)
    {
        $this->provider->clearCache();
    }
}
