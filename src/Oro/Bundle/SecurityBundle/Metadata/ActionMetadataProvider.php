<?php

namespace Oro\Bundle\SecurityBundle\Metadata;

class ActionMetadataProvider
{
    final public const CACHE_KEY = 'data';

    /**
     * @var AclAnnotationProvider
     */
    protected $annotationProvider;

    /**
     * @var array
     *         key = action name
     *         value = ActionMetadata
     */
    protected $localCache;

    public function __construct(
        AclAnnotationProvider $annotationProvider
    ) {
        $this->annotationProvider = $annotationProvider;
    }

    /**
     * Checks whether an action with the given name is defined.
     *
     * @param  string $actionName The entity class name
     * @return bool
     */
    public function isKnownAction($actionName)
    {
        $this->ensureMetadataLoaded();

        return isset($this->localCache[$actionName]);
    }

    /**
     * Gets metadata for all actions.
     *
     * @return ActionMetadata[]
     */
    public function getActions()
    {
        $this->ensureMetadataLoaded();

        return array_values($this->localCache);
    }

    /**
     * Warms up the cache
     */
    public function warmUpCache()
    {
        $this->ensureMetadataLoaded();
    }

    /**
     * Clears the cache
     */
    public function clearCache()
    {
        $this->localCache = null;
    }

    /**
     * Makes sure that metadata are loaded
     */
    protected function ensureMetadataLoaded()
    {
        if ($this->localCache === null) {
            $data = [];
            foreach ($this->annotationProvider->getAnnotations('action') as $annotation) {
                $data[$annotation->getId()] = new ActionMetadata(
                    $annotation->getId(),
                    $annotation->getGroup(),
                    $annotation->getLabel(),
                    $annotation->isEnabledAtCreation(),
                    $annotation->getOrder(),
                    $annotation->isVisible(),
                );
            }

            $this->localCache = $data;
        }
    }
}
