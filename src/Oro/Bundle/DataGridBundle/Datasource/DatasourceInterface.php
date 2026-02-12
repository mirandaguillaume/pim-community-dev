<?php

namespace Oro\Bundle\DataGridBundle\Datasource;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;

/**
 * Class DatasourceInterface
 * @package Oro\Bundle\DataGridBundle\Datasource
 */
interface DatasourceInterface
{
    /**
     * Add source to datagrid
     */
    public function process(DatagridInterface $grid, array $config);

    /**
     * Returns data extracted via datasource
     *
     * @return array
     */
    public function getResults();
}
