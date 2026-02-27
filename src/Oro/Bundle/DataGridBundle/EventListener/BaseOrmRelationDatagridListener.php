<?php

namespace Oro\Bundle\DataGridBundle\EventListener;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

/**
 * Class BaseRelationDatagridListener
 * @package Oro\Bundle\DataGridBundle\EventListener
 *
 * Event listener should be applied when entities relation managed via datagrid
 */
class BaseOrmRelationDatagridListener
{
    /**
     * Included/excluded param names
     * populated by oro/datagrid/column-form-listener on frontend
     */
    final public const GRID_PARAM_DATA_IN = 'data_in';
    final public const GRID_PARAM_DATA_NOT_IN = 'data_not_in';

    /**
     * @param string            $paramName  Parameter name that should be taken from request and binded to query
     * @param bool              $isEditMode whether or not to add data_in, data_not_in params to query
     */
    public function __construct(protected $paramName, private readonly RequestParameters $requestParams, protected $isEditMode = true) {}

    /**
     * Add filters to where clause
     * Base query should looks as following:
     * (CASE WHEN (:relationParamName IS NOT NULL) THEN
     *       CASE WHEN (:relationParamName
     *              MEMBER OF alias.relationField OR alias.id IN (:data_in)) AND alias.id NOT IN (:data_not_in)
     *       THEN true ELSE false END
     *  ELSE
     *       CASE WHEN alias.id IN (:data_in) AND alias.id NOT IN (:data_not_in)
     *       THEN true ELSE false END
     *  END) as relationColumnName
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQueryBuilder();

            $additionalParams = $this->requestParams->get(RequestParameters::ADDITIONAL_PARAMETERS);
            if (isset($additionalParams[self::GRID_PARAM_DATA_IN])) {
                $dataIn = $additionalParams[self::GRID_PARAM_DATA_IN];
            } else {
                $dataIn = [0];
            }

            if (isset($additionalParams[self::GRID_PARAM_DATA_NOT_IN])) {
                $dataOut = $additionalParams[self::GRID_PARAM_DATA_NOT_IN];
            } else {
                $dataOut = [0];
            }

            $queryParameters = [
                $this->paramName => $this->requestParams->get($this->paramName, null),
                'data_in'        => $dataIn,
                'data_not_in'    => $dataOut,
            ];

            if (!$this->isEditMode) {
                unset($queryParameters['data_in'], $queryParameters['data_not_in']);
            }

            $queryBuilder->setParameters($queryParameters);
        }
    }
}
