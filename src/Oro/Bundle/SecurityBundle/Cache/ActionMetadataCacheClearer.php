<?php

namespace Oro\Bundle\SecurityBundle\Cache;

use Oro\Bundle\SecurityBundle\Metadata\ActionMetadataProvider;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class ActionMetadataCacheClearer implements CacheClearerInterface
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
    public function clear($cacheDir)
    {
        $this->provider->clearCache();
    }
}
