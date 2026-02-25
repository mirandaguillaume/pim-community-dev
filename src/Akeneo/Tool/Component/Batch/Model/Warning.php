<?php

namespace Akeneo\Tool\Component\Batch\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
/**
 * Represents a Warning raised during step execution
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity()]
#[ORM\Table(name: 'akeneo_batch_warning')]
class Warning
{
    /** @var integer */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    #[ORM\ManyToOne(targetEntity: \Akeneo\Tool\Component\Batch\Model\StepExecution::class, inversedBy: 'warnings')]
    #[ORM\JoinColumn(name: 'step_execution_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private StepExecution $stepExecution;

    /** @var string */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private $reason;

    #[ORM\Column(name: 'reason_parameters', type: Types::ARRAY)]
    private array $reasonParameters;

    #[ORM\Column(type: Types::ARRAY)]
    private array $item;

    /**
     * Constructor
     *
     * @param string $reason
     */
    public function __construct(StepExecution $stepExecution, $reason, array $reasonParameters, array $item)
    {
        $this->stepExecution = $stepExecution;
        $this->reason = $reason;
        $this->reasonParameters = $reasonParameters;
        $this->item = $item;
    }

    /**
     * Returns the id of the warning
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the step execution
     *
     * @return StepExecution
     */
    public function getStepExecution()
    {
        return $this->stepExecution;
    }

    /**
     * Sets the step execution
     *
     *
     * @return $this
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * Returns the reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Sets the reason
     *
     * @param string $reason
     *
     * @return $this
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Returns the reason parameters
     *
     * @return array
     */
    public function getReasonParameters()
    {
        return $this->reasonParameters;
    }

    /**
     * Sets  the reason parameters
     *
     *
     * @return $this
     */
    public function setReasonParameters(array $reasonParameters)
    {
        $this->reasonParameters = $reasonParameters;

        return $this;
    }

    /**
     * Returns the item over which the warning is set
     *
     * @return array
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Sets the item over which the warning is set
     *
     * @param array $item
     *
     * @return $this
     */
    public function setItem($item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Returns a representation of the warning as an array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'reason'           => $this->reason,
            'reasonParameters' => $this->reasonParameters,
            'item'             => $this->item,
        ];
    }
}
