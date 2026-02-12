<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;

/**
 * Class Manager
 *
 * @package Oro\Bundle\DataGridBundle\Datagrid
 *
 * Responsibility of this class is to store raw config data, prepare configs for datagrid builder.
 * Public interface returns datagrid object prepared by builder using config
 */
class Manager implements ManagerInterface
{
    public function __construct(private readonly Builder $datagridBuilder, private readonly ConfigurationProviderInterface $configurationProvider, private readonly RequestParameters $requestParameters)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getDatagrid($name)
    {
        // prepare for work with current grid
        $this->requestParameters->setRootParameter($name);
        $config = $this->getConfigurationForGrid($name);
        $datagrid = $this->datagridBuilder->build($config);

        return $datagrid;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationForGrid($name)
    {
        return $this->configurationProvider->getConfiguration($name);
    }
}
