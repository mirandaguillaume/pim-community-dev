<?php

namespace Akeneo\Tool\Component\Batch\Event;

use Monolog\ErrorHandler;
use Monolog\Logger;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ErrorHandlerConfigurationListener
{
    public function __construct(private readonly Logger $logger, private readonly string $environment)
    {
    }

    public function onConsoleCommand(ConsoleCommandEvent $consoleCommandEvent)
    {
        if ('prod' === $this->environment) {
            $handler = new ErrorHandler($this->logger);
            $handler->registerErrorHandler([], false);
            $handler->registerExceptionHandler(['Throwable' => Logger::CRITICAL], false);
            $handler->registerFatalHandler();

            $consoleCommandEvent->getCommand()->getApplication()->setCatchExceptions(false);
        }
    }
}
