<?php

namespace Akeneo\Pim\Enrichment\Bundle\MassEditAction\Operation;

/**
 * A basic implementation of the MassEditOperation.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditOperation implements BatchableOperationInterface
{
    /**
     * @param string $jobInstanceCode
     * @param array  $filters
     * @param array  $actions
     */
    public function __construct(protected $jobInstanceCode, protected $filters, protected $actions) {}

    /**
     * {@inheritdoc}
     */
    public function getBatchConfig(): array
    {
        return [
            'filters' => $this->filters,
            'actions' => $this->actions,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getJobInstanceCode(): string
    {
        return $this->jobInstanceCode;
    }
}
