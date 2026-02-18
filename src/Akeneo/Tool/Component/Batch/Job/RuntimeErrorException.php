<?php

namespace Akeneo\Tool\Component\Batch\Job;

/**
 * Exception that stops the job execution
 * Its message will be translated
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class RuntimeErrorException extends \RuntimeException
{
    /**
     * @param string $message
     */
    public function __construct($message, protected array $messageParameters = [])
    {
        parent::__construct($message);
    }

    /**
     * @return array
     */
    public function getMessageParameters()
    {
        return $this->messageParameters;
    }
}
