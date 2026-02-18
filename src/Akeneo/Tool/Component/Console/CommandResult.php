<?php

/**
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Component\Console;

/**
 * Command result object
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class CommandResult implements CommandResultInterface
{
    /** @var array */
    protected $commandOutput;

    /**
     * @param int $commandStatus
     */
    public function __construct(array $output, protected $commandStatus)
    {
        $this->commandOutput = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommandOutput()
    {
        return $this->commandOutput;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommandStatus()
    {
        return $this->commandStatus;
    }
}
