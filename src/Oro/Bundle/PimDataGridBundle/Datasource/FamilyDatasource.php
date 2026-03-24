<?php

namespace Oro\Bundle\PimDataGridBundle\Datasource;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface;

/**
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyDatasource implements DatasourceInterface, ParameterizableInterface
{
    protected \Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface $repository;

    protected \Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface $massRepository;

    /** @var QueryBuilder */
    protected $qb;

    protected \Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface $hydrator;

    /** @var array */
    protected $parameters = [];

    public function __construct(
        DatagridRepositoryInterface $repository,
        MassActionRepositoryInterface $massRepository,
        HydratorInterface $hydrator
    ) {
        $this->repository = $repository;
        $this->massRepository = $massRepository;
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function process(DatagridInterface $grid, array $config): void
    {
        $this->qb = $this->repository->createDatagridQueryBuilder();
        $grid->setDatasource(clone $this);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters($parameters): static
    {
        $this->parameters += $parameters;

        if ($this->qb instanceof QueryBuilder) {
            foreach ($this->parameters as $name => $value) {
                $this->qb->setParameter($name, $value);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        return $this->hydrator->hydrate($this->qb);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(): never
    {
        throw new \LogicException("No need to implement this method, design flaw in interface!");
    }

    /**
     * {@inheritdoc}
     */
    public function getMassActionRepository()
    {
        return $this->massRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function setMassActionRepository(MassActionRepositoryInterface $massActionRepository): never
    {
        throw new \LogicException("No need to implement this method, design flaw in interface!");
    }

    /**
     * {@inheritdoc}
     */
    public function setHydrator(HydratorInterface $hydrator): static
    {
        $this->hydrator = $hydrator;

        return $this;
    }
}
