<?php

namespace Akeneo\Tool\Component\Batch\Step;

use Akeneo\Tool\Component\Batch\Model\StepExecution;

/**
 * Interface is used to receive StepExecution instance inside reader, processor or writer
 */
interface StepExecutionAwareInterface
{
    public function setStepExecution(StepExecution $stepExecution);
}
