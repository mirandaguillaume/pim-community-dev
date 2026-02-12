<?php

namespace Oro\Bundle\ConfigBundle\Config\Tree;

abstract class AbstractNodeDefinition
{
    /** @var array */
    protected $definition;

    /**
     * @param string $name
     */
    public function __construct(protected $name, array $definition)
    {
        $this->definition = $this->prepareDefinition($definition);
    }

    /**
     * Getter for name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set node priority
     *
     * @param int $priority
     *
     * @return $this
     *
     */
    public function setPriority($priority)
    {
        $this->definition['priority'] = $priority;

        return $this;
    }

    /**
     * Returns node priority
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->definition['priority'];
    }

    /**
     * Prepare definition, set default values
     *
     *
     * @return array
     */
    protected function prepareDefinition(array $definition)
    {
        if (!isset($definition['priority'])) {
            $definition['priority'] = 0;
        }

        return $definition;
    }
}
