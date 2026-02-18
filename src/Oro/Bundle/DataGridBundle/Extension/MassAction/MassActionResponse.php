<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction;

class MassActionResponse implements MassActionResponseInterface
{
    /** @var array */
    protected $options = [];

    /**
     * @param boolean $successful
     * @param string  $message
     */
    public function __construct(protected $successful, protected $message, array $options = [])
    {
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    public function getOption($name)
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->successful;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
